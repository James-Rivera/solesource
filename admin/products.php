<?php
session_start();
require_once '../includes/connect.php';

// Security gate: admins only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$success_message = '';
$error_message = '';

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
        return false;
    }
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

function generate_sku($brand, $name) {
    $brand_part = strtoupper(preg_replace('/[^A-Z0-9]/i', '', substr($brand, 0, 2)) ?: 'SS');
    $name_part = strtoupper(preg_replace('/[^A-Z0-9]/i', '', substr($name, 0, 3)) ?: 'PRD');
    $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    return $brand_part . '-' . $name_part . '-' . $rand;
}

function recalc_product_stock(mysqli $conn, int $productId): void {
    $stmt = $conn->prepare("UPDATE products p SET stock_quantity = (SELECT COALESCE(SUM(stock_quantity),0) FROM product_sizes WHERE product_id = p.id AND is_active = 1) WHERE p.id = ?");
	$stmt->bind_param('i', $productId);
	$stmt->execute();
	$stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
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
    $status = 'active';

    if ($name === '' || $brand === '' || $price === '' || !is_numeric($price)) {
        $error_message = 'Please provide Name, Brand, and a numeric Price.';
    } elseif (!$genderMen && !$genderWomen) {
        $error_message = 'Select at least one gender (Men or Women).';
    } elseif (!ensure_secondary_gender_column($conn, $error_message)) {
        // error message already set
    } else {
        if ($sku === '') {
            $sku = generate_sku($brand, $name);
        }

        $primary_gender = $genderWomen && !$genderMen ? 'Women' : 'Men';
        // enforce enum to Men/Women only (no Unisex/Both)
        $primary_gender = $primary_gender === 'Women' ? 'Women' : 'Men';
        $secondary_gender = ($genderMen && $genderWomen) ? 'Women' : 'None';

        $image_path = '';
        if (!empty($_FILES['image']['name'])) {
            if (!empty($_FILES['image']['error']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $error_message = 'Image upload failed. Please try again.';
            } else {
                $upload_dir = '../assets/img/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $basename = basename($_FILES['image']['name']);
                $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allowed)) {
                    $error_message = 'Invalid image type. Allowed: jpg, jpeg, png, webp.';
                } else {
					$slug = slugify($brand . '-' . $name);
					$newname = $slug . '-' . uniqid() . '.' . $ext;
					$target = $upload_dir . $newname;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        // Save relative path for frontend
                        $image_path = 'assets/img/products/' . $newname;
                    } else {
                        $error_message = 'Failed to upload image.';
                    }
                }
            }
        }

        if ($error_message === '') {
            // stock_quantity now calculated from product_sizes; omit from INSERT
            $sql = "INSERT INTO products (sku, name, brand, gender, secondary_gender, sport, colorway, description, release_date, image, price, is_featured, total_sold, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'active')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'ssssssssssdi',
                $sku,
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
                $is_featured
            );
        }

        if ($error_message === '') {
            if ($stmt->execute()) {
                $newProductId = (int) ($stmt->insert_id ?: $conn->insert_id);
                if ($newProductId) { recalc_product_stock($conn, $newProductId); }
                header('Location: edit-product.php?id=' . $newProductId);
                exit;
            } else {
                $error_message = $stmt->error ?: $conn->error ?: 'Insert failed. Please try again.';
            }
            $stmt->close();
        }
    }
}

// Fetch products with aggregated size stock (fallback to legacy stock_quantity)
$filter = $_GET['filter'] ?? '';
$products = [];
$lowStockSizes = [];

if ($filter === 'lowstock') {
    $alertSql = "SELECT ps.id, ps.product_id, ps.size_label, ps.gender, ps.stock_quantity, p.name AS product_name, p.image, p.brand
                 FROM product_sizes ps
                 JOIN products p ON p.id = ps.product_id
                 WHERE ps.is_active = 1 AND ps.stock_quantity < 3
                 ORDER BY ps.stock_quantity ASC, CAST(ps.size_label AS DECIMAL(10,2)) ASC";
    $alertRes = $conn->query($alertSql);
    if ($alertRes && $alertRes->num_rows > 0) {
        while ($row = $alertRes->fetch_assoc()) {
            $lowStockSizes[] = $row;
        }
    }
}

$sqlProducts = "SELECT p.*, COALESCE(SUM(ps.stock_quantity), p.stock_quantity) AS stock_total
                FROM products p
                LEFT JOIN product_sizes ps ON ps.product_id = p.id AND ps.is_active = 1
                GROUP BY p.id";
