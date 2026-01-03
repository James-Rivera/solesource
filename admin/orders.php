<?php
session_start();
require_once '../includes/connect.php';

// Security gate: admins only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$filter = isset($_GET['filter']) ? strtolower($_GET['filter']) : 'all';
$allowedFilters = ['all', 'pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$orders = [];
$sql = "SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at, o.tracking_number, o.courier, o.payment_method, u.full_name, u.email, COALESCE(SUM(oi.quantity), 0) AS item_count FROM orders o JOIN users u ON u.id = o.user_id LEFT JOIN order_items oi ON oi.order_id = o.id";
$params = [];
$types = '';
if ($filter !== 'all') {
    $sql .= " WHERE o.status = ?";
    $params[] = $filter;
    $types .= 's';
}
$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['total_formatted'] = '₱' . number_format((float) ($row['total_amount'] ?? 0), 2, '.', ',');
        $row['date_formatted'] = $row['created_at'] ? date('M d, Y', strtotime($row['created_at'])) : '';
        $orders[] = $row;
    }
    $stmt->close();
}

$msg = $_GET['msg'] ?? '';
$toastMessage = '';
$toastVariant = 'primary';
if ($msg === 'updated') {
    $toastMessage = 'Order updated successfully.';
    $toastVariant = 'success';
} elseif ($msg === 'invalid') {
    $toastMessage = 'Invalid request.';
    $toastVariant = 'danger';
} elseif ($msg === 'notfound') {
    $toastMessage = 'Order not found.';
    $toastVariant = 'warning';
} elseif ($msg === 'error') {
    $toastMessage = 'Update failed. Please try again.';
    $toastVariant = 'danger';
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
                    <a href="orders.php?filter=confirmed" class="filter-link <?php echo $filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                    <a href="orders.php?filter=shipped" class="filter-link <?php echo $filter === 'shipped' ? 'active' : ''; ?>">Shipped</a>
                    <a href="orders.php?filter=delivered" class="filter-link <?php echo $filter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
                    <a href="orders.php?filter=cancelled" class="filter-link <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                </div>
            </div>

            <?php if ($toastMessage): ?>
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000;">
                    <div id="statusToast" class="toast align-items-center text-bg-<?php echo htmlspecialchars($toastVariant); ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2600">
                        <div class="d-flex">
                            <div class="toast-body"><?php echo htmlspecialchars($toastMessage); ?></div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="orders-table-container">
                <!-- Table Header -->
                <div class="order-table-row order-table-header">
                    <div>Order</div>
                    <div>Customer</div>
                    <div>Payment</div>
                    <div>Fulfillment</div>
                    <div>Total</div>
                    <div>Action</div>
                </div>

                <!-- Order Rows -->
                <?php foreach ($orders as $order): ?>
                    <?php
                        $status = strtolower($order['status'] ?? '');
                        $fulfillmentClass = in_array($status, ['shipped', 'delivered'], true) ? 'fulfillment-shipped' : 'fulfillment-pending';
                        if ($status === 'cancelled') {
                            $fulfillmentClass = 'fulfillment-pending';
                        }
                        $statusLabel = ucfirst($status);
                        $itemsLabel = ((int) ($order['item_count'] ?? 0)) . ' item' . (((int) ($order['item_count'] ?? 0)) === 1 ? '' : 's');
                    ?>
                    <div class="order-table-row">
                        <div class="order-id-cell" data-label="Order">
                            <div class="fw-bold"><?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="text-muted small"><?php echo htmlspecialchars($order['date_formatted']); ?> • <?php echo htmlspecialchars($itemsLabel); ?></div>
                        </div>
                        <div class="customer-cell" data-label="Customer">
                            <div class="customer-name"><?php echo htmlspecialchars($order['full_name']); ?></div>
                            <div class="customer-email"><?php echo htmlspecialchars($order['email']); ?></div>
                        </div>
                        <div class="payment-cell" data-label="Payment">
                            <?php echo htmlspecialchars(strtoupper($order['payment_method'] ?? 'COD')); ?>
                        </div>
                        <div class="fulfillment-cell" data-label="Fulfillment">
                            <span class="fulfillment-dot <?php echo $fulfillmentClass; ?>"></span>
                            <span><?php echo htmlspecialchars($statusLabel); ?></span>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <div class="text-muted small mt-1">Tracking: <?php echo htmlspecialchars($order['tracking_number']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="total-cell" data-label="Total">
                            <?php echo htmlspecialchars($order['total_formatted']); ?>
                        </div>
                        <div data-label="Action" class="d-flex flex-column gap-1">
                            <a href="order-details.php?id=<?php echo urlencode($order['id']); ?>" class="action-link">View</a>
                            <?php if (in_array($status, ['pending', 'confirmed', 'shipped'], true)): ?>
                                <?php if ($status === 'pending'): ?>
                                    <form method="POST" action="order-update.php" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Confirm</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($status === 'confirmed'): ?>
                                    <form method="POST" action="order-update.php" class="d-inline w-100">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                        <input type="hidden" name="status" value="shipped">
                                        <div class="mb-1">
                                            <input type="text" name="tracking_number" class="form-control form-control-sm" placeholder="Tracking number (optional)" value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>">
                                        </div>
                                    <div class="mb-1">
                                            <input type="text" name="courier" class="form-control form-control-sm" placeholder="Courier (optional)" value="<?php echo htmlspecialchars($order['courier'] ?? ''); ?>">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-outline-info w-100">Mark Shipped</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($status === 'shipped'): ?>
                                    <form method="POST" action="order-update.php" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Mark Delivered</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (in_array($status, ['pending', 'confirmed'], true)): ?>
                                    <form method="POST" action="order-update.php" class="d-inline">
                                        <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($toastMessage): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('statusToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
