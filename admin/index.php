<?php
session_start();

// Mock admin check - replace with actual authentication
$isAdmin = true;
if (!$isAdmin) {
    header('Location: ../login.php');
    exit;
}

// Mock dashboard data
$stats = [
    ['label' => 'Total Revenue', 'value' => '₱150,200', 'icon' => 'bi-currency-dollar'],
    ['label' => 'Pending Orders', 'value' => '12', 'icon' => 'bi-clock-history'],
    ['label' => 'Total Users', 'value' => '1,284', 'icon' => 'bi-people'],
    ['label' => 'Low Stock', 'value' => '5', 'icon' => 'bi-exclamation-triangle'],
];

$recent_orders = [
    ['id' => '#2401-9921', 'customer' => 'Juan Dela Cruz', 'date' => 'Dec 30, 2025', 'status' => 'Processing', 'total' => '₱12,000'],
    ['id' => '#2401-9920', 'customer' => 'Maria Santos', 'date' => 'Dec 30, 2025', 'status' => 'Shipped', 'total' => '₱8,500'],
    ['id' => '#2401-9919', 'customer' => 'Pedro Garcia', 'date' => 'Dec 29, 2025', 'status' => 'Delivered', 'total' => '₱15,200'],
    ['id' => '#2401-9918', 'customer' => 'Ana Reyes', 'date' => 'Dec 29, 2025', 'status' => 'Processing', 'total' => '₱6,300'],
    ['id' => '#2401-9917', 'customer' => 'Carlos Mendoza', 'date' => 'Dec 28, 2025', 'status' => 'Cancelled', 'total' => '₱4,200'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <style>
        /* Global Styles */
        body {
            font-family: var(--brand-font);
            background: #fff;
            color: var(--brand-black);
        }

        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar - Dark Theme */
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
            font-weight: 600;
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
        }

        .admin-page-title {
            font-size: 2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
            margin-bottom: 0.5rem;
        }

        .admin-page-subtitle {
            color: #666;
            font-size: 1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: #fff;
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            padding: 2rem;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            border-color: #e0e0e0;
        }

        .stat-label {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 1px;
            margin-bottom: 0.875rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--brand-black);
            line-height: 1;
        }

        /* Recent Orders Section */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
        }

        .section-link {
            color: var(--brand-black);
            text-decoration: underline;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .section-link:hover {
            color: var(--brand-orange);
        }



        /* Orders List */
        .orders-list {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
        }

        .order-row {
            display: grid;
            grid-template-columns: 120px 1fr 140px 120px 120px 60px;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .order-row:last-child {
            border-bottom: none;
        }

        .order-row:hover {
            background-color: #fafafa;
        }

        .order-row-header {
            background: #f9f9f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #666;
            letter-spacing: 0.5px;
        }

        .order-row-header:hover {
            background: #f9f9f9;
        }

        .order-id {
            font-weight: 700;
            color: var(--brand-black);
        }

        .order-customer {
            color: var(--brand-black);
        }

        .order-date {
            font-size: 0.875rem;
            color: #666;
        }

        .order-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .order-status-dot {
            font-size: 1rem;
            line-height: 1;
        }

        .order-total {
            font-weight: 700;
            color: var(--brand-black);
        }

        .order-action {
            color: #666;
            text-decoration: none;
            font-size: 1.125rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .order-action:hover {
            color: var(--brand-black);
        }

        .order-action i {
            transition: transform 0.2s ease;
        }

        .order-action:hover i {
            transform: translateX(3px);
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

            .order-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .order-row-header {
                display: none;
            }

            .order-row > div:before {
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

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <h1 class="admin-page-title">Overview</h1>
                <p class="admin-page-subtitle">Monitor your store's performance and recent activity</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                    <div class="stat-card">
                        <div class="stat-label"><?php echo $stat['label']; ?></div>
                        <div class="stat-value"><?php echo $stat['value']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Orders -->
            <section>
                <div class="section-header">
                    <h2 class="section-title">Recent Orders</h2>
                    <a href="orders.php" class="section-link">View All</a>
                </div>

                <div class="orders-list">
                    <!-- Table Header -->
                    <div class="order-row order-row-header">
                        <div>Order ID</div>
                        <div>Customer</div>
                        <div>Date</div>
                        <div>Status</div>
                        <div>Total</div>
                        <div>Action</div>
                    </div>

                    <!-- Order Rows -->
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-row">
                            <div class="order-id" data-label="Order ID"><?php echo $order['id']; ?></div>
                            <div class="order-customer" data-label="Customer"><?php echo $order['customer']; ?></div>
                            <div class="order-date" data-label="Date"><?php echo $order['date']; ?></div>
                            <div class="order-status" data-label="Status">
                                <?php if ($order['status'] === 'Processing'): ?>
                                    <span class="order-status-dot text-warning">●</span>
                                    <span>Processing</span>
                                <?php elseif ($order['status'] === 'Shipped'): ?>
                                    <span class="order-status-dot text-info">●</span>
                                    <span>Shipped</span>
                                <?php elseif ($order['status'] === 'Delivered'): ?>
                                    <span class="order-status-dot text-success">●</span>
                                    <span>Delivered</span>
                                <?php elseif ($order['status'] === 'Cancelled'): ?>
                                    <span class="order-status-dot text-danger">●</span>
                                    <span>Cancelled</span>
                                <?php endif; ?>
                            </div>
                            <div class="order-total" data-label="Total"><?php echo $order['total']; ?></div>
                            <div data-label="Action">
                                <a href="order-details.php?id=<?php echo urlencode($order['id']); ?>" class="order-action">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
