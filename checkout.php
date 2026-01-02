<?php
session_start();
require_once 'includes/connect.php';

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
$userData = $userResult ? $userResult->fetch_assoc() : [];
$userStmt->close();

// Build cart details
$cartItems = [];
$productIds = array_unique(array_map(fn($item) => (int) ($item['id'] ?? 0), $sessionCart));
$productIds = array_filter($productIds, fn($id) => $id > 0);
$productIds = array_values($productIds);
$subtotal = 0;
$totalItems = 0;

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
        $price = (float) $product['price'];
        $lineTotal = $price * $qty;
        $subtotal += $lineTotal;
        $totalItems += $qty;

        $cartItems[] = [
            'id' => $id,
            'name' => $product['name'],
            'brand' => $product['brand'],
            'image' => $product['image'],
            'size' => $size,
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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $address1 = trim($_POST['address1'] ?? '');
    $address2 = trim($_POST['address2'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $paymentMethod = trim($_POST['payment'] ?? 'COD');

    $required = [
        'Email' => $email,
        'Phone' => $phone,
        'First Name' => $firstName,
        'Last Name' => $lastName,
        'Address Line 1' => $address1,
        'Province/State' => $province,
        'City/Municipality' => $city,
        'Postal Code' => $postal,
        'Country' => $country,
    ];
    foreach ($required as $label => $value) {
        if ($value === '') {
            $errors[] = "$label is required.";
        }
    }

    if (!$errors) {
        $shippingAddress = trim("$address1\n$address2\n$barangay $city, $province $postal\n$country");
        $orderNumber = 'SO-' . date('YmdHis') . '-' . rand(1000, 9999);
        $totalAmount = $subtotal;

        $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_method, shipping_phone, shipping_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmtOrder->bind_param('isdsss', $userId, $orderNumber, $totalAmount, $paymentMethod, $phone, $shippingAddress);
        $stmtOrder->execute();
        $orderId = $stmtOrder->insert_id;
        $stmtOrder->close();

        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, size, quantity, price_at_purchase) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $ci) {
            $stmtItem->bind_param('iisid', $orderId, $ci['id'], $ci['size'], $ci['qty'], $ci['price']);
            $stmtItem->execute();
        }
        $stmtItem->close();

        unset($_SESSION['cart']);
        header('Location: confirmation.php?order_id=' . urlencode($orderId));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <?php include 'includes/head-meta.php'; ?>
</head>

<body class="checkout-page">
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
            <div class="sub"><?php echo '(' . (int) $totalItems . ' item' . ($totalItems === 1 ? '' : 's') . ') - ₱' . number_format($subtotal, 2); ?></div>
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
            <form method="post">
                <div class="row g-5">
                    <div class="col-lg-7">
                        <div class="section-block mb-4">
                            <div class="section-title">Personal Details</div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="helper-text">
                                Become a <a href="#">SOLESOURCE Member</a> to get Member benefits. <a href="login.php">Login</a> or <a href="signup.php">Sign up</a> Now
                            </div>
                        </div>

                        <div class="section-block mb-4">
                            <div class="section-title">Shipping Details</div>
                            <div class="row g-3 mb-1">
                                <div class="col-md-6"><input type="text" name="first_name" class="form-control" placeholder="First Name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ($userData['full_name'] ?? '')); ?>" required></div>
                                <div class="col-md-6"><input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required></div>
                                <div class="col-12"><input type="text" name="address1" class="form-control" placeholder="Address Line 1" value="<?php echo htmlspecialchars($_POST['address1'] ?? ''); ?>" required></div>
                                <div class="col-12"><input type="text" name="address2" class="form-control" placeholder="Address Line 2" value="<?php echo htmlspecialchars($_POST['address2'] ?? ''); ?>"></div>
                                <div class="col-md-6"><input type="text" name="province" class="form-control" placeholder="Province/State" value="<?php echo htmlspecialchars($_POST['province'] ?? ''); ?>" required></div>
                                <div class="col-md-6"><input type="text" name="city" class="form-control" placeholder="City/Municipality" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required></div>
                                <div class="col-md-6"><input type="text" name="postal" class="form-control" placeholder="Postal Code" value="<?php echo htmlspecialchars($_POST['postal'] ?? ''); ?>" required></div>
                                <div class="col-md-6"><input type="text" name="barangay" class="form-control" placeholder="Barangay/District" value="<?php echo htmlspecialchars($_POST['barangay'] ?? ''); ?>"></div>
                                <div class="col-12"><input type="text" name="country" class="form-control" placeholder="Country" value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>" required></div>
                            </div>
                            <div class="form-check mt-4">
                                <input class="form-check-input orange-check" type="checkbox" value="" id="sameInfo" checked>
                                <label class="form-check-label" for="sameInfo" style="margin-top: 8px; margin-left: 7px; font-size: 0.9rem;">Billing address is same as shipping.</label>
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
                        </div>

                        <div class="mb-4">
                            <button class="btn place-order-btn w-100" type="submit">Place Order</button>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="summary-card">
                            <div class="summary-header">
                                <span class="summary-title">ORDER SUMMARY</span>
                                <a href="cart.php" class="summary-link">edit</a>
                            </div>
                            <div class="summary-row">
                                <span>Subtotal
                                    <i class="bi bi-question-circle ms-1 summary-question" data-bs-toggle="tooltip" data-bs-placement="top" title="Items total before delivery and fees."></i>
                                </span>
                                <span>₱<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Delivery &amp; Handling</span>
                                <span>Free</span>
                            </div>
                            <hr class="summary-divider">
                            <div class="summary-row summary-total">
                                <span>Total</span>
                                <span>₱<?php echo number_format($subtotal, 2); ?></span>
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

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sameInfo = document.getElementById('sameInfo');
                const billing = document.getElementById('billingDetails');
                const toggleBilling = () => {
                    if (!billing || !sameInfo) return;
                    billing.classList.toggle('d-none', sameInfo.checked);
                };
                sameInfo?.addEventListener('change', toggleBilling);
                toggleBilling();

                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
            });
        </script>
</body>

</html>