<?php
session_start();
require_once '../includes/connect.php';

// Security gate: only admins
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$revenue = 0;
$pendingOrders = 0;
$totalOrders = 0;
$lowStock = 0;

$res = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS revenue FROM orders WHERE status IN ('shipped','delivered')");
if ($res && $row = $res->fetch_assoc()) {
    $revenue = (float) $row['revenue'];
}

$res = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
if ($res && $row = $res->fetch_assoc()) {
    $totalOrders = (int) $row['cnt'];
}

$res = $conn->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
if ($res && $row = $res->fetch_assoc()) {
    $pendingOrders = (int) $row['cnt'];
}

$res = $conn->query("SELECT COUNT(*) AS cnt FROM product_sizes WHERE is_active = 1 AND stock_quantity < 3");
if ($res && $row = $res->fetch_assoc()) {
    $lowStock = (int) $row['cnt'];
}

$stats = [
    ['label' => 'Total Sales', 'value' => '₱' . number_format($revenue, 2, '.', ','), 'icon' => 'bi-currency-dollar'],
    ['label' => 'Total Orders', 'value' => $totalOrders, 'icon' => 'bi-receipt'],
    ['label' => 'Pending Orders', 'value' => $pendingOrders, 'icon' => 'bi-clock-history'],
    ['label' => 'Stock Alerts (<3)', 'value' => $lowStock, 'icon' => 'bi-exclamation-triangle'],
];

$recent_orders = [];
$stmt = $conn->prepare("SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at, u.full_name FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.created_at DESC LIMIT 5");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['date_formatted'] = $row['created_at'] ? date('M d, Y', strtotime($row['created_at'])) : '';
        $row['total_formatted'] = '₱' . number_format((float) $row['total_amount'], 0, '.', ',');
        $recent_orders[] = $row;
    }
    $stmt->close();
}
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
    <?php include 'includes/topbar.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <div>
                    <h1 class="admin-page-title">Overview</h1>
                    <p class="admin-page-subtitle">Monitor your store's performance and recent activity</p>
                </div>
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

            <!-- Quick Actions -->
            <section class="quick-actions mb-4">
                <div class="section-header">
                    <h2 class="section-title-small">Quick Actions</h2>
                </div>
                <div class="stats-grid quick-actions-grid">
                    <a class="stat-card quick-action-card" href="products.php#addProductForm">
                        <div>
                            <div class="stat-label">Catalog</div>
                            <div class="stat-value" style="font-size:1.3rem;">Add New Product</div>
                        </div>
                        <i class="bi bi-plus-circle"></i>
                    </a>
                    <a class="stat-card quick-action-card" href="orders.php?filter=pending">
                        <div>
                            <div class="stat-label">Orders</div>
                            <div class="stat-value" style="font-size:1.3rem;">Check Pending</div>
                        </div>
                        <i class="bi bi-truck"></i>
                    </a>
                    <a class="stat-card quick-action-card" href="products.php?filter=lowstock">
                        <div>
                            <div class="stat-label">Inventory</div>
                            <div class="stat-value" style="font-size:1.3rem;">View Stock Alerts</div>
                        </div>
                        <i class="bi bi-exclamation-triangle"></i>
                    </a>
                </div>
            </section>

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
                        <?php $status = strtolower($order['status'] ?? ''); ?>
                        <div class="order-row">
                            <div class="order-id" data-label="Order ID"><?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="order-customer" data-label="Customer"><?php echo htmlspecialchars($order['full_name']); ?></div>
                            <div class="order-date" data-label="Date"><?php echo htmlspecialchars($order['date_formatted']); ?></div>
                            <div class="order-status" data-label="Status">
                                <span class="status-pill status-<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                            </div>
                            <div class="order-total" data-label="Total"><?php echo htmlspecialchars($order['total_formatted']); ?></div>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const layout = document.querySelector('.admin-container');
            const toggles = document.querySelectorAll('[data-toggle-sidebar]');

            toggles.forEach(btn => {
                btn.addEventListener('click', () => {
                    layout.classList.toggle('sidebar-collapsed');
                });
            });
        });
    </script>
</body>

</html>