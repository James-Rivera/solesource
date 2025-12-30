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
    <style>
        /* Global Styles */
        body {
            font-family: var(--brand-font);
            background: #f6f6f6;
            color: var(--brand-black);
        }

        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar - Swiss/Minimal */
        .admin-sidebar {
            width: 280px;
            background: var(--brand-dark-gray);
            color: #fff;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .admin-logo {
            padding: 0 2rem;
            margin-bottom: 3rem;
        }

        .admin-logo img {
            height: 32px;
            filter: brightness(0) invert(1);
        }

        .admin-sidebar-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--brand-orange);
            padding: 0 2rem;
            margin-bottom: 1.5rem;
        }

        .admin-nav {
            display: flex;
            flex-direction: column;
        }

        .admin-nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 0.875rem 2rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.2s ease;
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
            border: none;
            background: transparent;
        }

        .admin-nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 2rem;
            right: 2rem;
            height: 2px;
            background-color: var(--brand-orange);
            width: 0;
            transition: width 0.3s ease;
        }

        .admin-nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-nav-link:hover::after {
            width: calc(100% - 4rem);
        }

        .admin-nav-link.active {
            color: #fff;
            font-weight: 700;
            background: rgba(233, 113, 63, 0.1);
        }

        .admin-nav-link.active::after {
            width: calc(100% - 4rem);
        }

        .admin-nav-link.logout {
            color: #ff6b6b;
            margin-top: auto;
        }

        .admin-nav-link.logout:hover {
            color: #ff5252;
            background: rgba(255, 107, 107, 0.1);
        }

        /* Main Content */
        .admin-content {
            flex: 1;
            margin-left: 280px;
            padding: 3rem 2rem;
        }

        .admin-header {
            margin-bottom: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-page-title {
            font-size: 2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
            letter-spacing: 1px;
        }

        .btn-add-product {
            background: var(--brand-black);
            color: #fff;
            padding: 0.75rem 2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .btn-add-product:hover {
            background: #000;
            transform: translateY(-1px);
        }

        /* Product Table */
        .products-table-container {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
        }

        .product-table-row {
            display: grid;
            grid-template-columns: 80px 2fr 120px 140px 120px 80px;
            gap: 1.5rem;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e5e5;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .product-table-row:last-child {
            border-bottom: none;
        }

        .product-table-row:hover {
            background-color: #fafafa;
        }

        .product-table-header {
            background: #f9f9f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #666;
            letter-spacing: 1px;
        }

        .product-table-header:hover {
            background: #f9f9f9;
        }

        .product-image-cell {
            width: 80px;
            height: 80px;
        }

        .product-image-cell img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 0.5rem;
            background: #fafafa;
        }

        .product-name-cell {
            font-weight: 700;
            color: var(--brand-black);
            text-transform: uppercase;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .stock-cell {
            font-size: 0.875rem;
        }

        .stock-low {
            color: #dc3545;
            font-weight: 600;
        }

        .stock-good {
            color: #28a745;
            font-weight: 600;
        }

        .stock-out {
            color: #6c757d;
            font-weight: 600;
        }

        .price-cell {
            font-weight: 700;
            color: var(--brand-black);
        }

        .status-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-active {
            background: #28a745;
        }

        .status-inactive {
            background: #6c757d;
        }

        .action-link {
            color: var(--brand-black);
            text-decoration: underline;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-link:hover {
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .admin-content {
                margin-left: 0;
            }

            .product-table-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .product-table-header {
                display: none;
            }

            .product-table-row > div:before {
                content: attr(data-label);
                font-weight: 700;
                text-transform: uppercase;
                font-size: 0.75rem;
                color: #666;
                display: block;
                margin-bottom: 0.25rem;
            }
        }
    </style>
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
