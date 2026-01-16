<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/orders/receipt-email.php';
require_once __DIR__ . '/../includes/vouchers/service.php';

$paypalClientId = getenv('PAYPAL_CLIENT_ID');

$convert_size_label = static function ($row, $desiredSystem = 'US', $fallback = '') {
    if (!$row) { return $fallback; }
    $usLabel = $row['size_label'] ?? $fallback;
    $gender = strtolower($row['gender'] ?? 'men');
    if (strtolower($desiredSystem) !== 'eu') { return $usLabel; }
    $numeric = (float) (preg_replace('/[^0-9.]/', '', $usLabel) ?: 0);
    if ($numeric <= 0) { return $usLabel; }
    $offset = ($gender === 'women') ? 31.5 : 33.0;
    $eu = $numeric + $offset;
    $formatted = floor($eu) == $eu ? number_format($eu, 0) : number_format($eu, 1);
    return 'EU ' . $formatted;
};

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit;
}

$sessionCart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($sessionCart)) {
    header('Location: shop.php');
    exit;
}

// Fetch user data
$userId = (int) $_SESSION['user_id'];
$userStmt = $conn->prepare('SELECT full_name, email FROM users WHERE id = ? LIMIT 1');
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult ? $userResult->fetch_assoc() : [];
$userStmt->close();

