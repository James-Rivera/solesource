<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=orders');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = (int) $_GET['id'];
$userId = (int) $_SESSION['user_id'];

// Fetch order with ownership check
$orderStmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1');
$orderStmt->bind_param('ii', $orderId, $userId);
$orderStmt->execute();
$orderRes = $orderStmt->get_result();
$order = $orderRes ? $orderRes->fetch_assoc() : null;
$orderStmt->close();

if (!$order) {
    header('Location: orders.php');
    exit;
}

// Fetch order items with product and size details
$itemStmt = $conn->prepare('SELECT oi.*, p.name, p.brand, p.image, ps.size_label FROM order_items oi JOIN products p ON p.id = oi.product_id LEFT JOIN product_sizes ps ON ps.id = oi.product_size_id WHERE oi.order_id = ?');
$itemStmt->bind_param('i', $orderId);
$itemStmt->execute();
$items = $itemStmt->get_result();
$orderItems = [];
while ($row = $items->fetch_assoc()) {
    $orderItems[] = $row;
}
$itemStmt->close();

$orderDisplayId = $order['order_number'] ?: ('#SS-2026-' . $order['id']);
$status = strtolower($order['status'] ?? 'pending');
$statusLabel = 'PENDING';
if ($status === 'confirmed') {
    $statusLabel = 'CONFIRMED';
} elseif ($status === 'shipped') {
    $statusLabel = 'SHIPPING';
} elseif ($status === 'delivered') {
    $statusLabel = 'DELIVERED';
} elseif ($status === 'cancelled') {
    $statusLabel = 'CANCELLED';
}

$orderDate = $order['created_at'] ? date('F d, Y', strtotime($order['created_at'])) : '';

$shippingAddress = '';
if (!empty($order['shipping_address'])) {
    $shippingAddress = $order['shipping_address'];
} else {
    $addressParts = [];
    if (!empty($order['address'])) {
        $addressParts[] = $order['address'];
    }
    if (!empty($order['barangay'])) {
        $addressParts[] = $order['barangay'];
    }
    $cityProvince = trim(($order['city'] ?? '') . (($order['province'] ?? '') ? ', ' . $order['province'] : ''));
    if ($cityProvince) {
        $addressParts[] = $cityProvince;
    }
    if (!empty($order['region'])) {
        $addressParts[] = $order['region'];
    }
    $zipCountry = trim(($order['zip_code'] ?? '') . (($order['country'] ?? '') ? ' ' . $order['country'] : ''));
    if ($zipCountry) {
        $addressParts[] = $zipCountry;
    }
    $shippingAddress = implode("\n", $addressParts);
}

$totalAmount = (float) ($order['total_amount'] ?? 0);
$subtotalAmount = (float) ($order['subtotal_amount'] ?? $totalAmount);
$voucherCode = trim((string) ($order['voucher_code'] ?? ''));
$voucherDiscount = (float) ($order['voucher_discount'] ?? 0);
$voucherType = $order['voucher_discount_type'] ?? '';
$hasVoucher = $voucherCode !== '' && $voucherDiscount > 0;
$primaryItem = $orderItems[0] ?? null;
$primaryImage = $primaryItem['image'] ?? 'assets/img/products/new/jordan-11-legend-blue.png';
$primaryName = $primaryItem['name'] ?? '';
$primaryBrand = $primaryItem['brand'] ?? '';
$primarySize = ($primaryItem['size_label'] ?? '') ?: ($primaryItem['size'] ?? '');
$primaryPrice = $primaryItem ? (float) $primaryItem['price_at_purchase'] : $totalAmount;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $title = 'SoleSource | Order Details';
    include __DIR__ . '/../includes/layout/head.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/confirmation.css">
</head>

