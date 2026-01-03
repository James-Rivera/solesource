<?php
session_start();
require_once '../includes/connect.php';

// Security gate: admins only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($orderId <= 0) {
    header('Location: orders.php?msg=invalid');
    exit;
}

$order = null;
$orderStmt = $conn->prepare("SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = ? LIMIT 1");
$orderStmt->bind_param('i', $orderId);
$orderStmt->execute();
$res = $orderStmt->get_result();
$order = $res ? $res->fetch_assoc() : null;
$orderStmt->close();

if (!$order) {
    header('Location: orders.php?msg=notfound');
    exit;
}

$items = [];
$itemStmt = $conn->prepare("SELECT oi.id, oi.quantity, oi.price_at_purchase, oi.size, ps.size_label, p.name, p.brand, p.image FROM order_items oi JOIN products p ON p.id = oi.product_id LEFT JOIN product_sizes ps ON ps.id = oi.product_size_id WHERE oi.order_id = ?");
$itemStmt->bind_param('i', $orderId);
$itemStmt->execute();
$itemRes = $itemStmt->get_result();
while ($row = $itemRes->fetch_assoc()) {
    $row['line_total'] = (float) $row['price_at_purchase'] * (int) $row['quantity'];
    $row['price_formatted'] = '₱' . number_format((float) $row['price_at_purchase'], 2, '.', ',');
    $row['line_total_formatted'] = '₱' . number_format($row['line_total'], 2, '.', ',');
    $items[] = $row;
}
$itemStmt->close();

$status = strtolower($order['status'] ?? 'pending');
$statusClass = 'text-secondary';
if ($status === 'pending') $statusClass = 'text-warning';
elseif ($status === 'confirmed') $statusClass = 'text-primary';
elseif ($status === 'shipped') $statusClass = 'text-info';
elseif ($status === 'delivered') $statusClass = 'text-success';
elseif ($status === 'cancelled') $statusClass = 'text-danger';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Order Details</title>
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
                    <h1 class="admin-page-title">Order <?php echo htmlspecialchars($order['order_number']); ?></h1>
                    <p class="admin-page-subtitle">Placed <?php echo $order['created_at'] ? date('M d, Y H:i', strtotime($order['created_at'])) : ''; ?></p>
                </div>
                <a class="admin-action-btn" href="orders.php">Back to Orders</a>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="orders-table-container p-3">
                        <h5 class="fw-bold mb-3">Items</h5>
                        <div class="product-table-row product-table-header">
                            <div>Product</div>
                            <div>Qty</div>
                            <div>Price</div>
                            <div>Total</div>
                        </div>
                        <?php foreach ($items as $item): ?>
                            <div class="product-table-row">
                                <div data-label="Product" class="d-flex align-items-center gap-2">
                                    <img src="<?php echo htmlspecialchars('../' . ($item['image'] ?? '')); ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;">
                                    <div>
                                        <div class="fw-bold small text-uppercase"><?php echo htmlspecialchars($item['brand']); ?></div>
                                        <div class="small"><?php echo htmlspecialchars($item['name']); ?></div>
                                    </div>
                                </div>
                                <div data-label="Qty"><?php echo (int) $item['quantity']; ?><?php $sizeDisplay = ($item['size_label'] ?? '') ?: ($item['size'] ?? ''); if ($sizeDisplay !== '') { echo ' • Size ' . htmlspecialchars($sizeDisplay); } ?></div>
                                <div data-label="Price"><?php echo htmlspecialchars($item['price_formatted']); ?></div>
                                <div data-label="Total"><?php echo htmlspecialchars($item['line_total_formatted']); ?></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                            <div class="p-3 text-center text-muted">No items found for this order.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="orders-table-container p-3 mb-3">
                        <h5 class="fw-bold mb-3">Status</h5>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="fs-5 <?php echo $statusClass; ?>">●</span>
                            <span class="fw-bold text-uppercase"><?php echo htmlspecialchars($status); ?></span>
                        </div>
                        <form method="POST" action="order-update.php" class="action-stack">
                            <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                            <div class="mb-2">
                                <label class="form-label small text-uppercase text-muted mb-1">Tracking Number</label>
                                <input type="text" name="tracking_number" class="form-control" value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>" placeholder="e.g. MOCK-123456">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-uppercase text-muted mb-1">Courier</label>
                                <input type="text" name="courier" class="form-control" value="<?php echo htmlspecialchars($order['courier'] ?? ''); ?>" placeholder="MockExpress">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small text-uppercase text-muted mb-1">Set Status</label>
                                <select name="status" class="form-select">
                                    <?php $statuses = ['pending','confirmed','shipped','delivered','cancelled']; ?>
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="admin-action-btn">Update Status</button>
                        </form>
                    </div>
                    <div class="orders-table-container p-3">
                        <h5 class="fw-bold mb-3">Customer & Shipping</h5>
                        <div class="mb-2 fw-bold"><?php echo htmlspecialchars($order['full_name']); ?></div>
                        <div class="mb-3 text-muted small"><?php echo htmlspecialchars($order['email']); ?></div>
                        <div class="small text-brand-black">
                            <?php echo htmlspecialchars($order['full_name']); ?><br>
                            <?php echo htmlspecialchars($order['address'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($order['city'] ?? ''); ?>, <?php echo htmlspecialchars($order['province'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($order['region'] ?? ''); ?> <?php echo htmlspecialchars($order['zip_code'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($order['country'] ?? 'Philippines'); ?>
                        </div>
                        <div class="mt-3 small text-muted">Payment: <?php echo htmlspecialchars(strtoupper($order['payment_method'] ?? 'COD')); ?></div>
                        <div class="mt-1 small text-muted">Total: ₱<?php echo number_format((float) ($order['total_amount'] ?? 0), 2); ?></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
