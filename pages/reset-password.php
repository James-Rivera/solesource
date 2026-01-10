<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/auth/password-reset.php';

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}

$error = '';
$success = '';
$tokenParam = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$tokenRecord = $tokenParam ? validate_password_reset_token($conn, $tokenParam) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$tokenRecord) {
        $error = 'Your reset link is invalid or has expired. Request a new email.';
    } elseif ($password === '' || $confirm === '') {
        $error = 'Please enter and confirm your new password.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('ss', $hash, $tokenRecord['email']);
            $updated = $stmt->execute();
            $stmt->close();
        } else {
            $updated = false;
        }

        if ($updated) {
            consume_password_reset_token($conn, $tokenRecord['email']);
            $success = 'Your password has been updated. You can log in with your new credentials.';
            $tokenRecord = null;
        } else {
            $error = 'We could not update your password. Please try again later.';
        }
    }
} else {
    if (!$tokenRecord) {
        $error = 'Your reset link is invalid or has expired.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = 'SoleSource | Reset Password';
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
                    <h1 class="auth-title">Reset password</h1>
                    <p class="text-muted small mb-4">Create a new password below. It must be at least 8 characters long.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success py-2 px-3 small mb-3" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <div class="text-center mb-4">
                            <a href="login.php" class="btn btn-login-primary">Go to login</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($tokenRecord && !$success): ?>
                        <form method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($tokenParam); ?>">
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="New password" aria-label="New password" required>
                            </div>
                            <div class="mb-4">
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" aria-label="Confirm new password" required>
                            </div>
                            <button class="btn btn-login-primary w-100" type="submit">Update password</button>
                        </form>
                    <?php elseif (!$success): ?>
                        <p class="text-muted small mb-0 text-center">
                            Need a new link? <a href="forgot-password.php">Request another password reset email.</a>
                        </p>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="login.php" class="forgot-link">Return to login</a>
                    </div>
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