<body class="confirmation-page">
    <?php include __DIR__ . '/../includes/layout/header.php'; ?>

    <main class="py-5 py-md-6">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-8">
                    <div class="confirmation-card ">
                        <div class="confirmation-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 py-4 px-5">
                            <div>
                                <div class="label">ORDER ID</div>
                                <div class="order-id"><?php echo htmlspecialchars($orderDisplayId); ?></div>
                            </div>
                            <div>
                                <span class="status-btn bg-white text-dark"><?php echo htmlspecialchars($statusLabel); ?></span>
                            </div>
                        </div>

                        <div class="confirmation-body mt-3">
                            <div class="mb-4">
                                <div class="order-summary-title mb-3 text-uppercase">Order Summary</div>
                                <?php if (!empty($orderItems)): ?>
                                    <?php foreach ($orderItems as $item): ?>
                                        <div class="d-flex flex-column flex-md-row align-items-start gap-4 mb-4 pb-4 border-bottom">
                                            <div class="flex-shrink-0 text-center" style="width: 250px; max-width: 100%;">
                                                <img src="<?php echo htmlspecialchars($item['image'] ?: 'assets/img/products/new/jordan-11-legend-blue.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="img-fluid">
                                            </div>
                                            <div class="flex-grow-1 d-flex flex-column justify-content-between gap-2 h-100">
                                                <div>
                                                    <div class="product-name fw-bold fs-5 lh-sm"><?php echo htmlspecialchars($item['name']); ?></div>
                                                    <div class="product-meta text-muted text-uppercase small">Brand: <?php echo htmlspecialchars($item['brand']); ?></div>
                                                    <div class="product-meta text-muted text-uppercase small">Size: <?php echo htmlspecialchars(($item['size_label'] ?? '') ?: $item['size']); ?> • Qty: <?php echo (int) $item['quantity']; ?></div>
                                                </div>
                                                <div class="product-price fw-bold fs-4 text-md-end">₱<?php echo number_format((float) $item['price_at_purchase'] * (int) $item['quantity'], 2); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted">No items found for this order.</div>
                                <?php endif; ?>
                            </div>

                            <hr class="section-divider my-4">

                            <?php if ($hasVoucher): ?>
                                <div class="voucher-banner" role="status">
                                    <div class="voucher-chip">Coupon Applied</div>
                                    <div class="voucher-copy">
                                        Voucher <strong><?php echo htmlspecialchars($voucherCode); ?></strong> saved you ₱<?php echo number_format($voucherDiscount, 2); ?> on this order.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="mb-4">
                                <div class="order-summary-title mb-3 text-uppercase">Cost Breakdown</div>
                                <div class="cost-breakdown-card">
                                    <div class="cost-row">
                                        <span>Subtotal</span>
                                        <span>₱<?php echo number_format($subtotalAmount, 2); ?></span>
                                    </div>
                                    <?php if ($voucherDiscount > 0): ?>
                                        <div class="cost-row savings">
                                            <span>Voucher <?php echo $voucherCode ? '(' . htmlspecialchars($voucherCode) . ')' : ''; ?></span>
                                            <span>-₱<?php echo number_format($voucherDiscount, 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="cost-row total">
                                        <span>Total Paid</span>
                                        <span>₱<?php echo number_format($totalAmount, 2); ?></span>
                                    </div>
                                    <p class="cost-note">
                                        Line-item prices show before discounts. Savings are reflected in the totals above.
                                    </p>
                                </div>
                            </div>

                            <hr class="section-divider my-4">

                            <div class="mb-4">
                                <div class="order-summary-title mb-3 text-uppercase">Complete Order Details</div>
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex align-items-start">
                                        <div class="summary-label text-uppercase text-muted">Order Number</div>
                                        <div class="summary-value text-muted ms-auto text-end"><?php echo htmlspecialchars($order['order_number'] ?: $orderDisplayId); ?></div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="summary-label text-uppercase text-muted">Order Date</div>
                                        <div class="summary-value text-muted ms-auto text-end"><?php echo htmlspecialchars($orderDate); ?></div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="summary-label text-uppercase text-muted">Customer</div>
                                        <div class="summary-value text-muted ms-auto text-end"><?php echo htmlspecialchars($order['full_name'] ?? ''); ?></div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="summary-label text-uppercase text-muted">Shipping Address</div>
                                        <div class="summary-value text-muted ms-auto text-end" style="white-space: pre-line;"><?php echo nl2br(htmlspecialchars($shippingAddress)); ?></div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="summary-label text-uppercase text-muted">Payment</div>
                                        <div class="summary-value text-muted ms-auto text-end"><?php echo htmlspecialchars(($order['payment_method'] ?? '') === 'COD' ? 'Cash on Delivery' : ($order['payment_method'] ?? '')); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 d-flex flex-column gap-2">
                                <a href="profile.php?tab=orders" class="btn w-100 cta-btn" style="background: var(--brand-orange, #f5804e); color: #fff; font-weight: 700;">Back to Orders</a>
                                <a href="#" class="btn w-100 cta-btn">Download</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>
</body>

</html>