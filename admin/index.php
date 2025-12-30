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
    <link rel="stylesheet" href="assets/css/admin.css">
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