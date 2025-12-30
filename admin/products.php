<?php
session_start();

// Mock product inventory
$products = [
    ['id' => 1, 'image' => '../assets/img/products/best/air-force-1.png', 'name' => 'NIKE AIR FORCE 1', 'stock' => 45, 'price' => '₱4,999.00', 'status' => 'Active'],
    ['id' => 2, 'image' => '../assets/img/products/new/adidas-gazelle.png', 'name' => 'ADIDAS GAZELLE INDOOR', 'stock' => 2, 'price' => '₱5,500.00', 'status' => 'Active'],
    ['id' => 3, 'image' => '../assets/img/products/best/jordan-1-high.png', 'name' => 'AIR JORDAN 1 HIGH', 'stock' => 23, 'price' => '₱8,295.00', 'status' => 'Active'],
    ['id' => 4, 'image' => '../assets/img/products/new/jordan-11-legend-blue.png', 'name' => 'JORDAN 11 RETRO', 'stock' => 8, 'price' => '₱12,000.00', 'status' => 'Active'],
    ['id' => 5, 'image' => '../assets/img/products/new/asics-gel-kayano-14.png', 'name' => 'ASICS GEL-KAYANO 14', 'stock' => 0, 'price' => '₱9,490.00', 'status' => 'Inactive'],
];
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
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1 class="admin-page-title">Inventory</h1>
                <button class="btn btn-add-product" type="button">Add Product</button>
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
                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                        </div>
                        <div class="product-name-cell" data-label="Product Name">
                            <?php echo $product['name']; ?>
                        </div>
                        <div class="stock-cell" data-label="Stock">
                            <?php if ($product['stock'] === 0): ?>
                                <span class="stock-out">Out of Stock</span>
                            <?php elseif ($product['stock'] < 10): ?>
                                <span class="stock-low">Low (<?php echo $product['stock']; ?>)</span>
                            <?php else: ?>
                                <span class="stock-good">Good (<?php echo $product['stock']; ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="price-cell" data-label="Price">
                            <?php echo $product['price']; ?>
                        </div>
                        <div class="status-cell" data-label="Status">
                            <span class="status-dot <?php echo $product['status'] === 'Active' ? 'status-active' : 'status-inactive'; ?>"></span>
                            <span><?php echo $product['status']; ?></span>
                        </div>
                        <div data-label="Action">
                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-link">Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
