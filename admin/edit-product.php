<?php
session_start();
require_once '../includes/connect.php';

// Security gate: admins only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($productId <= 0) {
    header('Location: products.php');
    exit;
}

$success_message = '';
$error_message = '';
$debug_logs = [];

function add_debug(array &$debug_logs, string $msg): void {
    $debug_logs[] = $msg;
}

function ensure_secondary_gender_column(mysqli $conn, string &$error_message): bool {
    static $checked = false;
    if ($checked) { return true; }
    $checked = true;
    $exists = false;
    $schemaStmt = $conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'secondary_gender' LIMIT 1");
    if ($schemaStmt && $schemaStmt->execute()) {
        $res = $schemaStmt->get_result();
        $exists = $res && $res->num_rows > 0;
    }
    if ($schemaStmt) { $schemaStmt->close(); }
    if ($exists) { return true; }
    $alter = $conn->query("ALTER TABLE products ADD COLUMN secondary_gender ENUM('Men','Women','None') NOT NULL DEFAULT 'None'");
    if (!$alter) {
        $error_message = 'Database is missing secondary_gender column and could not be updated: ' . $conn->error;
        add_debug($GLOBALS['debug_logs'], $error_message);
        return false;
    }
    return true;
}

function ensure_size_gender_enum(mysqli $conn, string &$error_message): bool {
    // Column already exists with Men/Women; avoid altering enum to include disallowed values
    return true;
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'product');
}

function handle_upload(?array $file): array {
    if (empty($file) || empty($file['name'])) {
        return ['path' => '', 'error' => ''];
    }
    $upload_dir = '../assets/img/products/';
    if (!empty($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
        return ['path' => '', 'error' => 'Image upload failed. Please try again.'];
    }
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $basename = basename($file['name']);
    $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        return ['path' => '', 'error' => 'Invalid image type. Allowed: jpg, jpeg, png, webp.'];
    }
    $slug = slugify($basename);
    $newname = $slug . '-' . uniqid() . '.' . $ext;
    $target = $upload_dir . $newname;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['path' => '', 'error' => 'Failed to upload image.'];
    }
    return ['path' => 'assets/img/products/' . $newname, 'error' => ''];
}

// Fetch product
$product = null;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $productId);
$stmt->execute();
$res = $stmt->get_result();
$product = $res ? $res->fetch_assoc() : null;
$stmt->close();

