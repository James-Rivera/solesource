<?php
session_start();

// Mock orders data
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$all_orders = [
    ['id' => '#2401-9921', 'customer' => 'Juan Dela Cruz', 'email' => 'juan@example.com', 'date' => 'Dec 30, 2025', 'payment' => 'Paid', 'fulfillment' => 'Unfulfilled', 'total' => '₱12,000.00'],
    ['id' => '#2401-9920', 'customer' => 'Maria Santos', 'email' => 'maria@example.com', 'date' => 'Dec 30, 2025', 'payment' => 'Paid', 'fulfillment' => 'Shipped', 'total' => '₱8,500.00'],
    ['id' => '#2401-9919', 'customer' => 'Pedro Garcia', 'email' => 'pedro@example.com', 'date' => 'Dec 29, 2025', 'payment' => 'Paid', 'fulfillment' => 'Unfulfilled', 'total' => '₱15,200.00'],
    ['id' => '#2401-9918', 'customer' => 'Ana Reyes', 'email' => 'ana@example.com', 'date' => 'Dec 29, 2025', 'payment' => 'Paid', 'fulfillment' => 'Unfulfilled', 'total' => '₱6,300.00'],
    ['id' => '#2401-9917', 'customer' => 'Carlos Mendoza', 'email' => 'carlos@example.com', 'date' => 'Dec 28, 2025', 'payment' => 'Paid', 'fulfillment' => 'Shipped', 'total' => '₱4,200.00'],
];

// Filter orders
if ($filter === 'pending') {
    $orders = array_filter($all_orders, fn($o) => $o['fulfillment'] === 'Unfulfilled');
} elseif ($filter === 'shipped') {
    $orders = array_filter($all_orders, fn($o) => $o['fulfillment'] === 'Shipped');
} else {
    $orders = $all_orders;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <style>
        /* Reuse same base styles from products.php */
        body {
            font-family: var(--brand-font);
            background: #f6f6f6;
            color: var(--brand-black);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

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

        .admin-content {
            flex: 1;
            margin-left: 280px;
            padding: 3rem 2rem;
        }

        .admin-header {
            margin-bottom: 2rem;
        }

        .admin-page-title {
            font-size: 2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
        }

        /* Filter Links */
        .filter-links {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .filter-link {
            color: #666;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .filter-link:hover {
            color: var(--brand-black);
        }

        .filter-link.active {
            color: var(--brand-black);
            border-bottom-color: var(--brand-orange);
        }

        /* Orders Table */
        .orders-table-container {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
        }

        .order-table-row {
            display: grid;
            grid-template-columns: 140px 2fr 140px 140px 140px 80px;
            gap: 1.5rem;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e5e5;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .order-table-row:last-child {
            border-bottom: none;
        }

        .order-table-row:hover {
            background-color: #fafafa;
        }

        .order-table-header {
            background: #f9f9f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #666;
            letter-spacing: 1px;
        }

        .order-table-header:hover {
            background: #f9f9f9;
        }

        .order-id-cell {
            font-weight: 700;
            color: var(--brand-black);
        }

        .customer-cell {
            color: var(--brand-black);
        }

        .customer-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .customer-email {
            font-size: 0.75rem;
            color: #999;
        }

        .payment-cell {
            font-size: 0.875rem;
            color: #666;
        }

        .fulfillment-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .fulfillment-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .fulfillment-pending {
            background: var(--brand-orange);
        }

        .fulfillment-shipped {
            background: #28a745;
        }

        .total-cell {
            font-weight: 700;
            color: var(--brand-black);
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

        @media (max-width: 991px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .admin-content {
                margin-left: 0;
            }

            .order-table-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .order-table-header {
                display: none;
            }

            .order-table-row > div:before {
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
                <h1 class="admin-page-title">Fulfillment</h1>
                
                <div class="filter-links">
                    <a href="orders.php" class="filter-link <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                    <a href="orders.php?filter=pending" class="filter-link <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="orders.php?filter=shipped" class="filter-link <?php echo $filter === 'shipped' ? 'active' : ''; ?>">Shipped</a>
                </div>
            </div>

            <div class="orders-table-container">
                <!-- Table Header -->
                <div class="order-table-row order-table-header">
                    <div>Order ID</div>
                    <div>Customer</div>
                    <div>Payment</div>
                    <div>Fulfillment</div>
                    <div>Total</div>
                    <div>Action</div>
                </div>

                <!-- Order Rows -->
                <?php foreach ($orders as $order): ?>
                    <div class="order-table-row">
                        <div class="order-id-cell" data-label="Order ID">
                            <?php echo $order['id']; ?>
                        </div>
                        <div class="customer-cell" data-label="Customer">
                            <div class="customer-name"><?php echo $order['customer']; ?></div>
                            <div class="customer-email"><?php echo $order['email']; ?></div>
                        </div>
                        <div class="payment-cell" data-label="Payment">
                            <?php echo $order['payment']; ?>
                        </div>
                        <div class="fulfillment-cell" data-label="Fulfillment">
                            <span class="fulfillment-dot <?php echo $order['fulfillment'] === 'Unfulfilled' ? 'fulfillment-pending' : 'fulfillment-shipped'; ?>"></span>
                            <span><?php echo $order['fulfillment']; ?></span>
                        </div>
                        <div class="total-cell" data-label="Total">
                            <?php echo $order['total']; ?>
                        </div>
                        <div data-label="Action">
                            <a href="order-details.php?id=<?php echo urlencode($order['id']); ?>" class="action-link">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