if ($filter === 'lowstock') {
    $sqlProducts .= " HAVING COALESCE(SUM(ps.stock_quantity), p.stock_quantity) < 3";
}
$sqlProducts .= " ORDER BY p.created_at DESC";
$result = $conn->query($sqlProducts);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['price_formatted'] = '₱' . number_format((float)$row['price'], 2, '.', ',');
        $row['stock_total'] = (int) ($row['stock_total'] ?? 0);
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Products</title>
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
                <div>
                    <h1 class="admin-page-title">Products</h1>
                    <p class="admin-page-subtitle">Manage your catalog</p>
                </div>
                <button class="btn btn-add-product" type="button" data-bs-toggle="collapse" data-bs-target="#addProductForm" aria-expanded="false" aria-controls="addProductForm">Create Product</button>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
            <?php elseif ($error_message): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="collapse mb-4" id="addProductForm">
                <div class="card card-body">
                    <form method="POST" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="action" value="add_product">
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Target</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="gender_men" id="gender_men" checked>
                                <label class="form-check-label" for="gender_men">Men</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="gender_women" id="gender_women">
                                <label class="form-check-label" for="gender_women">Women</label>
                            </div>
                            <small class="text-muted">Select one or both. If both, product will show for Men and Women.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sport</label>
                            <select name="sport" class="form-select">
                                <option value="">-- Optional --</option>
                                <option value="Running">Running</option>
                                <option value="Training">Training</option>
                                <option value="Lifestyle">Lifestyle</option>
                                <option value="Basketball">Basketball</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Colorway</label>
                            <input type="text" name="colorway" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Release Date</label>
                            <input type="date" name="release_date" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SKU (optional)</label>
                            <input type="text" name="sku" class="form-control" placeholder="Auto-generated if left blank">
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
                                <label class="form-check-label" for="is_featured">Featured</label>
                            </div>
                            <div>
                                <label class="form-label d-block">Image</label>
                                <input type="file" name="image" accept="image/*" class="form-control">
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($filter === 'lowstock'): ?>
                <div class="card card-body mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="mb-0">Low Stock Sizes ( &lt; 3 )</h5>
                        <span class="badge bg-warning text-dark px-3 py-2">Threshold: 3</span>
                    </div>
                    <?php if (empty($lowStockSizes)): ?>
                        <div class="text-muted">No low stock sizes found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Gender</th>
                                        <th>Stock</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockSizes as $ls): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($ls['image'])): ?>
                                                        <img src="<?php echo htmlspecialchars('../' . $ls['image']); ?>" alt="<?php echo htmlspecialchars($ls['product_name'] ?? ''); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($ls['product_name'] ?? ''); ?></div>
                                                        <?php if (!empty($ls['brand'])): ?><div class="text-muted small"><?php echo htmlspecialchars($ls['brand']); ?></div><?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($ls['size_label'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($ls['gender'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger fw-semibold px-3 py-2"><?php echo (int) ($ls['stock_quantity'] ?? 0); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <a class="action-link" href="edit-product.php?id=<?php echo urlencode((string) ($ls['product_id'] ?? 0)); ?>">Edit Product</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="products-table-container">
                <!-- Table Header -->
                <div class="product-table-row product-table-header">
                    <div>Image</div>
                    <div>Product Name</div>
                    <div>Stock</div>
                    <div>Price</div>
                    <div>Status</div>
                    <div>Action</div>
                </div>

                <!-- Product Rows -->
                <?php foreach ($products as $product): ?>
                    <div class="product-table-row">
                        <div class="product-image-cell" data-label="Image">
                            <img src="<?php echo htmlspecialchars('../' . $product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="product-name-cell" data-label="Product Name">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </div>
                        <div class="stock-cell" data-label="Stock">
                            <?php $stockVal = (int)($product['stock_total'] ?? $product['stock_quantity'] ?? 0); ?>
                            <?php if ($stockVal === 0): ?>
                                <span class="stock-out">Out of Stock</span>
                            <?php elseif ($stockVal < 10): ?>
                                <span class="stock-low">Low (<?php echo $stockVal; ?>)</span>
                            <?php else: ?>
                                <span class="stock-good">Good (<?php echo $stockVal; ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="price-cell" data-label="Price">
                            <?php echo htmlspecialchars($product['price_formatted'] ?? $product['price']); ?>
                        </div>
                        <div class="status-cell" data-label="Status">
                            <?php $active = strtolower($product['status'] ?? '') === 'active'; ?>
                            <span class="pill pill-sm <?php echo $active ? 'pill-active' : 'pill-inactive'; ?>"><?php echo $active ? 'Active' : 'Inactive'; ?></span>
                        </div>
                        <div data-label="Action">
                            <a href="edit-product.php?id=<?php echo urlencode($product['id']); ?>" class="action-link">Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
