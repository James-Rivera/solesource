<?php
session_start();
require_once 'includes/connect.php';
require_once 'includes/sms-config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile');
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Initialize SMS schema
ensureSMSSchema($conn);

// Track active tab (default profile; switch to settings on post)
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Fetch user from DB (replaces mock data)
$user = null;
$userStmt = $conn->prepare("SELECT id, full_name, email, password, birthdate, gender, created_at FROM users WHERE id = ? LIMIT 1");
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userRes = $userStmt->get_result();
$userRow = $userRes ? $userRes->fetch_assoc() : null;
$userStmt->close();

if (!$userRow) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Normalize for template usage
$user = [
    'id' => $userRow['id'],
    'name' => $userRow['full_name'],
    'email' => $userRow['email'],
    'password_hash' => $userRow['password'],
    'member_since' => $userRow['created_at'] ? date('F d, Y', strtotime($userRow['created_at'])) : '',
    'birthdate' => $userRow['birthdate'] ?: '',
    'gender' => $userRow['gender'] ?: '',
];

// Flash messages
$personal_success = '';
$personal_error = '';
$security_success = '';
$security_error = '';
$sms_success = '';
$sms_error = '';
$sms_notice = '';

// Initialize variables
$pending_otp = null;
$sms_config = null;

// Handle personal info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_personal') {
    $activeTab = 'settings';
    $fullName = trim($_POST['full_name'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    if ($fullName === '') {
        $personal_error = 'Full name is required.';
    } else {
        $bdVal = $birthdate !== '' ? $birthdate : null;
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, birthdate = ?, gender = ? WHERE id = ?");
        $stmt->bind_param('sssi', $fullName, $bdVal, $gender, $userId);
        if ($stmt->execute()) {
            $personal_success = 'Profile updated successfully.';
            $user['name'] = $fullName;
            $user['birthdate'] = $bdVal ?: '';
            $user['gender'] = $gender;
        } else {
            $personal_error = 'Failed to update profile. Please try again.';
        }
        $stmt->close();
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_password') {
    $activeTab = 'settings';
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current === '' || $new === '' || $confirm === '') {
        $security_error = 'Please fill out all password fields.';
    } elseif ($new !== $confirm) {
        $security_error = 'New password and confirmation do not match.';
    } elseif (strlen($new) < 8) {
        $security_error = 'New password must be at least 8 characters.';
    } elseif (!password_verify($current, $user['password_hash'])) {
        $security_error = 'Current password is incorrect.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $hash, $userId);
        if ($stmt->execute()) {
            $security_success = 'Password updated successfully.';
            $user['password_hash'] = $hash;
        } else {
            $security_error = 'Failed to update password. Please try again.';
        }
        $stmt->close();
    }
}

// Handle SMS OTP Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_sms_otp') {
    $activeTab = 'settings';
    $phone_number = trim($_POST['phone_number'] ?? '');
    $digits = preg_replace('/\D+/', '', $phone_number);

    if ($digits === '') {
        $sms_error = 'Phone number is required.';
    } elseif (strlen($digits) < 10 || strlen($digits) > 15) {
        $sms_error = 'Enter a valid phone number (10-15 digits).';
    } else {
        // Mark old pending OTPs as expired
        $expire_stmt = $conn->prepare("UPDATE otp_verifications SET status = 'expired' WHERE user_id = ? AND status = 'pending'");
        $expire_stmt->bind_param('i', $userId);
        $expire_stmt->execute();
        $expire_stmt->close();

        // Generate OTP
        $otp_code = generateOTP();
        $expires_at = date('Y-m-d H:i:s', time() + (OTP_VALIDITY_MINUTES * 60));

        // Insert OTP record
        $insert_otp = $conn->prepare("INSERT INTO otp_verifications (user_id, phone_number, otp_code, status, expires_at) VALUES (?, ?, ?, 'pending', ?)");
        $insert_otp->bind_param('isss', $userId, $digits, $otp_code, $expires_at);

        if ($insert_otp->execute()) {
            // Insert/Update SMS config
            $upsert_config = $conn->prepare("INSERT INTO sms_config (user_id, phone_number, is_verified) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE phone_number = ?");
            $upsert_config->bind_param('iss', $userId, $digits, $digits);
            $upsert_config->execute();
            $upsert_config->close();

            // Attempt to send OTP via configured SMS gateway
            $message = "Your SoleSource verification code is: $otp_code. Valid for " . OTP_VALIDITY_MINUTES . " minutes.";
            $sent = sendSms($digits, $message);

            // Log SMS attempt
            $log_stmt = $conn->prepare("INSERT INTO sms_logs (user_id, phone_number, message_type, direction, message_body, status) VALUES (?, ?, 'otp', 'outbound', ?, ?)");
            $status = $sent ? 'sent' : 'failed';
            $log_stmt->bind_param('isss', $userId, $digits, $message, $status);
            $log_stmt->execute();
            $log_stmt->close();

            $sms_success = 'OTP sent successfully.';
            $sms_notice = "Code: $otp_code (Dev mode - expires at " . date('h:i A', strtotime($expires_at)) . ")";
            
            // Set pending OTP for immediate form display
            $pending_otp = [
                'otp_code' => $otp_code,
                'expires_at' => $expires_at,
                'attempts' => 0
            ];
        } else {
            $sms_error = 'Failed to generate OTP. Try again. Error: ' . $conn->error;
        }

        $insert_otp->close();
    }
}

