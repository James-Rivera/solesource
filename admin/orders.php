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
    <link rel="stylesheet" href="assets/css/admin.css">
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