// Last used address from most recent order
$lastOrderAddress = null;
$lastOrderStmt = $conn->prepare('SELECT full_name, phone, address, city, province, region, barangay, zip_code, country FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
$lastOrderStmt->bind_param('i', $userId);
$lastOrderStmt->execute();
$lastOrderRes = $lastOrderStmt->get_result();
$lastOrderAddress = $lastOrderRes ? $lastOrderRes->fetch_assoc() : null;
$lastOrderStmt->close();

// Build cart details
$cartItems = [];
$productIds = array_unique(array_map(fn($item) => (int) ($item['id'] ?? 0), $sessionCart));
$productIds = array_filter($productIds, fn($id) => $id > 0);
$productIds = array_values($productIds);
$sizeMap = [];
$subtotal = 0;
$totalItems = 0;

if (!empty($sessionCart)) {
    $sizeIds = array_unique(array_filter(array_map(fn($item) => isset($item['size_id']) ? (int)$item['size_id'] : 0, $sessionCart)));
    if ($sizeIds) {
        $ph = implode(',', array_fill(0, count($sizeIds), '?'));
        $typesSz = str_repeat('i', count($sizeIds));
        $stmtSz = $conn->prepare("SELECT id, product_id, size_label, size_system, gender, stock_quantity FROM product_sizes WHERE id IN ($ph) AND is_active = 1");
        $bindSz = [$typesSz];
        foreach ($sizeIds as $idx => $sid) { $bindSz[] = &$sizeIds[$idx]; }
        call_user_func_array([$stmtSz, 'bind_param'], $bindSz);
        $stmtSz->execute();
        $resSz = $stmtSz->get_result();
        while ($row = $resSz->fetch_assoc()) {
            $sizeMap[(int)$row['id']] = $row;
        }
        $stmtSz->close();
    }
}

if ($productIds) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $bindParams = [$types];
    foreach ($productIds as $idx => $pid) {
        $bindParams[] = &$productIds[$idx];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
    $stmt->execute();
    $res = $stmt->get_result();
    $products = [];
    while ($row = $res->fetch_assoc()) {
        $products[$row['id']] = $row;
    }
    $stmt->close();

    foreach ($sessionCart as $item) {
        $id = (int) ($item['id'] ?? 0);
        if (!$id || !isset($products[$id])) {
            continue;
        }
        $product = $products[$id];
        $qty = max(1, (int) ($item['qty'] ?? 1));
        $size = $item['size'] ?? '';
        $sizeId = isset($item['size_id']) ? (int) $item['size_id'] : null;
        $sizeSystem = $item['size_system'] ?? 'US';
        $price = (float) $product['price'];
        $lineTotal = $price * $qty;
        $subtotal += $lineTotal;
        $totalItems += $qty;

        $sizeRow = $sizeId && isset($sizeMap[$sizeId]) ? $sizeMap[$sizeId] : null;
        $displaySize = $sizeRow ? $convert_size_label($sizeRow, $sizeSystem, $size) : $size;

        $cartItems[] = [
            'id' => $id,
            'name' => $product['name'],
            'brand' => $product['brand'],
            'image' => $product['image'],
            'size' => $displaySize,
            'size_id' => $sizeId,
            'qty' => $qty,
            'price' => $price,
            'line_total' => $lineTotal,
        ];
    }
}

if (empty($cartItems)) {
    header('Location: shop.php');
    exit;
}

$voucherCodeInput = '';
$voucherApplied = null;
$voucherDiscount = 0.0;
$totalAmount = $subtotal;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucherCodeInput = strtoupper(trim($_POST['voucher_code'] ?? ''));
    if ($voucherCodeInput !== '') {
        try {
            $voucherApplied = Vouchers\previewVoucher($conn, $voucherCodeInput);
            $voucherDiscount = Vouchers\computeDiscount($subtotal, $voucherApplied);
        } catch (Vouchers\ClientError $e) {
            $errors[] = 'Voucher error: ' . str_replace('_', ' ', $e->getMessage());
        } catch (Throwable $e) {
            $errors[] = 'Voucher error: unavailable';
        }
    }
    $totalAmount = max(0.0, $subtotal - $voucherDiscount);
    $isApplyOnly = isset($_POST['apply_voucher']);

    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $fullName = trim($_POST['full_name'] ?? ($user['full_name'] ?? ''));
    $address = trim($_POST['address'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postal = trim($_POST['zip_code'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $country = 'Philippines';
    $paymentMethod = trim($_POST['payment'] ?? 'COD');

    if (!$isApplyOnly) {
        if ($paymentMethod === 'PayPal') {
            $errors[] = 'Please complete payment using the PayPal button below. No order was created.';
        }

        $required = [
            'Email' => $email,
            'Phone' => $phone,
            'Full Name' => $fullName,
            'Address (Street/House No.)' => $address,
            'Region' => $region,
            'Province/State' => $province,
            'City/Municipality' => $city,
            'Barangay' => $barangay,
            'Postal Code' => $postal,
            'Country' => $country,
        ];
        foreach ($required as $label => $value) {
            if ($value === '') {
                $errors[] = "$label is required.";
            }
        }
    }

    if (!$errors && !$isApplyOnly) {
        $orderNumber = 'SO-' . date('YmdHis') . '-' . rand(1000, 9999);
        $shippingAddress = implode(', ', array_filter([$address, $barangay, $city, $province, $region, $postal, $country]));

        // Stock validation and reservation
        $stockErrors = [];
        $conn->begin_transaction();

        // Validate stock per item
        foreach ($cartItems as $ci) {
            $pid = (int) $ci['id'];
            $qty = (int) $ci['qty'];
            $sizeId = $ci['size_id'] ?? null;
            if ($sizeId) {
                $stmtCheck = $conn->prepare('SELECT stock_quantity FROM product_sizes WHERE id = ? AND product_id = ? AND is_active = 1 FOR UPDATE');
                $stmtCheck->bind_param('ii', $sizeId, $pid);
                $stmtCheck->execute();
                $res = $stmtCheck->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmtCheck->close();
                if (!$row) {
                    $stockErrors[] = 'Selected size is unavailable for product #' . $pid;
                    continue;
                }
                if ($qty > (int) $row['stock_quantity']) {
                    $stockErrors[] = 'Not enough stock for size ' . htmlspecialchars($ci['size']) . ' of product #' . $pid;
                }
            } else {
                $stmtCheck = $conn->prepare('SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE');
                $stmtCheck->bind_param('i', $pid);
                $stmtCheck->execute();
                $res = $stmtCheck->get_result();
                $row = $res ? $res->fetch_assoc() : null;
                $stmtCheck->close();
                if (!$row || $qty > (int) $row['stock_quantity']) {
                    $stockErrors[] = 'Not enough stock for product #' . $pid;
                }
            }
        }

        if (!empty($stockErrors)) {
            $conn->rollback();
            $errors = array_merge($errors, $stockErrors);
        } else {
            // Create order and items, then decrement stock
            $voucherCodeForSave = $voucherApplied['code'] ?? null;
            $voucherTypeForSave = $voucherApplied['discount_type'] ?? 'percent';
            $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, subtotal_amount, voucher_code, voucher_discount, voucher_discount_type, payment_method, phone, full_name, address, city, province, region, barangay, zip_code, country, shipping_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtOrder->bind_param(
                'isddsds' . str_repeat('s', 11),
                $userId,
                $orderNumber,
                $totalAmount,
                $subtotal,
                $voucherCodeForSave,
                $voucherDiscount,
                $voucherTypeForSave,
                $paymentMethod,
                $phone,
                $fullName,
                $address,
                $city,
                $province,
                $region,
                $barangay,
                $postal,
                $country,
                $shippingAddress
            );
            $stmtOrder->execute();
            $orderId = $stmtOrder->insert_id;
            $stmtOrder->close();

            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_size_id, size, quantity, price_at_purchase) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($cartItems as $ci) {
                $sizeId = $ci['size_id'] ?? null;
                $stmtItem->bind_param('iiisid', $orderId, $ci['id'], $sizeId, $ci['size'], $ci['qty'], $ci['price']);
                $stmtItem->execute();

                if ($sizeId) {
                    $stmtDec = $conn->prepare('UPDATE product_sizes SET stock_quantity = stock_quantity - ? WHERE id = ?');
                    $stmtDec->bind_param('ii', $ci['qty'], $sizeId);
                    $stmtDec->execute();
                    $stmtDec->close();

                    $stmtDecProd = $conn->prepare('UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE id = ?');
                    $stmtDecProd->bind_param('ii', $ci['qty'], $ci['id']);
                    $stmtDecProd->execute();
                    $stmtDecProd->close();
                } else {
                    $stmtDec = $conn->prepare('UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE id = ?');
                    $stmtDec->bind_param('ii', $ci['qty'], $ci['id']);
                    $stmtDec->execute();
                    $stmtDec->close();
                }

                    // Increment total_sold so best-seller ordering reflects actual purchases
                    $stmtIncSold = $conn->prepare('UPDATE products SET total_sold = COALESCE(total_sold,0) + ? WHERE id = ?');
                    if ($stmtIncSold) {
                        $qty = (int)$ci['qty'];
                        $pid = (int)$ci['id'];
                        $stmtIncSold->bind_param('ii', $qty, $pid);
                        $stmtIncSold->execute();
                        $stmtIncSold->close();
                    }
            }
            $stmtItem->close();

            $conn->commit();

            if ($voucherApplied) {
                $voucherRedeemerId = !empty($voucherApplied['student_id']) ? $voucherApplied['student_id'] : (string)$userId;
                try {
                    $redeemResult = Vouchers\markRedeemed($conn, $voucherApplied['code'], $voucherRedeemerId, $orderNumber, $voucherDiscount);
                    if (($voucherApplied['source'] ?? '') === 'api' && !empty($voucherApplied['student_id'])) {
                        Vouchers\notifyCollaborator([
                            'code' => $voucherApplied['code'],
                            'student-id' => $voucherApplied['student_id'],
                            'order-number' => $orderNumber,
                            'redeemed-at' => date(DATE_ATOM),
                            'remaining-uses' => $redeemResult['remaining_uses'] ?? 0,
                            'can-reuse' => $redeemResult['canReuse'] ?? false,
                            'discount-applied' => $voucherDiscount,
                        ]);
                    }
                } catch (Throwable $e) {
                    error_log('Voucher redeem failed for order ' . $orderNumber . ': ' . $e->getMessage());
                }
            }

            $emailData = build_receipt_email([
                'orderId' => $orderId,
                'orderNumber' => $orderNumber,
                'orderDate' => date('F j, Y'),
                'fullName' => $fullName,
                'paymentMethod' => $paymentMethod,
                'shippingAddress' => $shippingAddress,
                'cartItems' => $cartItems,
                'totalAmount' => $totalAmount,
                'subtotalAmount' => $subtotal,
                'voucherCode' => $voucherCodeForSave,
                'voucherDiscount' => $voucherDiscount,
                'voucherType' => $voucherTypeForSave,
            ]);

            try {
                $queueId = queueEmail(
                    $conn,
                    $email,
                    $emailData['subject'],
                    $emailData['html'],
                    $emailData['alt'],
                    $emailData['embedded']
                );
                $_SESSION['email_job_id'] = (int) $queueId;
                $_SESSION['email_notice'] = 'We’re sending your receipt now. If you don’t see it in a few minutes, please check your spam folder.';
            } catch (Throwable $e) {
                $_SESSION['email_notice'] = 'Receipt email could not be queued. We will retry shortly.';
                error_log('Receipt email queue failed for order ' . $orderId . ': ' . $e->getMessage());
            }

            unset($_SESSION['cart']);
            header('Location: confirmation.php?order_id=' . urlencode($orderId));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $title = 'SoleSource | Checkout';
    include __DIR__ . '/../includes/layout/head.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-overrides.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">
</head>

<body class="checkout-page">
    <div id="globalLoader" class="global-loader-backdrop" aria-hidden="true">
        <div class="global-loader-content">
            <div class="global-loader-ring"></div>
            <img src="assets/img/svg/white-logo.svg" alt="Loading" class="global-loader-logo">
        </div>
    </div>
    <header class="checkout-secure-bar">
        <div class="container-xxl d-flex align-items-center justify-content-between">
            <img src="assets/svg/logo-big-white.svg" alt="SoleSource" height="26">
            <div class="d-flex align-items-center gap-2 text-white-50 small">
                <i class="bi bi-lock-fill"></i>
                <span>Secure checkout</span>
            </div>
        </div>
    </header>

    <header class="checkout-hero py-5">
        <div class="container-xxl d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
            <a href="cart.php" class="return-link text-white d-inline-flex align-items-center text-decoration-none">
                <i class="bi bi-chevron-left me-2"></i>
                <span class="return-text">Return to Bag</span>
            </a>
        </div>
        <div class="container-xxl mt-3">
            <h1>Checkout</h1>
            <div class="sub"><?php echo '(' . (int) $totalItems . ' item' . ($totalItems === 1 ? '' : 's') . ') - ₱' . number_format($totalAmount, 2); ?></div>
        </div>
    </header>

    <main class="py-5">
        <div class="container-xxl">
            <?php if ($errors): ?>
                <div class="alert alert-danger"> 
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="d-flex justify-content-center align-items-center mb-5 text-uppercase fw-bold text-small">
                <span class="text-muted">Bag</span>
                <span class="mx-3 text-muted">/</span>
                <span class="text-dark">Checkout</span>
                <span class="mx-3 text-muted">/</span>
                <span class="text-muted">Confirmation</span>
            </div>
            <form method="post">
                <input type="hidden" name="payment_agreed" id="payment_agreed" value="">
                <div class="row g-5">
                    <div class="col-lg-7">
                        <div class="section-block mb-4">
                            <div class="section-title">Personal Details</div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="section-block mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <div class="section-title mb-0">Shipping Details</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-dark btn-sm" id="savedAddressBtn">Select Saved Address</button>
                                    <?php if ($lastOrderAddress): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="importLastAddressBtn">Import Last Used</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <input type="hidden" name="address_id" id="address_id">
                            <input type="hidden" name="region" id="region_text" required>
                            <input type="hidden" name="province" id="province_text" required>
                            <input type="hidden" name="city" id="city_text" required>
                            <input type="hidden" name="barangay" id="barangay_text" required>
                            <div class="row g-3 mb-1">
                                <div class="col-12"><input type="text" name="full_name" class="form-control" placeholder="Full Name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ($user['full_name'] ?? '')); ?>" required></div>
                                <div class="col-12"><input type="text" name="address" class="form-control" placeholder="Address (Street/House No.)" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required></div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Region</label>
                                    <select id="region_select" class="form-select" autocomplete="off" placeholder="Select Region..."></select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Province</label>
                                    <select id="province_select" class="form-select" autocomplete="off" placeholder="Select Province..." disabled></select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City / Municipality</label>
                                    <select id="city_select" class="form-select" autocomplete="off" placeholder="Select City..." disabled></select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Barangay</label>
                                    <select id="barangay_select" class="form-select" autocomplete="off" placeholder="Select Barangay..." disabled></select>
                                </div>
                                <div class="col-md-6"><input type="text" name="zip_code" class="form-control" placeholder="Postal Code" value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>" required></div>
                                <div class="col-md-6"><input type="text" name="country" class="form-control" placeholder="Country" value="Philippines" readonly></div>
                            </div>
                        </div>

                        <div class="section-block mb-4">
                            <div class="section-title">Delivery Options</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card-choice position-relative h-100">
                                        <input type="radio" name="delivery" id="delivery-standard" checked>
                                        <label for="delivery-standard">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="delivery-title">standard delivery</span>
                                                <span class="delivery-price">Free</span>
                                            </div>
                                            <div class="delivery-note">Between 2 – 5 March<br>8:00 – 10:00</div>
                                            <div class="delivery-note mt-2">Shipping company</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card-choice position-relative h-100">
                                        <input type="radio" name="delivery" id="delivery-pickup">
                                        <label for="delivery-pickup">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="delivery-title">pick up</span>
                                                <span class="delivery-price">Free</span>
                                            </div>
                                            <div class="delivery-note">Pay now, collect in our nearest store</div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-block mb-4">
                            <div class="section-title">Voucher</div>
                            <div class="input-group">
                                <input type="text" name="voucher_code" id="voucher_code_input" class="form-control" placeholder="Enter voucher code" value="<?php echo htmlspecialchars($voucherCodeInput); ?>">
                                <button class="btn btn-outline-dark" type="button" id="applyVoucherBtn">Apply</button>
                            </div>
                            <div id="voucher_status" class="small mt-2"></div>
                            <?php if ($voucherApplied): ?>
                                <div class="text-success small mt-2">
                                    Applied <?php echo htmlspecialchars($voucherApplied['code']); ?> &middot;
                                    <?php echo $voucherApplied['discount_type'] === 'percent'
                                        ? htmlspecialchars($voucherApplied['discount_value']) . '% off'
                                        : '₱' . number_format($voucherApplied['discount_value'], 2) . ' off'; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="section-block mb-4">
                            <div class="section-title">Payment</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="payment-choice w-100 position-relative">
                                        <input type="radio" name="payment" id="pay-cod" value="COD" <?php echo (($_POST['payment'] ?? 'COD') === 'COD') ? 'checked' : ''; ?>>
                                        <div class="payment-pill">
                                            <i class="bi bi-credit-card me-2"></i>
                                            <span class="payment-cod">Cash on Delivery</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="payment-choice w-100 position-relative">
                                        <input type="radio" name="payment" id="pay-gcash" value="GCash" <?php echo (($_POST['payment'] ?? '') === 'GCash') ? 'checked' : ''; ?>>
                                        <div class="payment-pill">
                                            <img src="assets/img/icons/gcash-seeklogo.svg" alt="GCash" class="payment-icon-img">
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="payment-choice w-100 position-relative">
                                        <input type="radio" name="payment" id="pay-paypal" value="PayPal" <?php echo (($_POST['payment'] ?? '') === 'PayPal') ? 'checked' : ''; ?>>
                                        <div class="payment-pill">
                                            <img src="assets/img/icons/paypal-seeklogo.svg" alt="PayPal" class="payment-icon-img">
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div id="paypal-button-wrap" class="mt-3 d-none">
                                <div class="small text-muted mb-2">Complete your payment with PayPal.</div>
                                <div id="paypal-button-container"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <button class="btn place-order-btn w-100" type="submit">Place Order</button>
                        </div>
                    </div>

                    <div class="col-lg-5 d-none d-lg-block">
                        <div class="summary-card">
                            <div class="summary-header">
                                <span class="summary-title">ORDER SUMMARY</span>
                                <a href="cart.php" class="summary-link">edit</a>
                            </div>
                            <?php if ($voucherApplied): ?>
                            <div class="summary-voucher mb-3 text-success small">
                                Voucher applied: <?php echo htmlspecialchars($voucherApplied['code']); ?> &middot;
                                <?php echo $voucherApplied['discount_type'] === 'percent'
                                    ? htmlspecialchars($voucherApplied['discount_value']) . '% off'
                                    : '₱' . number_format($voucherApplied['discount_value'], 2) . ' off'; ?>
                            </div>
                            <?php endif; ?>
                            <div class="summary-row">
                                <span>Subtotal
                                    <i class="bi bi-question-circle ms-1 summary-question" data-bs-toggle="tooltip" data-bs-placement="top" title="Items total before delivery and fees."></i>
                                </span>
                                <span>₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <?php if ($voucherDiscount > 0): ?>
                            <div class="summary-row text-success">
                                <span>Voucher Discount</span>
                                <span>-₱<?php echo number_format($voucherDiscount, 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="summary-row">
                                <span>Delivery &amp; Handling</span>
                                <span>Free</span>
                            </div>
                            <hr class="summary-divider">
                            <div class="summary-row summary-total">
                                <span>Total</span>
                                <span>₱<?php echo number_format($totalAmount, 2); ?></span>
                            </div>
                            <div class="est-title mt-3">Estimated Delivery</div>
                            <?php foreach ($cartItems as $ci): ?>
                            <div class="mini-product">
                                <img src="<?php echo htmlspecialchars($ci['image']); ?>" alt="<?php echo htmlspecialchars($ci['name']); ?>" class="mini-thumb">
                                <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                    <div class="mini-meta">
                                        <div class="mini-brand"><?php echo htmlspecialchars($ci['brand']); ?></div>
                                        <div class="mini-name"><?php echo htmlspecialchars($ci['name']); ?></div>
                                        <div class="mini-attr">Qty <?php echo (int) $ci['qty']; ?></div>
                                        <div class="mini-attr">Size <?php echo htmlspecialchars($ci['size']); ?></div>
                                    </div>
                                    <div class="mini-price">₱<?php echo number_format($ci['line_total'], 2); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Mobile Summary Drawer Trigger -->
    <div class="mobile-summary-bar d-lg-none">
        <div class="d-flex flex-column">
            <span class="text-muted small">Total</span>
            <span class="mobile-summary-total">₱<?php echo number_format($totalAmount, 2); ?></span>
        </div>
        <button class="btn mobile-summary-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSummaryDrawer" aria-controls="mobileSummaryDrawer">
            View summary
        </button>
    </div>

    <!-- Mobile Summary Drawer -->
    <div class="offcanvas offcanvas-bottom mobile-summary-offcanvas" tabindex="-1" id="mobileSummaryDrawer" aria-labelledby="mobileSummaryDrawerLabel">
        <div class="mobile-summary-handle"></div>
        <div class="offcanvas-header pt-0 pb-2">
            <div>
                <div class="text-uppercase fw-bold small text-muted">Order Total</div>
                <div class="fs-4 fw-bold text-brand-black">₱<?php echo number_format($totalAmount, 2); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="summary-card">
                <?php if ($voucherDiscount > 0): ?>
                <div class="summary-voucher mb-3 text-success small">
                    Voucher applied: <?php echo htmlspecialchars($voucherApplied['code']); ?> &middot;
                    <?php echo $voucherApplied['discount_type'] === 'percent'
                        ? htmlspecialchars($voucherApplied['discount_value']) . '% off'
                        : '₱' . number_format($voucherApplied['discount_value'], 2) . ' off'; ?>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <?php if ($voucherDiscount > 0): ?>
                <div class="summary-row text-success">
                    <span>Voucher Discount</span>
                    <span>-₱<?php echo number_format($voucherDiscount, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Delivery &amp; Handling</span>
                    <span>Free</span>
                </div>
                <hr class="summary-divider">
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span>₱<?php echo number_format($totalAmount, 2); ?></span>
                </div>
                <div class="est-title mt-3">Items</div>
                <?php foreach ($cartItems as $ci): ?>
                <div class="mini-product">
                    <img src="<?php echo htmlspecialchars($ci['image']); ?>" alt="<?php echo htmlspecialchars($ci['name']); ?>" class="mini-thumb">
                    <div class="flex-grow-1 d-flex flex-column justify-content-between">
                        <div class="mini-meta">
                            <div class="mini-brand"><?php echo htmlspecialchars($ci['brand']); ?></div>
                            <div class="mini-name"><?php echo htmlspecialchars($ci['name']); ?></div>
                            <div class="mini-attr">Qty <?php echo (int) $ci['qty']; ?></div>
                            <div class="mini-attr">Size <?php echo htmlspecialchars($ci['size']); ?></div>
                        </div>
                        <div class="mini-price">₱<?php echo number_format($ci['line_total'], 2); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <footer class="checkout-secure-footer">
        <div class="container-fluid">
            <div class="checkout-footer-inner">
                <div class="footer-left">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Philippines</span>
                    </div>
                    <span class="footer-dot d-none d-sm-inline">•</span>
                    <span>© 2025 SOLESOURCE, Inc. All Rights Reserved.</span>
                    <span class="footer-dot d-none d-sm-inline">•</span>
                    <div class="footer-links">
                        <a href="#">Terms of Use</a>
                        <span class="footer-dot">•</span>
                        <a href="#">Terms of Sale</a>
                        <span class="footer-dot">•</span>
                        <a href="#">Privacy Policy</a>
                    </div>
                </div>
                <div class="footer-payments">
                    <img class="footer-pay-icon" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='52' height='28' viewBox='0 0 52 28'><rect width='52' height='28' rx='4' fill='%23f5f5f5'/><text x='50%' y='55%' text-anchor='middle' font-size='10' fill='%23000' font-family='Arial'>VISA</text></svg>" alt="Visa">
                    <img class="footer-pay-icon" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='52' height='28' viewBox='0 0 52 28'><rect width='52' height='28' rx='4' fill='%23f5f5f5'/><text x='50%' y='55%' text-anchor='middle' font-size='10' fill='%23000' font-family='Arial'>MC</text></svg>" alt="Mastercard">
                    <img class="footer-pay-icon" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='52' height='28' viewBox='0 0 52 28'><rect width='52' height='28' rx='4' fill='%23f5f5f5'/><text x='50%' y='55%' text-anchor='middle' font-size='10' fill='%23000' font-family='Arial'>AMEX</text></svg>" alt="American Express">
                    <img class="footer-pay-icon" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='52' height='28' viewBox='0 0 52 28'><rect width='52' height='28' rx='4' fill='%23f5f5f5'/><text x='50%' y='55%' text-anchor='middle' font-size='10' fill='%23000' font-family='Arial'>PayPal</text></svg>" alt="PayPal">
                    <img class="footer-pay-icon" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='52' height='28' viewBox='0 0 52 28'><rect width='52' height='28' rx='4' fill='%23f5f5f5'/><text x='50%' y='55%' text-anchor='middle' font-size='10' fill='%23000' font-family='Arial'>GCash</text></svg>" alt="GCash">
                    <img class="footer-pay-icon" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='52' height='28' viewBox='0 0 52 28'><rect width='52' height='28' rx='4' fill='%23f5f5f5'/><text x='50%' y='55%' text-anchor='middle' font-size='10' fill='%23000' font-family='Arial'>GrabPay</text></svg>" alt="GrabPay">
                </div>
            </div>
        </div>
    </footer>

    <!-- Saved Address Selector Modal -->
    <div class="modal fade" id="addressSelectorModal" tabindex="-1" aria-labelledby="addressSelectorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-uppercase" id="addressSelectorLabel">Select Saved Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="addressSelectorEmpty" class="text-muted text-center py-4 d-none">No saved addresses yet.</div>
                    <div id="addressSelectorList" class="list-group list-group-flush"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- COD Confirmation Modal -->
    <div class="modal fade" id="codConfirmModal" tabindex="-1" aria-labelledby="codConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="codConfirmLabel">Confirm Cash on Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">You are to pay <strong id="codAmount">₱0.00</strong> upon delivery.</p>
                    <p class="small text-muted">Please ensure someone will be available to receive and pay for the order. By agreeing, you confirm you will pay the full amount in cash to the courier.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="codAgreeBtn">I Agree - Place Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- GCash Modal -->
    <div class="modal fade" id="gcashModal" tabindex="-1" aria-labelledby="gcashLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gcashLabel">Pay with GCash</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="small text-muted">Scan the QR code below using your GCash app to pay the exact amount.</p>
                    <img src="https://via.placeholder.com/300x300.png?text=GCash+QR" alt="GCash QR" id="gcashQr" class="img-fluid mb-3" />
                    <div class="small text-muted">After payment, tap "I have paid" to finish placing your order.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="gcashPaidBtn">I have paid</button>
                </div>
            </div>
        </div>
    </div>

        <?php if (!empty($paypalClientId)): ?>
        <script src="https://www.paypal.com/sdk/js?client-id=<?php echo urlencode($paypalClientId); ?>&currency=PHP&intent=capture"></script>
        <?php endif; ?>
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(el => new bootstrap.Tooltip(el));

                const regionText = document.getElementById('region_text');
                const provinceText = document.getElementById('province_text');
                const cityText = document.getElementById('city_text');
                const barangayText = document.getElementById('barangay_text');

                const savedAddressBtn = document.getElementById('savedAddressBtn');
                const importLastAddressBtn = document.getElementById('importLastAddressBtn');
                const addressSelectorModalEl = document.getElementById('addressSelectorModal');
                const addressSelectorList = document.getElementById('addressSelectorList');
                const addressSelectorEmpty = document.getElementById('addressSelectorEmpty');
                const addressIdInput = document.getElementById('address_id');
                const addressInput = document.querySelector('input[name="address"]');
                const fullNameInput = document.querySelector('input[name="full_name"]');
                const phoneInput = document.querySelector('input[name="phone"]');
                const zipInput = document.querySelector('input[name="zip_code"]');
                const countryInput = document.querySelector('input[name="country"]');
                const savedAddressModal = addressSelectorModalEl ? new bootstrap.Modal(addressSelectorModalEl) : null;
                const lastOrderAddress = <?php echo json_encode($lastOrderAddress); ?>;
                let savedAddresses = [];

                const regionSelectEl = document.getElementById('region_select');
                const provinceSelectEl = document.getElementById('province_select');
                const citySelectEl = document.getElementById('city_select');
                const barangaySelectEl = document.getElementById('barangay_select');

                const tomDefaults = {
                    valueField: 'code',
                    labelField: 'name',
                    searchField: 'name',
                    maxItems: 1,
                    create: false,
                    persist: false,
                    allowEmptyOption: true,
                    placeholder: 'Select...',
                };

                const regionSelect = new TomSelect(regionSelectEl, { ...tomDefaults, placeholder: 'Select Region...' });
                const provinceSelect = new TomSelect(provinceSelectEl, { ...tomDefaults, placeholder: 'Select Province...' });
                const citySelect = new TomSelect(citySelectEl, { ...tomDefaults, placeholder: 'Select City...' });
                const barangaySelect = new TomSelect(barangaySelectEl, { ...tomDefaults, placeholder: 'Select Barangay...' });

                // Debugging removed in production — debug functions are no-ops.
                const debugLog = () => {};
                const ssLogger = () => {};

                const dataSources = {
                    regions: 'https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/region.json',
                    provinces: 'https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/province.json',
                    cities: 'https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/city.json',
                    barangays: 'https://raw.githubusercontent.com/isaacdarcilla/philippine-addresses/main/barangay.json',
                };

                let regionsData = [];
                let provincesData = [];
                let citiesData = [];
                let barangaysData = [];

                const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (ch) => {
                    switch (ch) {
                        case '&': return '&amp;';
                        case '<': return '&lt;';
                        case '>': return '&gt;';
                        case '"': return '&quot;';
                        case "'": return '&#39;';
                        default: return ch;
                    }
                });

                const formatAddress = (addr) => [addr.address_line || addr.address, addr.barangay, addr.city, addr.province, addr.region, addr.zip_code, addr.country]
                    .filter(Boolean)
                    .join(', ');

                const normalizeName = (str) => {
                    if (!str) return '';
                    return String(str)
                        .replace(/\([^)]*\)/g, '')
                        .replace(/[.,\/#!$%\^&\*;:{}=\-_`~\[\]"]+/g, '')
                        .replace(/\s+/g, ' ')
                        .trim()
                        .toLowerCase();
                };

                const findByName = (collection, nameKey, codeKey, value) => {
                    const target = normalizeName(value);
                    if (!target) return '';
                    let match = collection.find((item) => normalizeName(item[nameKey]) === target);
                    if (!match) {
                        match = collection.find((item) => normalizeName(item[nameKey]).includes(target) || normalizeName(item[nameKey]).startsWith(target));
                    }
                    return match ? match[codeKey] : '';
                };

                const fetchJson = async (url) => {
                    const res = await fetch(url);
                    if (!res.ok) throw new Error('Failed to load ' + url);
                    return res.json();
                };

                const resetSelect = (ts, disable = true) => {
                    ts.clear(true);
                    ts.clearOptions();
                    if (disable) {
                        ts.disable();
                    } else {
                        ts.enable();
                    }
                };

                const setHidden = (input, value) => {
                    if (input) {
                        input.value = value || '';
                    }
                };

                const loadRegions = async () => {
                    try {
                        // Fetch region and address datasets in parallel to avoid race conditions later
                        const [r, p, c, b] = await Promise.all([
                            fetchJson(dataSources.regions).catch((e) => { console.error('regions load failed', e); return []; }),
                            fetchJson(dataSources.provinces).catch((e) => { console.error('provinces load failed', e); return []; }),
                            fetchJson(dataSources.cities).catch((e) => { console.error('cities load failed', e); return []; }),
                            fetchJson(dataSources.barangays).catch((e) => { console.error('barangays load failed', e); return []; }),
                        ]);
                        regionsData = r || [];
                        provincesData = p || [];
                        citiesData = c || [];
                        barangaysData = b || [];

                        if (Object.keys(regionSelect.options || {}).length === 0) {
                            regionSelect.addOptions(regionsData.map(r => ({ code: r.region_code, name: r.region_name })));
                        }
                    } catch (e) {
                        console.error(e);
                    }
                };

                const onRegionChange = async (regionCode) => {
                    const selected = regionSelect.options[regionCode];
                    setHidden(regionText, selected ? selected.name : '');
                    setHidden(provinceText, '');
                    setHidden(cityText, '');
                    setHidden(barangayText, '');

                    resetSelect(provinceSelect, true);
                    resetSelect(citySelect, true);
                    resetSelect(barangaySelect, true);

                    if (!regionCode) return;

                    try {
                        if (!provincesData.length) {
                            provincesData = await fetchJson(dataSources.provinces);
                        }
                        const filtered = provincesData.filter(p => p.region_code === regionCode).map(p => ({ code: p.province_code, name: p.province_name }));
                        provinceSelect.addOptions(filtered);
                        provinceSelect.enable();
                    } catch (e) {
                        console.error(e);
                    }
                };

                const onProvinceChange = async (provinceCode) => {
                    const selected = provinceSelect.options[provinceCode];
                    setHidden(provinceText, selected ? selected.name : '');
                    setHidden(cityText, '');
                    setHidden(barangayText, '');

                    resetSelect(citySelect, true);
                    resetSelect(barangaySelect, true);

                    if (!provinceCode) return;

                    try {
                        if (!citiesData.length) {
                            citiesData = await fetchJson(dataSources.cities);
                        }
                        const filtered = citiesData.filter(c => c.province_code === provinceCode).map(c => ({ code: c.city_code, name: c.city_name }));
                        citySelect.addOptions(filtered);
                        citySelect.enable();
                    } catch (e) {
                        console.error(e);
                    }
                };

                const onCityChange = async (cityCode) => {
                    const selected = citySelect.options[cityCode];
                    setHidden(cityText, selected ? selected.name : '');
                    setHidden(barangayText, '');

                    resetSelect(barangaySelect, true);

                    if (!cityCode) return;

                    try {
                        if (!barangaysData.length) {
                            barangaysData = await fetchJson(dataSources.barangays);
                        }
                        const filtered = barangaysData.filter(b => b.city_code === cityCode).map(b => ({ code: b.brgy_code, name: b.brgy_name }));
                        barangaySelect.addOptions(filtered);
                        barangaySelect.enable();
                    } catch (e) {
                        console.error(e);
                    }
                };

                const onBarangayChange = (barangayCode) => {
                    const selected = barangaySelect.options[barangayCode];
                    setHidden(barangayText, selected ? selected.name : '');
                };

                const ensureOption = (ts, value, name) => {
                    if (!ts || !value) return false;
                    const exists = ts.options && ts.options[value];
                    if (!exists) {
                        ts.addOption({ code: value, name: name || value, text: name || value, value });
                    }
                    ts.setValue(value, true);
                    return true;
                };

                const ensureNativeOption = (selectEl, value, name) => {
                    try {
                        if (!selectEl || !value) return;
                        const found = Array.from(selectEl.options).some(o => String(o.value) === String(value));
                        if (!found) {
                            const opt = document.createElement('option');
                            opt.value = value;
                            opt.text = name || value;
                            selectEl.appendChild(opt);
                        }
                        selectEl.value = value;
                    } catch (e) {
                        // ignore
                    }
                };

                // After a successful restore, watch for late resets and reapply if necessary.
                const installPostRestoreWatcher = (addr) => {
                    try {
                        if (!addr) return;
                        const CHECK_MS = 200;
                        const DURATION_MS = 4000;
                        const maxChecks = Math.ceil(DURATION_MS / CHECK_MS);
                        let checks = 0;
                        let reapplies = 0;
                        const expected = {
                            region: addr.region_code || addr.region_text || '',
                            province: addr.province_code || addr.province_text || '',
                            city: addr.city_code || addr.city_text || '',
                            barangay: addr.barangay_code || addr.barangay_text || '',
                        };

                        const checkFn = async () => {
                            checks++;
                            const curRegion = (typeof regionSelect?.getValue === 'function') ? regionSelect.getValue() : (regionText?.value || '');
                            const curProvince = (typeof provinceSelect?.getValue === 'function') ? provinceSelect.getValue() : (provinceText?.value || '');
                            if (!curRegion && expected.region) {
                                reapplies++;
                                debugLog && debugLog('postRestoreWatcher: region missing, reapplying', { attempt: reapplies });
                                await applyAddressToForm(addr);
                            } else if (!curProvince && expected.province) {
                                reapplies++;
                                debugLog && debugLog('postRestoreWatcher: province missing, reapplying', { attempt: reapplies });
                                await applyAddressToForm(addr);
                            }

                            if (checks >= maxChecks) {
                                clearInterval(iv);
                                debugLog && debugLog('postRestoreWatcher: finished', { checks, reapplies });
                                try { sessionStorage.removeItem('checkout_address_restore_v1'); } catch (e) {}
                            }
                        };

                        const iv = setInterval(checkFn, CHECK_MS);
                        // run one immediately
                        checkFn();
                    } catch (e) {
                        // ignore
                    }
                };

                const applyAddressToForm = async (addr) => {
                    if (!addr) return;
                    debugLog && debugLog('applyAddressToForm.start', addr);
                    if (fullNameInput) fullNameInput.value = addr.full_name || '';
                    if (phoneInput) phoneInput.value = addr.phone || '';
                    if (addressInput) addressInput.value = addr.address_line || addr.address || '';
                    if (zipInput) zipInput.value = addr.zip_code || '';
                    if (countryInput) countryInput.value = addr.country || 'Philippines';
                    if (addressIdInput) addressIdInput.value = addr.id || '';

                    await loadRegions();

                    // Prefer explicit codes if provided; otherwise fall back to name matching and injected options.
                    const regionCode = addr.region_code || findByName(regionsData, 'region_name', 'region_code', addr.region);
                    if (regionCode && ensureOption(regionSelect, regionCode, addr.region)) {
                        ensureNativeOption(regionSelectEl, regionCode, addr.region);
                        await onRegionChange(regionCode);
                    } else {
                        resetSelect(provinceSelect, true);
                        resetSelect(citySelect, true);
                        resetSelect(barangaySelect, true);
                    }

                    const provinceCode = addr.province_code || findByName(provincesData, 'province_name', 'province_code', addr.province);
                    if (provinceCode && ensureOption(provinceSelect, provinceCode, addr.province)) {
                        ensureNativeOption(provinceSelectEl, provinceCode, addr.province);
                        await onProvinceChange(provinceCode);
                    }

                    const cityCode = addr.city_code || findByName(citiesData, 'city_name', 'city_code', addr.city);
                    if (cityCode && ensureOption(citySelect, cityCode, addr.city)) {
                        ensureNativeOption(citySelectEl, cityCode, addr.city);
                        await onCityChange(cityCode);
                    }

                    const barangayCode = addr.barangay_code || findByName(barangaysData, 'brgy_name', 'brgy_code', addr.barangay);
                    if (barangayCode) {
                        ensureOption(barangaySelect, barangayCode, addr.barangay);
                        ensureNativeOption(barangaySelectEl, barangayCode, addr.barangay);
                        onBarangayChange(barangayCode);
                    }

                    // Set the hidden text fields after selects are populated so change handlers won't clear them
                    if (regionText) setHidden(regionText, addr.region || '');
                    if (provinceText) setHidden(provinceText, addr.province || '');
                    if (cityText) setHidden(cityText, addr.city || '');
                    if (barangayText) setHidden(barangayText, addr.barangay || '');
                    debugLog && debugLog('applyAddressToForm.done', { region: regionSelect.getValue ? regionSelect.getValue() : null, province: provinceSelect.getValue ? provinceSelect.getValue() : null, city: citySelect.getValue ? citySelect.getValue() : null, barangay: barangaySelect.getValue ? barangaySelect.getValue() : null });
                };

                const renderSavedAddresses = (list) => {
                    if (!addressSelectorList) return;
                    addressSelectorList.innerHTML = '';
                    if (!list.length) {
                        addressSelectorEmpty?.classList.remove('d-none');
                        return;
                    }
                    addressSelectorEmpty?.classList.add('d-none');
                    list.forEach((addr) => {
                        const item = document.createElement('div');
                        item.className = 'list-group-item list-group-item-action py-3';
                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div>
                                    <div class="fw-bold text-uppercase">${escapeHtml(addr.label || 'Address')}</div>
                                    <div class="small text-muted">${escapeHtml(addr.full_name)} • ${escapeHtml(addr.phone)}</div>
                                    <div class="small">${escapeHtml(formatAddress(addr))}</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-dark" data-action="use-address" data-id="${addr.id}">Use this</button>
                            </div>
                        `;
                        addressSelectorList.appendChild(item);
                    });
                };

                const loadSavedAddresses = async () => {
                    try {
                        const res = await fetch('/includes/account/address-list.php');
                        if (res.status === 401) {
                            window.location.href = 'login.php?redirect=checkout';
                            return;
                        }
                        const data = await res.json();
                        if (data?.ok && Array.isArray(data.data)) {
                            savedAddresses = data.data;
                            renderSavedAddresses(savedAddresses);
                        }
                    } catch (err) {
                        console.error('Failed to load saved addresses', err);
                    }
                };

                regionSelect.on('change', onRegionChange);
                provinceSelect.on('change', onProvinceChange);
                citySelect.on('change', onCityChange);
                barangaySelect.on('change', onBarangayChange);

                loadRegions();

                savedAddressBtn?.addEventListener('click', async () => {
                    await loadSavedAddresses();
                    savedAddressModal?.show();
                });

                addressSelectorList?.addEventListener('click', async (evt) => {
                    const btn = evt.target.closest('button[data-action="use-address"]');
                    if (!btn) return;
                    const id = Number(btn.dataset.id || 0);
                    const addr = savedAddresses.find((a) => Number(a.id) === id);
                    if (addr) {
                        await applyAddressToForm(addr);
                        savedAddressModal?.hide();
                    }
                });

                if (importLastAddressBtn && lastOrderAddress) {
                    importLastAddressBtn.addEventListener('click', async () => {
                        await applyAddressToForm({ ...lastOrderAddress, address_line: lastOrderAddress.address });
                        if (addressIdInput) {
                            addressIdInput.value = '';
                        }
                    });
                }

                // Restore address from sessionStorage (retry until TomSelect helpers are ready)
                (function() {
                    const STORAGE_KEY = 'checkout_address_restore_v1';
                    const raw = sessionStorage.getItem(STORAGE_KEY);
                    if (!raw) return;
                    let addr = null;
                    try { addr = JSON.parse(raw); } catch (e) { addr = null; }
                    if (!addr) { sessionStorage.removeItem(STORAGE_KEY); return; }

                    let attempts = 0;
                    const maxAttempts = 30; // ~4.5s
                    const tryApply = async () => {
                        attempts++;
                        try {
                            if (typeof applyAddressToForm === 'function') {
                                await applyAddressToForm(addr);
                            } else {
                                // fallback: fill basic inputs
                                if (fullNameInput) fullNameInput.value = addr.full_name || '';
                                if (phoneInput) phoneInput.value = addr.phone || '';
                                if (addressInput) addressInput.value = addr.address_line || addr.address || '';
                                if (zipInput) zipInput.value = addr.zip_code || '';
                                if (countryInput) countryInput.value = addr.country || 'Philippines';
                            }

                            // Verify region/province set (either via text fields or TomSelect values)
                            const regionOk = (regionText && regionText.value) || (typeof regionSelect?.getValue === 'function' && regionSelect.getValue());
                            const provinceOk = (provinceText && provinceText.value) || (typeof provinceSelect?.getValue === 'function' && provinceSelect.getValue());
                            if (regionOk && provinceOk) {
                                // Start a short-lived watcher to catch any late resets and reapply if needed.
                                try { installPostRestoreWatcher(addr); } catch (e) {}
                                // The watcher will clear sessionStorage after it finishes; return now.
                                return;
                            }
                        } catch (e) {
                            // ignore and retry
                        }
                        if (attempts < maxAttempts) {
                            setTimeout(tryApply, 150);
                        } else {
                            sessionStorage.removeItem(STORAGE_KEY);
                        }
                    };
                    // start trying shortly after page load so initializers have a moment
                    setTimeout(tryApply, 80);
                })();
            });
        </script>
        <script>
            window.ssLogger = window.ssLogger || function(){};
            (function() {
                const form = document.querySelector('form');
                const paymentInputs = Array.from(document.querySelectorAll('input[name="payment"]'));
                const payPalWrap = document.getElementById('paypal-button-wrap');
                const payPalContainer = document.getElementById('paypal-button-container');
                const payPalRadio = document.getElementById('pay-paypal');
                const voucherInput = document.querySelector('input[name="voucher_code"]');
                let buttonsInstance = null;

                const getVoucherCode = () => (voucherInput?.value || '').trim().toUpperCase();

                const parseJsonResponse = async (response, label) => {
                    const raw = await response.text();
                    try {
                        return JSON.parse(raw || '{}');
                    } catch (err) {
                        console.error('PayPal ' + label + ' response was not JSON:', raw);
                        throw err;
                    }
                };

                const showPayPal = () => {
                    if (payPalWrap) {
                        payPalWrap.classList.remove('d-none');
                    }
                    renderButtons();
                };

                const hidePayPal = () => {
                    if (payPalWrap) {
                        payPalWrap.classList.add('d-none');
                    }
                };

                const renderButtons = () => {
                    if (!payPalContainer || typeof paypal === 'undefined' || buttonsInstance) {
                        return;
                    }
                    buttonsInstance = paypal.Buttons({
                        style: { shape: 'rect', layout: 'vertical' },
                        createOrder: async () => {
                            const fd = new FormData();
                            const voucherCode = getVoucherCode();
                            if (voucherCode) {
                                fd.append('voucher_code', voucherCode);
                            }
                            const res = await fetch('/includes/orders/paypal-create.php', { method: 'POST', body: fd });
                            const data = await parseJsonResponse(res, 'create');
                            if (!data?.ok || !data.id) {
                                throw new Error(data?.error || 'Failed to create PayPal order');
                            }
                            return data.id;
                        },
                        onApprove: async (data) => {
                            const fd = new FormData(form);
                            const voucherCode = getVoucherCode();
                            if (voucherCode) {
                                fd.set('voucher_code', voucherCode);
                            }
                            fd.append('order_id', data.orderID);
                            const res = await fetch('/includes/orders/paypal-capture.php', { method: 'POST', body: fd });
                            const json = await parseJsonResponse(res, 'capture');
                            if (!json?.ok) {
                                alert(json?.error || 'PayPal capture failed');
                                if (buttonsInstance && typeof buttonsInstance.restart === 'function') {
                                    buttonsInstance.restart();
                                }
                                return;
                            }
                            window.location = 'confirmation.php?order_id=' + encodeURIComponent(json.order_id);
                        },
                        onError: (err) => {
                            console.error(err);
                            alert('PayPal error. Please try again.');
                        },
                    });
                    buttonsInstance.render(payPalContainer);
                };

                paymentInputs.forEach((input) => {
                    input.addEventListener('change', (evt) => {
                        if (evt.target.value === 'PayPal') {
                            showPayPal();
                        } else {
                            hidePayPal();
                        }
                    });
                });

                if (payPalRadio && payPalRadio.checked) {
                    showPayPal();
                }

                form?.addEventListener('submit', (evt) => {
                    // If the form was submitted by the voucher "Apply" button, allow normal submit
                    const submitter = evt.submitter || (evt.explicitOriginalTarget || null);
                    if (submitter && (submitter.name === 'apply_voucher' || submitter.id === 'applyVoucherBtn')) {
                        return;
                    }

                    const payment = new FormData(form).get('payment');
                    const agreed = document.getElementById('payment_agreed')?.value === '1';
                    if (payment === 'PayPal') {
                        evt.preventDefault();
                        if (!form.checkValidity()) {
                            form.reportValidity();
                            return;
                        }
                        if (typeof paypal === 'undefined') {
                            alert('PayPal SDK failed to load. Please refresh and try again.');
                            return;
                        }
                        showPayPal();
                        payPalContainer?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    // Handle COD and GCash with modals to confirm payment flow
                    if ((payment === 'COD' || payment === 'GCash') && !agreed) {
                        evt.preventDefault();
                        if (!form.checkValidity()) {
                            form.reportValidity();
                            return;
                        }
                        if (payment === 'COD') {
                            const codModalEl = document.getElementById('codConfirmModal');
                            const codAmountEl = document.getElementById('codAmount');
                            if (codAmountEl) codAmountEl.textContent = '₱' + Number(<?php echo json_encode($totalAmount); ?>).toFixed(2);
                            const codModal = new bootstrap.Modal(codModalEl);
                            codModal.show();
                        } else {
                            const gcashModalEl = document.getElementById('gcashModal');
                            const gcashModal = new bootstrap.Modal(gcashModalEl);
                            gcashModal.show();
                        }
                        return;
                    }
                });

                // Intercept Apply to preview voucher via AJAX and avoid full-page submit (prevents TomSelect resets)
                (function() {
                    const applyBtn = document.getElementById('applyVoucherBtn');
                    const voucherInput = document.getElementById('voucher_code_input');
                    const voucherStatus = document.getElementById('voucher_status');
                    if (!applyBtn || !voucherInput) return;

                    // logging disabled

                    const saveAddressSnapshot = () => {
                        try {
                            const STORAGE_KEY = 'checkout_address_restore_v1';
                            const idEl = document.getElementById('address_id');
                            const nameEl = document.querySelector('input[name="full_name"]');
                            const phoneEl = document.querySelector('input[name="phone"]');
                            const addrEl = document.querySelector('input[name="address"]');
                            const zipEl = document.querySelector('input[name="zip_code"]');
                            const countryEl = document.querySelector('input[name="country"]');
                            const regionTextEl = document.getElementById('region_text');
                            const provinceTextEl = document.getElementById('province_text');
                            const cityTextEl = document.getElementById('city_text');
                            const barangayTextEl = document.getElementById('barangay_text');
                            const regionSel = document.getElementById('region_select');
                            const provinceSel = document.getElementById('province_select');
                            const citySel = document.getElementById('city_select');
                            const barangaySel = document.getElementById('barangay_select');

                            const addrObj = {
                                id: idEl?.value || '',
                                full_name: nameEl?.value || '',
                                phone: phoneEl?.value || '',
                                address_line: addrEl?.value || '',
                                zip_code: zipEl?.value || '',
                                country: countryEl?.value || 'Philippines',
                                region_text: regionTextEl?.value || '',
                                province_text: provinceTextEl?.value || '',
                                city_text: cityTextEl?.value || '',
                                barangay_text: barangayTextEl?.value || '',
                                region_code: regionSel?.value || regionTextEl?.value || '',
                                province_code: provinceSel?.value || provinceTextEl?.value || '',
                                city_code: citySel?.value || cityTextEl?.value || '',
                                barangay_code: barangaySel?.value || barangayTextEl?.value || '',
                            };
                            sessionStorage.setItem(STORAGE_KEY, JSON.stringify(addrObj));
                            ssLogger('applyBtn.click: saved addr', addrObj);
                        } catch (err) {
                            console.error('applyBtn save failed', err);
                        }
                    };

                    const updateVoucherUI = (data, code) => {
                        try {
                            if (!voucherStatus) return;
                            if (data?.ok && data?.discount_value) {
                                const txt = (data.discount_type === 'percent')
                                    ? `Applied ${code} · ${data.discount_value}% off` 
                                    : `Applied ${code} · ₱${Number(data.discount_value).toFixed(2)} off`;
                                voucherStatus.textContent = txt;

                                // update summary voucher area
                                const summaryVoucher = document.querySelector('.summary-voucher');
                                if (summaryVoucher) {
                                    summaryVoucher.textContent = `Voucher applied: ${code} · ` + (data.discount_type === 'percent' ? `${data.discount_value}% off` : `₱${Number(data.discount_value).toFixed(2)} off`);
                                } else {
                                    const el = document.createElement('div');
                                    el.className = 'summary-voucher mb-3 text-success small';
                                    el.textContent = `Voucher applied: ${code} · ` + (data.discount_type === 'percent' ? `${data.discount_value}% off` : `₱${Number(data.discount_value).toFixed(2)} off`);
                                    const summaryCard = document.querySelector('.summary-card');
                                    if (summaryCard) summaryCard.insertBefore(el, summaryCard.firstChild.nextSibling);
                                }

                                // update total display if provided
                                if (typeof data.total_amount !== 'undefined') {
                                    const totalEls = document.querySelectorAll('.summary-row.summary-total span');
                                    if (totalEls.length) {
                                        totalEls[totalEls.length - 1].textContent = '₱' + Number(data.total_amount).toFixed(2);
                                    }
                                    const mobileTotal = document.querySelector('.mobile-summary-total');
                                    if (mobileTotal) mobileTotal.textContent = '₱' + Number(data.total_amount).toFixed(2);
                                }
                            } else {
                                voucherStatus.textContent = data?.error || 'Invalid voucher';
                            }
                        } catch (e) { console.error(e); }
                    };

                    applyBtn.addEventListener('click', async (e) => {
                        e.preventDefault();
                        const code = (voucherInput.value || '').trim().toUpperCase();
                        if (!code) return;
                        saveAddressSnapshot();
                        try {
                            const fd = new FormData();
                            fd.append('voucher_code', code);
                            fd.append('preview', '1');
                            const res = await fetch('/includes/vouchers/preview.php', { method: 'POST', body: fd });
                            const json = await res.json();
                            ssLogger('voucher.preview.result', json);
                            updateVoucherUI(json, code);
                        } catch (err) {
                            console.error('Voucher preview failed', err);
                            voucherStatus.textContent = 'Voucher preview failed';
                        }
                    }, { passive: true });
                })();
            })();
        </script>
        <script>
            (function() {
                const loader = document.getElementById('globalLoader');
                if (!loader) { return; }
                const showLoader = () => loader.classList.add('active');
                const hideLoader = () => loader.classList.remove('active');
                window.showGlobalLoader = showLoader;
                window.hideGlobalLoader = hideLoader;
                showLoader();
                window.addEventListener('load', hideLoader);
                document.addEventListener('click', (e) => {
                    const target = e.target.closest('a, button');
                    if (!target) return;
                    const href = target.getAttribute('href');
                    const isLocalNav = href && !href.startsWith('#') && !href.startsWith('javascript:') && !target.hasAttribute('data-bs-toggle');
                    if (isLocalNav) {
                        showLoader();
                    }
                });
            })();
        </script>
        <script>
            (function() {
                const form = document.querySelector('form');
                const paymentAgreedInput = document.getElementById('payment_agreed');
                const codAgreeBtn = document.getElementById('codAgreeBtn');
                const gcashPaidBtn = document.getElementById('gcashPaidBtn');

                if (codAgreeBtn) {
                    codAgreeBtn.addEventListener('click', () => {
                        if (paymentAgreedInput) paymentAgreedInput.value = '1';
                        // close modal then submit
                        const codModalEl = document.getElementById('codConfirmModal');
                        const m = bootstrap.Modal.getInstance(codModalEl);
                        if (m) m.hide();
                        form?.submit();
                    });
                }

                if (gcashPaidBtn) {
                    gcashPaidBtn.addEventListener('click', () => {
                        if (paymentAgreedInput) paymentAgreedInput.value = '1';
                        const gcashModalEl = document.getElementById('gcashModal');
                        const m = bootstrap.Modal.getInstance(gcashModalEl);
                        if (m) m.hide();
                        form?.submit();
                    });
                }
            })();
        </script>
        
</body>

</html>