// Handle SMS OTP Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'verify_sms_otp') {
    $activeTab = 'settings';
    $otp_input = trim($_POST['otp_code'] ?? '');
    $otp_input = preg_replace('/\D+/', '', $otp_input);

    if ($otp_input === '' || strlen($otp_input) !== OTP_LENGTH) {
        $sms_error = 'Enter a valid ' . OTP_LENGTH . '-digit code.';
    } else {
        // Find pending OTP
        $find_otp = $conn->prepare("SELECT id, otp_code, attempts, expires_at FROM otp_verifications WHERE user_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
        $find_otp->bind_param('i', $userId);
        $find_otp->execute();
        $otp_result = $find_otp->get_result();

        if ($otp_result && $otp_result->num_rows > 0) {
            $otp_record = $otp_result->fetch_assoc();

            // Check expiration
            if (strtotime($otp_record['expires_at']) <= time()) {
                $sms_error = 'OTP has expired. Request a new code.';
                $update_expired = $conn->prepare("UPDATE otp_verifications SET status = 'expired' WHERE id = ?");
                $update_expired->bind_param('i', $otp_record['id']);
                $update_expired->execute();
                $update_expired->close();
            } elseif ($otp_record['attempts'] >= MAX_OTP_ATTEMPTS) {
                $sms_error = 'Too many failed attempts. Request a new code.';
            } elseif ($otp_input === $otp_record['otp_code']) {
                // OTP matches - mark as verified
                $verify_otp = $conn->prepare("UPDATE otp_verifications SET status = 'verified', verified_at = NOW() WHERE id = ?");
                $verify_otp->bind_param('i', $otp_record['id']);
                $verify_otp->execute();
                $verify_otp->close();

                // Get phone number and mark config as verified
                $get_phone = $conn->prepare("SELECT phone_number FROM otp_verifications WHERE id = ?");
                $get_phone->bind_param('i', $otp_record['id']);
                $get_phone->execute();
                $phone_result = $get_phone->get_result();
                if ($phone_result && $phone_result->num_rows > 0) {
                    $phone_row = $phone_result->fetch_assoc();
                    $verify_config = $conn->prepare("UPDATE sms_config SET is_verified = 1, verified_at = NOW() WHERE user_id = ? AND phone_number = ?");
                    $verify_config->bind_param('is', $userId, $phone_row['phone_number']);
                    $verify_config->execute();
                    $verify_config->close();
                }
                $get_phone->close();

                $sms_success = 'Phone number verified successfully!';
            } else {
                // Increment attempts
                $attempts = $otp_record['attempts'] + 1;
                $update_attempts = $conn->prepare("UPDATE otp_verifications SET attempts = ? WHERE id = ?");
                $update_attempts->bind_param('ii', $attempts, $otp_record['id']);
                $update_attempts->execute();
                $update_attempts->close();

                $remaining = MAX_OTP_ATTEMPTS - $attempts;
                $sms_error = "Invalid code. $remaining attempt(s) remaining.";
            }
        } else {
            $sms_error = 'No active OTP request found. Request a new code.';
        }

        $find_otp->close();
    }
}

