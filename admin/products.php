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

function generate_sku($brand, $name) {
    $brand_part = strtoupper(preg_replace('/[^A-Z0-9]/i', '', substr($brand, 0, 2)) ?: 'SS');
    $name_part = strtoupper(preg_replace('/[^A-Z0-9]/i', '', substr($name, 0, 3)) ?: 'PRD');
    $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    return $brand_part . '-' . $name_part . '-' . $rand;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $gender = trim($_POST['gender'] ?? 'Unisex');
    $colorway = trim($_POST['colorway'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = 'active';

    if ($name === '' || $brand === '' || $price === '' || !is_numeric($price)) {
        $error_message = 'Please provide Name, Brand, and a numeric Price.';
    } else {
        if ($sku === '') {
            $sku = generate_sku($brand, $name);
        }

        $image_path = '';
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = '../assets/img/products/';
            $basename = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error_message = 'Invalid image type. Allowed: jpg, jpeg, png, webp.';
            } else {
                $newname = uniqid('prod_', true) . '.' . $ext;
                $target = $upload_dir . $newname;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    // Save relative path for frontend
                    $image_path = 'assets/img/products/' . $newname;
                } else {
                    $error_message = 'Failed to upload image.';
                }
            }
        }

        if ($error_message === '') {
            $sql = "INSERT INTO products (sku, name, brand, gender, colorway, description, release_date, image, price, stock_quantity, is_featured, total_sold, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'active')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'ssssssssdid',
                $sku,
                $name,
                $brand,
                $gender,
                $colorway,
                $description,
                $release_date,
                $image_path,
                $price,
                $stock_quantity,
                $is_featured
            );
        }

        if ($error_message === '') {
            if ($stmt->execute()) {
                $success_message = 'Product added successfully.';
            } else {
                $error_message = 'Insert failed. Please try again.';
            }
            $stmt->close();
        }
    }
}

// Fetch products from DB
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['price_formatted'] = '₱' . number_format((float)$row['price'], 2, '.', ',');
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
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Men">Men</option>
                                <option value="Women">Women</option>
                                <option value="Unisex" selected>Unisex</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" value="0" min="0">
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
                            <?php $stockVal = (int)($product['stock_quantity'] ?? 0); ?>
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
