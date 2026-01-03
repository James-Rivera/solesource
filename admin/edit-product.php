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

if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_product') {
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
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

    if ($name === '' || $brand === '' || $price === '' || !is_numeric($price)) {
        $error_message = 'Please provide Name, Brand, and a numeric Price.';
    } else {
        $upload = handle_upload($_FILES['image'] ?? null);
        if ($upload['error'] !== '') {
            $error_message = $upload['error'];
        } else {
            $image_path = $upload['path'] ?: ($product['image'] ?? '');
            $sql = "UPDATE products SET name = ?, brand = ?, gender = ?, colorway = ?, description = ?, release_date = ?, image = ?, price = ?, stock_quantity = ?, is_featured = ?, status = ?, sku = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'sssssssdisdsi',
                $name,
                $brand,
                $gender,
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
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
