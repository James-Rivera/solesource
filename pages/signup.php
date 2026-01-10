<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';



// SMS sending config
$sms_provider = getenv('SMS_PROVIDER') ?: 'philsms'; // 'philsms' or 'local'
$philsms_token = getenv('PHILSMS_TOKEN') ?: '897|RclyFQhD0mYNyUDRvzc4LcaoN6eGKjxxGrvAXJe6598f040a';
$philsms_sender = getenv('PHILSMS_SENDER') ?: 'SoleSource';
$gateway_url = getenv('SMS_GATEWAY_URL') ?: 'http://192.168.1.5:8080';
$gateway_user = getenv('SMS_GATEWAY_USER') ?: 'sms';
$gateway_pass = getenv('SMS_GATEWAY_PASS') ?: '_GkVArG2';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_raw = trim($_POST['phone'] ?? '');
    // Accept E.164 format only (e.g., +639171234567)
    $phone_digits = preg_replace('/\D+/', '', $phone_raw);
    if (strpos($phone_raw, '+') === 0) {
        $phone_e164 = $phone_raw;
    } elseif (strpos($phone_digits, '09') === 0) {
        $phone_e164 = '+63' . substr($phone_digits, 1);
    } elseif (strpos($phone_digits, '63') === 0) {
        $phone_e164 = '+' . $phone_digits;
    } else {
        $phone_e164 = '';
    }
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $email === '' || $phone_e164 === '' || $password === '' || $confirm_password === '') {
        $error_message = 'All fields are required.';
    } elseif (!preg_match('/^\+63\d{10}$/', $phone_e164)) {
        $error_message = 'Please enter a valid phone number in E.164 format (e.g., +639171234567).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error_message = 'An account with that email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'customer';
            $insert = $conn->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param('sssss', $full_name, $email, $phone_e164, $hashed, $role);
            if ($insert->execute()) {
                // Send welcome SMS (try primary, fallback to secondary)
                $message = "Welcome to SoleSource, $full_name! Your account has been created successfully. Thank you for joining us! Reply BOOST to receive your coupon code.";
                $sent = false;
                $error = '';
                if ($sms_provider === 'philsms') {
                    $philsms_payload = [
                        'recipient' => ltrim($phone_e164, '+'),
                        'sender_id' => $philsms_sender,
                        'type' => 'plain',
                        'message' => $message
                    ];
                    $ch = curl_init('https://dashboard.philsms.com/api/v3/sms/send');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Authorization: Bearer ' . $philsms_token,
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ]);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($philsms_payload));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $resp = curl_exec($ch);
                    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    $sent = $resp && $http < 400 && strpos($resp, 'success') !== false;
                    $error = $resp;
                }
                if (!$sent) {
                    // fallback to local gateway
                    $payload = [
                        'phoneNumbers' => [$phone_e164],
                        'message'      => $message,
                    ];
                    $options = [
                        'http' => [
                            'method'  => 'POST',
                            'header'  => [
                                'Content-Type: application/json',
                                'Authorization: Basic ' . base64_encode("$gateway_user:$gateway_pass"),
                            ],
                            'content' => json_encode($payload),
                            'timeout' => 10,
                        ],
                    ];
                    $context = stream_context_create($options);
                    @file_get_contents(rtrim($gateway_url, '/') . '/messages', false, $context);
                }
                header('Location: login.php');
                exit;
            } else {
                $error_message = 'Registration failed. Please try again.';
            }
            $insert->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = 'SoleSource | Signup';
    include __DIR__ . '/../includes/layout/head.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-overrides.css">
    <link rel="stylesheet" href="assets/css/signup.css">
</head>
<body class="signup-page">
    <header class="signup-header">
        <div class="container d-flex justify-content-center">
            <a href="index.php" class="d-inline-block">
                <img src="assets/svg/logo-big-white.svg" alt="SoleSource" class="signup-logo">
            </a>
        </div>
    </header>

    <main class="signup-main">
        <div class="container">
            <div class="signup-wrapper">
                <div class="auth-card">
                    <h1 class="auth-title">Create a SoleSource Account</h1>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" name="full_name" class="form-control" placeholder="Full Name" aria-label="Full Name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Email address" aria-label="Email address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <input type="tel" name="phone" class="form-control" placeholder="Phone number (e.g., +639171234567)" aria-label="Phone number" pattern="\+63\d{10}" title="Enter number in E.164 format, e.g., +639171234567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            <div class="form-text">Enter your phone number in E.164 format: <b>+639XXXXXXXXX</b></div>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" aria-label="Password">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" aria-label="Confirm Password">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="newsletterCheck">
                            <label class="form-check-label newsletter-label" for="newsletterCheck">
                                Sign up to receive SOLESOURCE's email newsletter with special promotions, news and more.
                            </label>
                        </div>
                        <div class="signup-legal small text-muted mb-3">By creating an account, you agree to the Terms of Service and Privacy Policy.</div>
                        <button class="btn btn-signup-primary w-100 mb-3" type="submit">Create Account</button>
                        <div class="small text-muted mb-2">Already have an account?</div>
                        <a class="btn btn-signup-secondary w-100" href="login.php">Log In</a>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <section class="container need-help mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="help-title">Need Help/Questions?</div>
                <div class="help-copy">Have any questions or comments? Reach out to us through our contact options.</div>
            </div>
            <a href="#" class="help-link">Contact us</a>
        </div>
    </section>

    <footer class="bg-brand-black py-4">
        <div class="container text-center text-white-50 small">&copy; 2025 SOLESOURCE. All rights reserved.</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
