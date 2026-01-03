<?php
session_start();
require_once 'includes/connect.php';

if (!isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$orderId = (int) $_GET['order_id'];
$userId = (int) $_SESSION['user_id'];

$stmtOrder = $conn->prepare('SELECT o.*, u.full_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ? LIMIT 1');
$stmtOrder->bind_param('ii', $orderId, $userId);
$stmtOrder->execute();
$orderRes = $stmtOrder->get_result();
$order = $orderRes ? $orderRes->fetch_assoc() : null;
$stmtOrder->close();

if (!$order) {
    header('Location: index.php');
    exit;
}

$stmtItems = $conn->prepare('SELECT oi.*, p.name, p.brand, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
$stmtItems->bind_param('i', $orderId);
$stmtItems->execute();
$itemsRes = $stmtItems->get_result();
$orderItems = [];
while ($row = $itemsRes->fetch_assoc()) {
    $orderItems[] = $row;
}
$stmtItems->close();

$primaryItem = $orderItems[0] ?? null;
$displayOrderId = '#SS-2026-' . $order['id'];
$orderDate = $order['created_at'] ? date('F j, Y', strtotime($order['created_at'])) : '';
$customerName = $order['full_name'] ?? '';
$cityProvince = trim(($order['city'] ?? '') . (($order['province'] ?? '') ? ', ' . $order['province'] : ''));
$postalCountry = trim(($order['zip_code'] ?? '') . (($order['country'] ?? '') ? ' ' . $order['country'] : ''));
$composedAddress = implode("\n", array_filter([
    $order['address'] ?? '',
    $cityProvince,
    $postalCountry,
]));
$shippingAddress = $composedAddress ?: ($order['shipping_address'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/confirmation.css">
    <?php include 'includes/head-meta.php'; ?>
</head>

<body class="confirmation-page">
    <?php include 'includes/header.php'; ?>

    <main class="py-5 py-md-6">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="confirmation-card">
                        <div class="confirmation-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <div class="label">ORDER CONFIRMATION ID</div>
                                <div class="order-id"><?php echo htmlspecialchars($displayOrderId); ?></div>
                            </div>
                            <div>
                                <button class="status-btn" type="button">ORDER STATUS</button>
                            </div>
                        </div>

                        <div class="confirmation-body text-center mt-3">
                            <div class="brand-mark">
                                <img src="assets/img/logo-big.png" alt="SoleSource Logo" class="brand-logo"/>
                            </div>
                            <h1 class="hero-title">IT'S YOURS</h1>
                            <div class="hero-subcontainer">
                                <p class="hero-subtext mb-3">
                                    Your order is confirmed. Use the tracking link above to follow its progress.
                                </p>
                                <p class="hero-subtext">
                                    We have successfully charged your payment method for the cost of your order and will be removing any temporary authorization holds. For invoice details, please visit your Order History on SoleSource.
                                </p>
                            </div>

                            <hr class="section-divider my-4">

                            <div class="text-start mb-4">
                                <div class="shipping-label text-uppercase">Shipping to: <?php echo htmlspecialchars($customerName); ?></div>
                                <div class="shipping-address"><?php echo nl2br(htmlspecialchars($shippingAddress)); ?></div>
                            </div>

                            <hr class="section-divider my-4">

                            <div class="mb-4 text-start">
                                <div class="order-summary-title mb-4 text-uppercase">Order Summary</div>
                                <?php if (!empty($orderItems)): ?>
                                    <?php foreach ($orderItems as $item): ?>
                                        <div class="d-flex flex-column flex-md-row align-items-start gap-4 mb-4 pb-4 border-bottom">
                                            <div class="flex-shrink-0 text-center" style="width: 220px; max-width: 100%;">
                                                <img src="<?php echo htmlspecialchars($item['image'] ?: 'assets/img/products/new/jordan-11-legend-blue.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: auto; object-fit: contain;">
                                            </div>
                                            <div class="flex-grow-1 d-flex flex-column justify-content-between gap-2 h-100">
                                                <div>
                                                    <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                                    <div class="product-meta">Brand: <?php echo htmlspecialchars($item['brand']); ?></div>
                                                    <div class="product-meta">Size: <?php echo htmlspecialchars($item['size']); ?> • Qty: <?php echo (int) $item['quantity']; ?></div>
                                                </div>
                                                <div class="product-price fw-bold fs-4 text-start">₱<?php echo number_format((float) $item['price_at_purchase'] * (int) $item['quantity'], 2); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted">No items found for this order.</div>
                                <?php endif; ?>
                            </div>

                            <hr class="section-divider my-4">

                            <div class="mb-4 text-start">
                                <div class="order-summary-title mb-3 d-flex align-items-center justify-content-between text-uppercase">
                                    <span>Complete Order Details</span>
                                </div>
                                <div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">ORDER NUMBER</div>
                                        <div class="summary-value text-uppercase ms-auto"><?php echo htmlspecialchars($order['order_number'] ?? $displayOrderId); ?></div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">ORDER DATE</div>
                                        <div class="summary-value text-uppercase ms-auto"><?php echo htmlspecialchars($orderDate); ?></div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">CUSTOMER</div>
                                        <div class="summary-value text-uppercase ms-auto"><?php echo htmlspecialchars($customerName); ?></div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">SHIPPING ADDRESS</div>
                                        <div class="summary-value text-uppercase ms-auto"><?php echo nl2br(htmlspecialchars($shippingAddress)); ?></div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">PAYMENT</div>
                                        <div class="summary-value text-uppercase ms-auto"><?php echo htmlspecialchars($order['payment_method'] ?? ''); ?></div>
                                    </div>
                                </div>
                            </div>

                            <hr class="section-divider my-4">

                            <div class="mt-5">
                                <a href="index.php" class="btn w-100 cta-btn">Back to Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>

</html>
