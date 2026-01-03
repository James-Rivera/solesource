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
    $newname = uniqid('prod_', true) . '.' . $ext;
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

$productSizes = load_product_sizes($conn, $productId);

if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_sizes') {
    $ids = $_POST['size_id'] ?? [];
    $labels = $_POST['size_label'] ?? [];
    $systems = $_POST['size_system'] ?? [];
    $genders = $_POST['size_gender'] ?? [];
    $stocks = $_POST['size_stock'] ?? [];
    $activeIds = array_map('intval', $_POST['size_active'] ?? []);

    $stmtUpdate = $conn->prepare("UPDATE product_sizes SET size_label = ?, size_system = ?, gender = ?, stock_quantity = ?, is_active = ? WHERE id = ? AND product_id = ?");
    foreach ($ids as $idx => $sid) {
        $sid = (int) $sid;
        $label = trim($labels[$idx] ?? '');
        $system = in_array(($systems[$idx] ?? 'US'), ['US','EU','UK','CM'], true) ? $systems[$idx] : 'US';
        $genderVal = in_array(($genders[$idx] ?? 'Unisex'), ['Men','Women','Unisex'], true) ? $genders[$idx] : 'Unisex';
        $stockVal = (int) ($stocks[$idx] ?? 0);
        $isActive = in_array($sid, $activeIds, true) ? 1 : 0;
        $stmtUpdate->bind_param('sssiiii', $label, $system, $genderVal, $stockVal, $isActive, $sid, $productId);
        $stmtUpdate->execute();
    }
    $stmtUpdate->close();
    $success_message = 'Sizes updated.';
    $productSizes = load_product_sizes($conn, $productId);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_size') {
    $label = trim($_POST['new_size_label'] ?? '');
    $system = in_array(($_POST['new_size_system'] ?? 'US'), ['US','EU','UK','CM'], true) ? $_POST['new_size_system'] : 'US';
    $genderVal = in_array(($_POST['new_size_gender'] ?? 'Unisex'), ['Men','Women','Unisex'], true) ? $_POST['new_size_gender'] : 'Unisex';
    $stockVal = (int) ($_POST['new_size_stock'] ?? 0);
    if ($label === '') {
        $error_message = 'Size label is required.';
    } else {
        $stmtAdd = $conn->prepare("INSERT INTO product_sizes (product_id, size_label, size_system, gender, stock_quantity, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmtAdd->bind_param('isssi', $productId, $label, $system, $genderVal, $stockVal);
        if ($stmtAdd->execute()) {
            $success_message = 'Size added.';
        } else {
            $error_message = 'Failed to add size (duplicate size/system/gender?).';
        }
        $stmtAdd->close();
    }
    $productSizes = load_product_sizes($conn, $productId);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_size') {
    $deleteId = (int) ($_POST['delete_size_id'] ?? 0);
    if ($deleteId > 0) {
        $stmtDel = $conn->prepare("DELETE FROM product_sizes WHERE id = ? AND product_id = ?");
        $stmtDel->bind_param('ii', $deleteId, $productId);
        if ($stmtDel->execute()) {
            $success_message = 'Size deleted.';
        } else {
            $error_message = 'Failed to delete size.';
        }
        $stmtDel->close();
    } else {
        $error_message = 'Invalid size selected for deletion.';
    }
    $productSizes = load_product_sizes($conn, $productId);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_product') {
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $gender = trim($_POST['gender'] ?? 'Unisex');
    $sport = trim($_POST['sport'] ?? '');
    $colorway = trim($_POST['colorway'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

    if ($name === '' || $brand === '' || $price === '' || !is_numeric($price)) {
        $error_message = 'Please provide Name, Brand, and a numeric Price.';
    } else {
        $upload = handle_upload($_FILES['image'] ?? null);
        if ($upload['error'] !== '') {
            $error_message = $upload['error'];
        } else {
            $image_path = $upload['path'] ?: ($product['image'] ?? '');
            $sql = "UPDATE products SET name = ?, brand = ?, gender = ?, sport = ?, colorway = ?, description = ?, release_date = ?, image = ?, price = ?, stock_quantity = ?, is_featured = ?, status = ?, sku = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'ssssssssdiissi',
                $name,
                $brand,
                $gender,
                $sport,
                $colorway,
                $description,
                $release_date,
                $image_path,
                $price,
                $stock_quantity,
                $is_featured,
                $status,
                $sku,
                $productId
            );
            if ($stmt->execute()) {
                $success_message = 'Product updated successfully.';
                $product['name'] = $name;
                $product['brand'] = $brand;
                $product['gender'] = $gender;
                $product['sport'] = $sport;
                $product['colorway'] = $colorway;
                $product['description'] = $description;
                $product['release_date'] = $release_date;
                $product['image'] = $image_path;
                $product['price'] = $price;
                $product['stock_quantity'] = $stock_quantity;
                $product['is_featured'] = $is_featured;
                $product['status'] = $status;
                $product['sku'] = $sku;
            } else {
                $error_message = 'Update failed. Please try again.';
            }
            $stmt->close();
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
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <?php $genders = ['Men','Women','Unisex']; ?>
                            <?php foreach ($genders as $g): ?>
                                <option value="<?php echo $g; ?>" <?php echo $product['gender'] === $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                            <?php endforeach; ?>
                        </select>
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
                    <div class="col-md-4">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" value="<?php echo (int) $product['stock_quantity']; ?>" min="0">
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Sizes &amp; Stock</h5>
                </div>
                <form method="POST" class="table-responsive mb-3">
                    <table class="table align-middle mb-2">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>System</th>
                                <th>Gender</th>
                                <th>Stock</th>
                                <th>Active</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productSizes)): ?>
                                <tr><td colspan="6" class="text-muted">No sizes yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($productSizes as $ps): ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="size_id[]" value="<?php echo (int) $ps['id']; ?>">
                                            <input type="text" name="size_label[]" class="form-control" value="<?php echo htmlspecialchars($ps['size_label']); ?>" required>
                                        </td>
                                        <td>
                                            <select name="size_system[]" class="form-select">
                                                <?php foreach (['US','EU','UK','CM'] as $sys): ?>
                                                    <option value="<?php echo $sys; ?>" <?php echo ($ps['size_system'] ?? 'US') === $sys ? 'selected' : ''; ?>><?php echo $sys; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="size_gender[]" class="form-select">
                                                <?php foreach (['Men','Women','Unisex'] as $g): ?>
                                                    <option value="<?php echo $g; ?>" <?php echo ($ps['gender'] ?? 'Unisex') === $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td style="max-width: 120px;">
                                            <input type="number" name="size_stock[]" class="form-control" value="<?php echo (int) ($ps['stock_quantity'] ?? 0); ?>" min="0">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="size_active[]" value="<?php echo (int) $ps['id']; ?>" <?php echo !empty($ps['is_active']) ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="text-center">
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
                    <div class="text-end">
                        <button type="submit" name="action" value="update_sizes" class="admin-action-btn">Update Sizes</button>
                    </div>
                </form>

                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="add_size">
                    <div class="col-md-3">
                        <label class="form-label">New Size Label</label>
                        <input type="text" name="new_size_label" class="form-control" placeholder="e.g. US M 9" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">System</label>
                        <select name="new_size_system" class="form-select">
                            <?php foreach (['US','EU','UK','CM'] as $sys): ?>
                                <option value="<?php echo $sys; ?>"><?php echo $sys; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="new_size_gender" class="form-select">
                            <?php foreach (['Men','Women','Unisex'] as $g): ?>
                                <option value="<?php echo $g; ?>"><?php echo $g; ?></option>
                            <?php endforeach; ?>
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