// Get current SMS config status
$sms_status_stmt = $conn->prepare("SELECT phone_number, is_verified, verified_at FROM sms_config WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$sms_status_stmt->bind_param('i', $userId);
$sms_status_stmt->execute();
$sms_config_result = $sms_status_stmt->get_result();
if ($sms_config_result && $sms_config_result->num_rows > 0) {
    $sms_config = $sms_config_result->fetch_assoc();
}
$sms_status_stmt->close();

// Get pending OTP if exists (only if not already set by POST handler)
if (!$pending_otp) {
    $pending_otp_stmt = $conn->prepare("SELECT otp_code, attempts, expires_at FROM otp_verifications WHERE user_id = ? AND status = 'pending' AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
    $pending_otp_stmt->bind_param('i', $userId);
    $pending_otp_stmt->execute();
    $pending_otp_result = $pending_otp_stmt->get_result();
    if ($pending_otp_result && $pending_otp_result->num_rows > 0) {
        $pending_otp = $pending_otp_result->fetch_assoc();
        $sms_notice = "Active OTP Code (Dev): " . htmlspecialchars($pending_otp['otp_code']) . " (Expires: " . date('h:i A', strtotime($pending_otp['expires_at'])) . ")";
    }
    $pending_otp_stmt->close();
}

$wishlist_products = [];
$wlStmt = $conn->prepare("SELECT p.id AS product_id, p.name, p.brand, p.image, p.price FROM user_wishlist uw JOIN products p ON p.id = uw.product_id WHERE uw.user_id = ? ORDER BY uw.created_at DESC");
$wlStmt->bind_param('i', $userId);
$wlStmt->execute();
$wlRes = $wlStmt->get_result();
while ($row = $wlRes->fetch_assoc()) {
    $row['id'] = (int) $row['product_id'];
    $row['price'] = '₱' . number_format((float)$row['price'], 2, '.', ',');
    $wishlist_products[] = $row;
}
$wlStmt->close();

$purchasedProducts = [];
$purchasesStmt = $conn->prepare("SELECT p.id, p.name, p.brand, p.image, p.price, MAX(o.created_at) AS last_purchased FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN products p ON p.id = oi.product_id WHERE o.user_id = ? GROUP BY p.id, p.name, p.brand, p.image, p.price ORDER BY last_purchased DESC LIMIT 12");
$purchasesStmt->bind_param('i', $userId);
$purchasesStmt->execute();
$purchasesRes = $purchasesStmt->get_result();
while ($row = $purchasesRes->fetch_assoc()) {
    $row['price'] = '₱' . number_format((float) $row['price'], 2, '.', ',');
    $purchasedProducts[] = $row;
}
$purchasesStmt->close();

$ordersResult = null;
$orderCount = 0;
$orderStmt = $conn->prepare("SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at, MIN(p.image) AS product_image, COALESCE(SUM(oi.quantity), 0) AS item_count FROM orders o LEFT JOIN order_items oi ON oi.order_id = o.id LEFT JOIN products p ON p.id = oi.product_id WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC");
$orderStmt->bind_param('i', $userId);
$orderStmt->execute();
$ordersResult = $orderStmt->get_result();
$orderCount = $ordersResult ? $ordersResult->num_rows : 0;
$orderStmt->close();
$hasOrders = $orderCount > 0;

// Saved addresses
$addresses = [];
$addrStmt = $conn->prepare("SELECT id, label, full_name, phone, address_line, city, province, region, barangay, zip_code, country, is_default, created_at FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$addrStmt->bind_param('i', $userId);
$addrStmt->execute();
$addrRes = $addrStmt->get_result();
while ($row = $addrRes->fetch_assoc()) {
    $row['id'] = (int) $row['id'];
    $row['is_default'] = (int) $row['is_default'];
    $addresses[] = $row;
}
$addrStmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | My Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/account.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css">
    <?php include 'includes/head-meta.php'; ?>
    <style>
        /* Force HR visibility */
        hr {
            background-color: #000 !important;
            opacity: 1 !important;
            border: none;
            height: 1px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Account Navigation Tabs -->
    <nav class="account-nav sticky-top">
        <div class="container-xxl">
            <ul class="nav nav-tabs border-0 justify-content-center" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab === 'orders' ? 'active' : ''; ?>" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Orders</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab === 'settings' ? 'active' : ''; ?>" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Settings</button>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Profile Tab -->
        <div class="tab-pane fade <?php echo $activeTab === 'profile' ? 'show active' : ''; ?>" id="profile" role="tabpanel">
            <!-- Hero Banner -->
            <section class="profile-hero">
                <div class="container-xxl position-relative">
                    <div class="profile-hero-content">
                        <h1>HI <?php echo strtoupper($user['name']); ?></h1>
                        <p class="member-since">Member since <?php echo $user['member_since']; ?></p>
                    </div>
                    <div class="profile-hero-icon d-none d-lg-block">
                        <img src="assets/img/svg/white-logo.svg" alt="SoleSource Icon" style="width: 100px; height: 100px;">
                    </div>
                </div>
            </section>

            <!-- Wishlist Section -->
            <?php if (!empty($wishlist_products)): ?>
            <section class="py-5 bg-light">
                <div class="container-xxl">
                    <h2 class="account-section-title">My wishlist</h2>
                    <div class="row row-cols-2 row-cols-md-4 g-4">
                        <?php foreach ($wishlist_products as $product): ?>
                            <div class="col">
                                <div class="wishlist-product-card h-100">
                                    <div class="wishlist-product-image-wrapper position-relative">
                                        <button class="wishlist-remove-btn" data-product-id="<?php echo $product['id']; ?>" aria-label="Remove from wishlist">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <a href="product-details.php?id=<?php echo urlencode($product['id']); ?>" class="d-block ratio ratio-1x1 wishlist-product-media">
                                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="wishlist-product-image">
                                        </a>
                                    </div>
                                    <div class="wishlist-product-body">
                                        <div class="wishlist-product-brand text-muted small fw-bold text-uppercase"><?php echo $product['brand']; ?></div>
                                        <a href="product-details.php?id=<?php echo urlencode($product['id']); ?>" class="wishlist-product-name fw-bold text-dark text-decoration-none d-block mb-1"><?php echo $product['name']; ?></a>
                                        <div class="wishlist-product-price"><?php echo $product['price']; ?></div>
                                        <a class="btn btn-add-to-cart btn-sm" href="product-details.php?id=<?php echo urlencode($product['id']); ?>">
                                            View Product
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Purchases Section -->
            <section class="py-5">
                <div class="container-xxl">
                    <h2 class="account-section-title">My purchases</h2>
                    <div class="row g-4">
                        <?php foreach ($purchasedProducts as $shoe): ?>
                            <?php include 'includes/product-card.php'; ?>
                        <?php endforeach; ?>
                        <?php if (empty($purchasedProducts)): ?>
                            <div class="col-12 text-center text-muted">No purchases yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- Orders Tab -->
        <div class="tab-pane fade <?php echo $activeTab === 'orders' ? 'show active' : ''; ?>" id="orders" role="tabpanel">
            <section class="py-5">
                <div class="container-xxl">
                    <h2 class="account-section-title">ORDERS</h2>
                    <hr class="mb-5">
                    
                    <?php if ($hasOrders && $ordersResult): ?>
                        <!-- Recent Orders List -->
                        <div class="mb-5">
                            <div class="order-history-list">
                                <?php $rowIndex = 0; $lastIndex = $orderCount - 1; ?>
                                <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                    <?php
                                        $rowIndex++;
                                        $isLast = ($rowIndex === $orderCount);
                                        $status = $order['status'] ?? '';
                                        $statusClass = 'text-secondary';
                                        switch ($status) {
                                            case 'pending':
                                                $statusClass = 'text-warning';
                                                break;
                                            case 'confirmed':
                                                $statusClass = 'text-primary';
                                                break;
                                            case 'shipped':
                                                $statusClass = 'text-info';
                                                break;
                                            case 'delivered':
                                                $statusClass = 'text-success';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'text-danger';
                                                break;
                                        }
                                        $itemCount = (int) ($order['item_count'] ?? 0);
                                        $itemsLabel = $itemCount . ' Item' . ($itemCount === 1 ? '' : 's');
                                        $orderDate = $order['created_at'] ? date('M d, Y', strtotime($order['created_at'])) : '';
                                        $orderImage = $order['product_image'] ?? '';
                                        if (!$orderImage) {
                                            $orderImage = 'assets/img/products/new/jordan-11-legend-blue.png';
                                        }
                                    ?>
                                    <div class="order-history-row d-flex align-items-center py-4 <?php echo !$isLast ? 'border-bottom border-light' : ''; ?>">
                                        <!-- Product Image -->
                                        <div class="order-history-image flex-shrink-0 me-4">
                                            <img src="<?php echo htmlspecialchars($orderImage); ?>" alt="Order Item" class="rounded">
                                        </div>
                                        
                                        <!-- Order Details -->
                                        <div class="order-history-details flex-grow-1">
                                            <div class="order-history-number fw-bold text-brand-black mb-1">Order <?php echo htmlspecialchars($order['order_number']); ?></div>
                                            <div class="order-history-meta text-muted small mb-2"><?php echo htmlspecialchars($orderDate); ?> • <?php echo htmlspecialchars($itemsLabel); ?></div>
                                            <div class="order-history-status">
                                                <span class="<?php echo $statusClass; ?> fs-6 me-1">●</span><span class="small"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                                            </div>
                                        </div>
                                        
                                        <!-- Price & Action -->
                                        <div class="order-history-right ms-auto text-end">
                                            <div class="order-history-price fw-bold text-brand-black mb-2">₱<?php echo number_format((float) ($order['total_amount'] ?? 0), 2); ?></div>
                                            <a href="view_order.php?id=<?php echo urlencode($order['id']); ?>" class="order-history-link text-brand-black text-decoration-underline small d-inline-flex align-items-center gap-1">
                                                View Details
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="orders-empty">
                            <h2>NO ORDERS YET</h2>
                            <p>time to start the collection</p>
                            <a href="shop.php" class="btn btn-shop">Shop now</a>
                        </div>
                    <?php endif; ?>
                    
                    <hr class="mt-5">
                    <div class="mt-4">
                        <h5 class="fw-bold text-brand-black">order not appearing?</h5>
                        <a href="#" class="text-decoration-underline text-brand-black">Check the status of your order</a>
                    </div>
                </div>
            </section>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade <?php echo $activeTab === 'settings' ? 'show active' : ''; ?>" id="settings" role="tabpanel">
            <section class="py-5">
                <div class="container-xxl">
                    <div class="row">
                        <!-- Sidebar -->
                        <div class="col-lg-3 settings-sidebar">
                            <div class="settings-sidebar-title">ACCOUNT OVERVIEW</div>
                            <nav class="nav flex-column">
                                <a class="nav-link active" href="#personal-info" data-bs-toggle="pill">Personal Information</a>
                                <a class="nav-link" href="#address-view" data-bs-toggle="pill">Address</a>
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Log out</a>
                            </nav>
                        </div>

                        <!-- Main Content -->
                        <div class="col-lg-9">
                            <div class="tab-content">
                                <!-- Personal Information -->
                                <div class="tab-pane fade show active" id="personal-info">
                                    <h1 class="settings-content-title">MY DETAILS</h1>
                                    <p class="settings-subtitle">Feel free to edit any of your details below so your account is up to date</p>

                                    <?php if ($personal_success): ?>
                                        <div class="alert alert-success py-2 px-3" role="alert"><?php echo htmlspecialchars($personal_success); ?></div>
                                    <?php elseif ($personal_error): ?>
                                        <div class="alert alert-danger py-2 px-3" role="alert"><?php echo htmlspecialchars($personal_error); ?></div>
                                    <?php endif; ?>

                                    <!-- Personal Details -->
                                    <div class="settings-section">
                                        <h3 class="settings-section-title">DETAILS</h3>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">NAME</div>
                                            <div class="settings-detail-value"><?php echo $user['name']; ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">DATE OF BIRTH</div>
                                            <div class="settings-detail-value"><?php echo $user['birthdate'] ? date('F d, Y', strtotime($user['birthdate'])) : '—'; ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">GENDER</div>
                                            <div class="settings-detail-value"><?php echo $user['gender'] ?: '—'; ?></div>
                                        </div>
                                        <a href="#" class="settings-edit-link" data-bs-toggle="modal" data-bs-target="#editPersonalModal">EDIT</a>
                                    </div>

                                    <!-- Account Details -->
                                    <div class="settings-section">
                                        <?php if ($security_success): ?>
                                            <div class="alert alert-success py-2 px-3" role="alert"><?php echo htmlspecialchars($security_success); ?></div>
                                        <?php elseif ($security_error): ?>
                                            <div class="alert alert-danger py-2 px-3" role="alert"><?php echo htmlspecialchars($security_error); ?></div>
                                        <?php endif; ?>
                                        <h3 class="settings-section-title">ACCOUNT DETAILS</h3>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">EMAIL</div>
                                            <div class="settings-detail-value"><?php echo $user['email']; ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">PASSWORD</div>
                                            <div class="settings-detail-value">*****************</div>
                                        </div>
                                        <a href="#" class="settings-edit-link" data-bs-toggle="modal" data-bs-target="#editSecurityModal">EDIT</a>
                                    </div>

                                    <!-- Manage Account -->
                                    <div class="settings-section">
                                        <h3 class="settings-section-title">Manage Account</h3>
                                        <button type="button" class="btn btn-delete-account" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Delete Account</button>
                                    </div>

                                    <!-- SMS Verification -->
                                    <div class="settings-section">
                                        <?php if ($sms_success): ?>
                                            <div class="alert alert-success py-2 px-3" role="alert"><?php echo htmlspecialchars($sms_success); ?></div>
                                        <?php elseif ($sms_error): ?>
                                            <div class="alert alert-danger py-2 px-3" role="alert"><?php echo htmlspecialchars($sms_error); ?></div>
                                        <?php endif; ?>
                                        <?php if ($sms_notice): ?>
                                            <div class="alert alert-info py-2 px-3" role="alert"><?php echo $sms_notice; ?></div>
                                        <?php endif; ?>

                                        <h3 class="settings-section-title">PHONE VERIFICATION</h3>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">PHONE NUMBER</div>
                                            <div class="settings-detail-value"><?php echo $sms_config ? htmlspecialchars($sms_config['phone_number']) : '—'; ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">STATUS</div>
                                            <div class="settings-detail-value">
                                                <?php if ($sms_config && $sms_config['is_verified']): ?>
                                                    <span class="badge bg-success">Verified</span>
                                                    <span class="text-muted small ms-2"><?php echo date('M d, Y h:i A', strtotime($sms_config['verified_at'])); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not Verified</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <form method="POST" class="row g-2 mt-3 align-items-end">
                                            <input type="hidden" name="action" value="request_sms_otp">
                                            <div class="col-md-6 col-lg-5">
                                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Phone Number</label>
                                                <input type="text" name="phone_number" class="form-control" placeholder="09XXXXXXXXX" value="<?php echo $pending_otp || $sms_config ? htmlspecialchars($sms_config['phone_number'] ?? '') : ''; ?>" <?php echo $pending_otp ? 'disabled' : ''; ?>>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <button class="btn btn-dark w-100" type="submit" <?php echo $pending_otp ? 'disabled' : ''; ?>>Request OTP</button>
                                            </div>
                                        </form>

                                        <?php if ($pending_otp): ?>
                                            <form method="POST" class="row g-2 mt-3 align-items-end">
                                                <input type="hidden" name="action" value="verify_sms_otp">
                                                <div class="col-md-6 col-lg-4">
                                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Verification Code</label>
                                                    <input type="text" name="otp_code" class="form-control" placeholder="<?php echo OTP_LENGTH; ?>-digit code" maxlength="<?php echo OTP_LENGTH; ?>" inputmode="numeric">
                                                </div>
                                                <div class="col-md-6 col-lg-3">
                                                    <button class="btn btn-outline-dark w-100" type="submit">Verify Code</button>
                                                </div>
                                                <div class="col-12 small text-muted">Waiting for SMS from Android device...</div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Address View -->
                                <div class="tab-pane fade" id="address-view">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                        <div>
                                            <h1 class="settings-content-title mb-1">MY ADDRESS</h1>
                                            <p class="settings-subtitle mb-0">Please add your address for easier shopping</p>
                                        </div>
                                        <button type="button" class="btn btn-dark text-uppercase fw-bold" id="addAddressBtn" data-bs-toggle="modal" data-bs-target="#addressModal">
                                            <i class="bi bi-plus-circle me-1"></i> Add New Address
                                        </button>
                                    </div>

                                    <div class="mt-4">
                                        <div id="addressEmpty" class="text-center text-muted py-4 border rounded <?php echo !empty($addresses) ? 'd-none' : ''; ?>">No saved addresses yet. Add one to speed up checkout.</div>
                                        <div id="addressCards" class="row g-3">
                                            <?php foreach ($addresses as $addr): ?>
                                                <?php
                                                    $addressParts = array_filter([
                                                        $addr['address_line'] ?? '',
                                                        $addr['barangay'] ?? '',
                                                        $addr['city'] ?? '',
                                                        $addr['province'] ?? '',
                                                        $addr['region'] ?? '',
                                                        $addr['zip_code'] ?? '',
                                                        $addr['country'] ?? '',
                                                    ]);
                                                    $addressText = implode(', ', $addressParts);
                                                    $addrJson = htmlspecialchars(json_encode($addr), ENT_QUOTES, 'UTF-8');
                                                ?>
                                                <div class="col-md-6">
                                                    <div class="border rounded h-100 p-3 position-relative">
                                                        <?php if (!empty($addr['is_default'])): ?>
                                                            <span class="badge bg-dark position-absolute top-0 end-0 m-3">Default</span>
                                                        <?php endif; ?>
                                                        <div class="fw-bold text-uppercase text-dark mb-1"><?php echo htmlspecialchars($addr['label'] ?: 'Address'); ?></div>
                                                        <div class="small text-muted mb-2"><?php echo htmlspecialchars($addr['full_name']); ?></div>
                                                        <div class="small text-brand-black"><?php echo htmlspecialchars($addressText); ?></div>
                                                        <div class="small text-muted mt-2">Phone: <?php echo htmlspecialchars($addr['phone']); ?></div>
                                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit-address" data-address="<?php echo $addrJson; ?>">Edit</button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete-address" data-id="<?php echo (int) $addr['id']; ?>">Delete</button>
                                                            <?php if (empty($addr['is_default'])): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-dark" data-action="make-default" data-id="<?php echo (int) $addr['id']; ?>">Make Default</button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Edit Personal Information Modal -->
    <div class="modal fade" id="editPersonalModal" tabindex="-1" aria-labelledby="editPersonalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="editPersonalModalLabel">Edit Personal Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <form id="personalInfoForm" method="POST">
                        <input type="hidden" name="action" value="update_personal">
                        <div class="mb-4">
                            <label for="fullName" class="form-label text-muted small fw-bold text-uppercase mb-1">Full Name</label>
                            <input type="text" class="form-control form-control-lg" id="fullName" name="full_name" value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                        <div class="mb-4">
                            <label for="birthdate" class="form-label text-muted small fw-bold text-uppercase mb-1">Date of Birth</label>
                            <input type="date" class="form-control form-control-lg" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>">
                        </div>
                        <div class="mb-4">
                            <label for="gender" class="form-label text-muted small fw-bold text-uppercase mb-1">Gender</label>
                            <select class="form-select form-select-lg" id="gender" name="gender">
                                <option value="">Select</option>
                                <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                <option value="Prefer not to say" <?php echo $user['gender'] === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="personalInfoForm" class="btn px-4" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Security Modal -->
    <div class="modal fade" id="editSecurityModal" tabindex="-1" aria-labelledby="editSecurityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="editSecurityModalLabel">Account Security</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <form id="securityForm" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="mb-4">
                            <label for="emailAddress" class="form-label text-muted small fw-bold text-uppercase mb-1">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="emailAddress" value="<?php echo $user['email']; ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                            <small class="text-muted">Email cannot be changed. Contact support if needed.</small>
                        </div>
                        <div class="mb-4">
                            <label for="currentPassword" class="form-label text-muted small fw-bold text-uppercase mb-1">Current Password</label>
                            <input type="password" class="form-control form-control-lg" id="currentPassword" name="current_password" placeholder="Enter current password">
                        </div>
                        <div class="mb-4">
                            <label for="newPassword" class="form-label text-muted small fw-bold text-uppercase mb-1">New Password</label>
                            <input type="password" class="form-control form-control-lg" id="newPassword" name="new_password" placeholder="Enter new password">
                        </div>
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label text-muted small fw-bold text-uppercase mb-1">Confirm New Password</label>
                            <input type="password" class="form-control form-control-lg" id="confirmPassword" name="confirm_password" placeholder="Confirm new password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="securityForm" class="btn px-4" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Update Password</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Address Management Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="addressModalLabel">Add Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <form id="addressForm">
                        <input type="hidden" name="id" id="address_id">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Label (Home, Work)</label>
                                <input type="text" class="form-control" name="label" placeholder="Home">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Full Name</label>
                                <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Phone</label>
                                <input type="text" class="form-control" name="phone" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Address Line</label>
                                <input type="text" class="form-control" name="address_line" placeholder="Street / House / Building" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Region</label>
                                <select id="profile_region_select" class="form-select" name="region" autocomplete="off" placeholder="Select Region..." required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Province / State</label>
                                <select id="profile_province_select" class="form-select" name="province" autocomplete="off" placeholder="Select Province..." disabled required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">City / Municipality</label>
                                <select id="profile_city_select" class="form-select" name="city" autocomplete="off" placeholder="Select City..." disabled required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Barangay</label>
                                <select id="profile_barangay_select" class="form-select" name="barangay" autocomplete="off" placeholder="Select Barangay..." disabled required></select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Postal Code</label>
                                <input type="text" class="form-control" name="zip_code" placeholder="Postal Code" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Country</label>
                                <input type="text" class="form-control" name="country" value="Philippines" required>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="addressDefault" name="is_default" value="1">
                                    <label class="form-check-label" for="addressDefault">Set as default address</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div id="addressError" class="alert alert-danger py-2 px-3 d-none"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addressForm" class="btn px-4" id="addressSubmitBtn" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Save Address</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="deleteAccountModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <p class="mb-0 text-brand-black">Are you sure you want to permanently delete your SoleSource account? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="deleteAccountConfirmBtn" class="btn px-4" style="background: #dc3545; color: #fff; font-weight: 600;">Delete Account</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <p class="mb-0 text-brand-black">Are you sure you want to log out of your SoleSource account?</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <a href="logout.php" class="btn px-4" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Yes, Log Out</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        window.profilePageData = {
            addresses: <?php echo json_encode($addresses); ?>
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html>