// Fetch product sizes helper
function load_product_sizes(mysqli $conn, int $productId): array {
    $rows = [];
    $stmt = $conn->prepare("SELECT * FROM product_sizes WHERE product_id = ? ORDER BY size_label");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function recalc_product_stock(mysqli $conn, int $productId): void {
    $sumStmt = $conn->prepare("UPDATE products p SET stock_quantity = (SELECT COALESCE(SUM(stock_quantity),0) FROM product_sizes WHERE product_id = p.id AND is_active = 1) WHERE p.id = ?");
    $sumStmt->bind_param('i', $productId);
    $sumStmt->execute();
    $sumStmt->close();
}

$productSizes = load_product_sizes($conn, $productId);

if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_sizes') {
        if (ensure_size_gender_enum($conn, $error_message)) {
            $ids = $_POST['size_id'] ?? [];
            $labels = $_POST['size_label'] ?? [];
            $genders = $_POST['size_gender'] ?? [];
            $stocks = $_POST['size_stock'] ?? [];
            $activeIds = array_map('intval', $_POST['size_active'] ?? []);

            $stmtUpdate = $conn->prepare("UPDATE product_sizes SET size_label = ?, size_system = 'US', gender = ?, stock_quantity = ?, is_active = ? WHERE id = ? AND product_id = ?");
            if (!$stmtUpdate) {
                $error_message = $conn->error;
                add_debug($debug_logs, 'Prepare failed (update_sizes): ' . $conn->error);
            } else {
                foreach ($ids as $idx => $sid) {
                    $sid = (int) $sid;
                    $label = trim($labels[$idx] ?? '');
                    $genderVal = (($genders[$idx] ?? 'Men') === 'Women') ? 'Women' : 'Men';
                    $stockVal = (int) ($stocks[$idx] ?? 0);
                    $isActive = in_array($sid, $activeIds, true) ? 1 : 0;
                    $stmtUpdate->bind_param('ssiiii', $label, $genderVal, $stockVal, $isActive, $sid, $productId);
                    if (!$stmtUpdate->execute()) {
                        $error_message = $stmtUpdate->error ?: $conn->error;
                        $success_message = '';
                        add_debug($debug_logs, 'Update size failed id ' . $sid . ': ' . $error_message);
                    }
                }
                $stmtUpdate->close();
                if (!$error_message) { $success_message = 'Sizes updated.'; }
            }
        }
        $productSizes = load_product_sizes($conn, $productId);
        recalc_product_stock($conn, $productId);
    } elseif ($action === 'add_size') {
        if (ensure_size_gender_enum($conn, $error_message)) {
            $label = trim($_POST['new_size_label'] ?? '');
            $genderVal = (($_POST['new_size_gender'] ?? 'Men') === 'Women') ? 'Women' : 'Men';
            $stockVal = (int) ($_POST['new_size_stock'] ?? 0);
            if ($label === '') {
                $error_message = 'Size label is required.';
            } else {
                $stmtAdd = $conn->prepare("INSERT INTO product_sizes (product_id, size_label, size_system, gender, stock_quantity, is_active) VALUES (?, ?, 'US', ?, ?, 1)");
                if (!$stmtAdd) {
                    $error_message = $conn->error;
                    add_debug($debug_logs, 'Prepare failed (add_size): ' . $conn->error);
                } else {
                    $stmtAdd->bind_param('issi', $productId, $label, $genderVal, $stockVal);
                    if ($stmtAdd->execute()) {
                        $success_message = 'Size added.';
                    } else {
                        $error_message = $stmtAdd->error ?: $conn->error ?: 'Failed to add size (duplicate size/system/gender?).';
                        $success_message = '';
                        add_debug($debug_logs, 'Add size failed: ' . $error_message);
                    }
                    $stmtAdd->close();
                }
            }
        }
        $productSizes = load_product_sizes($conn, $productId);
        recalc_product_stock($conn, $productId);
    } elseif ($action === 'delete_size') {
        $deleteId = (int) ($_POST['delete_size_id'] ?? 0);
        if ($deleteId > 0) {
            $stmtSoft = $conn->prepare("UPDATE product_sizes SET is_active = 0, stock_quantity = 0 WHERE id = ? AND product_id = ?");
            if (!$stmtSoft) {
                $error_message = $conn->error ?: 'Failed to deactivate size.';
                add_debug($debug_logs, 'Prepare failed (delete_size soft): ' . ($conn->error ?: ''));
            } else {
                $stmtSoft->bind_param('ii', $deleteId, $productId);
                if ($stmtSoft->execute()) {
                    $success_message = 'Size deactivated (soft delete).';
                    $error_message = '';
                } else {
                    $error_message = $stmtSoft->error ?: $conn->error ?: 'Failed to deactivate size.';
                    $success_message = '';
                    add_debug($debug_logs, 'Soft delete failed id ' . $deleteId . ': ' . $error_message);
                }
                $stmtSoft->close();
            }
        } else {
            $error_message = 'Invalid size selected for deletion.';
            $success_message = '';
        }
        $productSizes = load_product_sizes($conn, $productId);
        recalc_product_stock($conn, $productId);
    } elseif ($action === 'update_product') {
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $genderMen = isset($_POST['gender_men']);
        $genderWomen = isset($_POST['gender_women']);
        $sport = trim($_POST['sport'] ?? '');
        $colorway = trim($_POST['colorway'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $release_date = trim($_POST['release_date'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

        if ($name === '' || $brand === '' || $price === '' || !is_numeric($price)) {
            $error_message = 'Please provide Name, Brand, and a numeric Price.';
        } elseif (!$genderMen && !$genderWomen) {
            $error_message = 'Select at least one gender (Men or Women).';
        } elseif (!ensure_secondary_gender_column($conn, $error_message)) {
            // error set
        } else {
            $upload = handle_upload($_FILES['image'] ?? null);
            if ($upload['error'] !== '') {
                $error_message = $upload['error'];
            } else {
                $image_path = $upload['path'] ?: ($product['image'] ?? '');
                $primary_gender = $genderWomen && !$genderMen ? 'Women' : 'Men';
                $secondary_gender = ($genderMen && $genderWomen) ? 'Women' : 'None';
                $sql = "UPDATE products SET name = ?, brand = ?, gender = ?, secondary_gender = ?, sport = ?, colorway = ?, description = ?, release_date = ?, image = ?, price = ?, is_featured = ?, status = ?, sku = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $error_message = $conn->error;
                    add_debug($debug_logs, 'Prepare failed (update_product): ' . $conn->error);
                } else {
                    $stmt->bind_param(
                        'sssssssssdissi',
                        $name,
                        $brand,
                        $primary_gender,
                        $secondary_gender,
                        $sport,
                        $colorway,
                        $description,
                        $release_date,
                        $image_path,
                        $price,
                        $is_featured,
                        $status,
                        $sku,
                        $productId
                    );
                    if ($stmt->execute()) {
                        $success_message = 'Product updated successfully.';
                        $product['name'] = $name;
                        $product['brand'] = $brand;
                        $product['gender'] = $primary_gender;
                        $product['secondary_gender'] = $secondary_gender;
                        $product['sport'] = $sport;
                        $product['colorway'] = $colorway;
                        $product['description'] = $description;
                        $product['release_date'] = $release_date;
                        $product['image'] = $image_path;
                        $product['price'] = $price;
                        $product['is_featured'] = $is_featured;
                        $product['status'] = $status;
                        $product['sku'] = $sku;

                        if (!$genderMen) {
                            if (!$conn->query("DELETE FROM product_sizes WHERE product_id = {$productId} AND gender = 'Men'")) {
                                $error_message = $conn->error ?: 'Failed to remove Men sizes.';
                                add_debug($debug_logs, 'Cleanup Men sizes failed: ' . $error_message);
                            }
                        }
                        if (!$genderWomen) {
                            if (!$conn->query("DELETE FROM product_sizes WHERE product_id = {$productId} AND gender = 'Women'")) {
                                $error_message = $conn->error ?: 'Failed to remove Women sizes.';
                                add_debug($debug_logs, 'Cleanup Women sizes failed: ' . $error_message);
                            }
                        }
                        if (!$error_message) {
                            recalc_product_stock($conn, $productId);
                        } else {
                            $success_message = '';
                        }
                    } else {
                        $error_message = $stmt->error ?: $conn->error ?: 'Update failed. Please try again.';
                        $success_message = '';
                        add_debug($debug_logs, 'Update product failed: ' . $error_message);
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/topbar.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1 class="admin-page-title">Edit Product</h1>
                <a class="admin-action-btn" href="products.php">Back</a>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
            <?php elseif ($error_message): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="card card-body">
                <form method="POST" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="action" value="update_product">
                    <div class="col-md-6">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($product['brand']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                    </div>
                    <?php
                        $hasMen = ($product['gender'] ?? '') === 'Men' || ($product['secondary_gender'] ?? '') === 'Men';
                        $hasWomen = ($product['gender'] ?? '') === 'Women' || ($product['secondary_gender'] ?? '') === 'Women';
                    ?>
                    <div class="col-md-4">
                        <label class="form-label">Product Target</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="gender_men" id="gender_men" <?php echo $hasMen ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="gender_men">Men</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="gender_women" id="gender_women" <?php echo $hasWomen ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="gender_women">Women</label>
                        </div>
                        <small class="text-muted">Select one or both. If both, product shows under Men and Women.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sport</label>
                        <select name="sport" class="form-select">
                            <?php $sports = ['', 'Running','Training','Lifestyle','Basketball']; ?>
                            <?php foreach ($sports as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo ($product['sport'] ?? '') === $s ? 'selected' : ''; ?>><?php echo $s === '' ? '-- Optional --' : $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Colorway</label>
                        <input type="text" name="colorway" class="form-control" value="<?php echo htmlspecialchars($product['colorway']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Release Date</label>
                        <input type="date" name="release_date" class="form-control" value="<?php echo htmlspecialchars($product['release_date']); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku']); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?php echo !empty($product['is_featured']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">Featured</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo ($product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($product['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Image</label>
                        <input type="file" name="image" accept="image/*" class="form-control">
                        <?php if (!empty($product['image'])): ?>
                            <div class="mt-2">
                                <img src="<?php echo htmlspecialchars('../' . $product['image']); ?>" alt="Current" style="width:120px; height:120px; object-fit:cover; border-radius:8px; border:1px solid #e5e5e5;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" class="admin-action-btn">Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="card card-body mt-4">
                <?php
                    $primaryGender = ($product['gender'] ?? 'Men') === 'Women' ? 'Women' : 'Men';
                    $targetMen = ($product['gender'] ?? '') === 'Men' || ($product['secondary_gender'] ?? '') === 'Men';
                    $targetWomen = ($product['gender'] ?? '') === 'Women' || ($product['secondary_gender'] ?? '') === 'Women';
                    $menSizes = [];
                    $womenSizes = [];
                    foreach ($productSizes as $ps) {
                        $rawGender = $ps['gender'] ?? 'Men';
                        $normalized = $rawGender === 'Women' ? 'Women' : ($rawGender === 'Men' ? 'Men' : $primaryGender);
                        if ($normalized === 'Women') { $womenSizes[] = $ps; }
                        else { $menSizes[] = $ps; }
                    }
                    $showMen = $targetMen || !empty($menSizes);
                    $showWomen = $targetWomen || !empty($womenSizes);
                    $totalStock = array_sum(array_map(fn($ps) => !empty($ps['is_active']) ? (int)($ps['stock_quantity'] ?? 0) : 0, $productSizes));
                ?>
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                    <h5 class="mb-0">Sizes &amp; Stock</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Total Stock</span>
                        <span class="badge bg-secondary-subtle text-dark fw-semibold px-3 py-2"><?php echo (int) $totalStock; ?></span>
                    </div>
                </div>
                <form method="POST" class="table-responsive mb-3">
                    <?php if ($showMen): ?>
                    <h6 class="fw-bold">Men's Inventory (US)</h6>
                    <table class="table align-middle mb-3 responsive-admin">
                        <thead>
                            <tr>
                                <th>Label</th><th>System</th><th>Gender</th><th>Stock</th><th>Active</th><th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($menSizes)): ?>
                                <tr><td colspan="6" class="text-muted">No Men's sizes.</td></tr>
                            <?php else: ?>
                                <?php foreach ($menSizes as $ps): ?>
                                    <tr>
                                        <td data-label="Label">
                                            <input type="hidden" name="size_id[]" value="<?php echo (int) $ps['id']; ?>">
                                            <input type="text" name="size_label[]" class="form-control" value="<?php echo htmlspecialchars($ps['size_label']); ?>" required>
                                        </td>
                                        <td data-label="System">
                                            <input type="hidden" name="size_system[]" value="US">
                                            <span class="text-muted small">US</span>
                                        </td>
                                        <td data-label="Gender">
                                            <input type="hidden" name="size_gender[]" value="Men">
                                            <span class="badge bg-light text-dark">Men</span>
                                        </td>
                                        <td data-label="Stock" style="max-width: 120px;">
                                            <input type="number" name="size_stock[]" class="form-control" value="<?php echo (int) ($ps['stock_quantity'] ?? 0); ?>" min="0">
                                        </td>
                                        <td data-label="Active" class="text-center">
                                            <input class="form-check-input" type="checkbox" name="size_active[]" value="<?php echo (int) $ps['id']; ?>" <?php echo !empty($ps['is_active']) ? 'checked' : ''; ?>>
                                        </td>
                                        <td data-label="Delete" class="text-center">
                                            <input type="hidden" name="delete_size_id" value="<?php echo (int) $ps['id']; ?>">
                                            <button type="submit" name="action" value="delete_size" class="btn btn-link text-danger p-0" aria-label="Delete size" onclick="return confirm('Delete this size?');" formaction="edit-product.php?id=<?php echo urlencode($productId); ?>" formmethod="POST" formnovalidate>
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <?php if ($showWomen): ?>
                    <h6 class="fw-bold">Women's Inventory (US)</h6>
                    <table class="table align-middle mb-3 responsive-admin">
                        <thead>
                            <tr>
                                <th>Label</th><th>System</th><th>Gender</th><th>Stock</th><th>Active</th><th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($womenSizes)): ?>
                                <tr><td colspan="6" class="text-muted">No Women's sizes.</td></tr>
                            <?php else: ?>
                                <?php foreach ($womenSizes as $ps): ?>
                                    <tr>
                                        <td data-label="Label">
                                            <input type="hidden" name="size_id[]" value="<?php echo (int) $ps['id']; ?>">
                                            <input type="text" name="size_label[]" class="form-control" value="<?php echo htmlspecialchars($ps['size_label']); ?>" required>
                                        </td>
                                        <td data-label="System">
                                            <input type="hidden" name="size_system[]" value="US">
                                            <span class="text-muted small">US</span>
                                        </td>
                                        <td data-label="Gender">
                                            <input type="hidden" name="size_gender[]" value="Women">
                                            <span class="badge bg-light text-dark">Women</span>
                                        </td>
                                        <td data-label="Stock" style="max-width: 120px;">
                                            <input type="number" name="size_stock[]" class="form-control" value="<?php echo (int) ($ps['stock_quantity'] ?? 0); ?>" min="0">
                                        </td>
                                        <td data-label="Active" class="text-center">
                                            <input class="form-check-input" type="checkbox" name="size_active[]" value="<?php echo (int) $ps['id']; ?>" <?php echo !empty($ps['is_active']) ? 'checked' : ''; ?>>
                                        </td>
                                        <td data-label="Delete" class="text-center">
                                            <input type="hidden" name="delete_size_id" value="<?php echo (int) $ps['id']; ?>">
                                            <button type="submit" name="action" value="delete_size" class="btn btn-link text-danger p-0" aria-label="Delete size" onclick="return confirm('Delete this size?');" formaction="edit-product.php?id=<?php echo urlencode($productId); ?>" formmethod="POST" formnovalidate>
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <div class="text-end">
                        <button type="submit" name="action" value="update_sizes" class="admin-action-btn">Update Sizes</button>
                    </div>
                </form>

                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="add_size">
                    <div class="col-md-3">
                        <label class="form-label">New Size Label</label>
                        <input type="text" name="new_size_label" class="form-control" placeholder="e.g. 9 or 9.5" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">System</label>
                        <input type="hidden" name="new_size_system" value="US">
                        <div class="form-control-plaintext text-muted">US</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="new_size_gender" class="form-select">
                            <?php if ($targetMen): ?><option value="Men">Men</option><?php endif; ?>
                            <?php if ($targetWomen): ?><option value="Women">Women</option><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Stock</label>
                        <input type="number" name="new_size_stock" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        <button type="submit" class="btn btn-primary">Add Size</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
