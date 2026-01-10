<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/../includes/auth/password-reset.php';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';
$success = '';
$emailInput = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $emailInput;

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $stmt = $conn->prepare('SELECT full_name FROM users WHERE email = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();
        } else {
            $user = null;
        }

        if ($user) {
            $token = create_password_reset_token($conn, $email);

            if ($token) {
                $envAppUrl = getenv('APP_URL') ?: ($_SERVER['APP_URL'] ?? '');
                $origin = $envAppUrl ? rtrim($envAppUrl, '/') : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
                $resetUrl = rtrim($origin, '/') . '/pages/reset-password.php?token=' . urlencode($token);

                $subject = 'Reset your SoleSource password';
                $htmlBody = '<p style="font-family:Arial,sans-serif;font-size:14px;color:#121212;">'
                    . 'Hello ' . htmlspecialchars($user['full_name'] ?? 'there', ENT_QUOTES, 'UTF-8') . ',</p>'
                    . '<p style="font-family:Arial,sans-serif;font-size:14px;color:#121212;">'
                    . 'Someone requested a password reset for your SoleSource account. Use the link below within 60 minutes.</p>'
                    . '<p style="font-family:Arial,sans-serif;text-align:center;margin:24px 0;">'
                    . '<a href="' . htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:12px 24px;background:#E9713F;color:#fff;text-decoration:none;border-radius:4px;font-weight:600;">Reset Password</a>'
                    . '</p>'
                    . '<p style="font-family:Arial,sans-serif;font-size:13px;color:#6f6f6f;">'
                    . 'If you did not request this, you can ignore this email.</p>';
                $textBody = "Hello {$user['full_name']},\n\nReset your SoleSource password using this link (valid for 60 minutes):\n{$resetUrl}\n\nIf you did not request this, you can ignore this email.";

                queueEmail($conn, $email, $subject, $htmlBody, $textBody);
                $emailInput = '';
            } else {
                $error = 'We could not generate a reset link right now. Please try again later.';
            }
        }

        if ($error === '') {
            $success = 'If the email you entered matches an account, we sent reset instructions.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = 'SoleSource | Forgot Password';
    include __DIR__ . '/../includes/layout/head.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap-overrides.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-page">
    <header class="login-header">
        <div class="container d-flex justify-content-center">
            <a href="index.php" class="d-inline-block">
                <img src="assets/svg/logo-big-white.svg" alt="SoleSource" class="login-logo">
            </a>
        </div>
    </header>

    <main class="login-main">
        <div class="container mb-5">
            <div class="login-wrapper">
                <div class="auth-card">
                    <h1 class="auth-title">Forgot password</h1>
                    <p class="text-muted small mb-4">Enter the email tied to your account and we'll send reset instructions.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success py-2 px-3 small mb-3" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Email address" aria-label="Email address" required value="<?php echo htmlspecialchars($emailInput); ?>">
                        </div>
                        <button class="btn btn-login-primary w-100" type="submit">Send reset link</button>
                        <div class="text-center mt-3">
                            <a href="login.php" class="forgot-link">Back to login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-brand-black py-4">
        <div class="container text-center text-white-50 small">&copy; 2025 SOLESOURCE. All rights reserved.</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
