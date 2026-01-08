<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? '';

    if ($email === '' || $password === '') {
        $error_message = 'Email and password are required.';
    } else {
        $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            if ($redirect === 'checkout') {
                header('Location: checkout.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error_message = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = 'SoleSource | Login';
    include __DIR__ . '/../includes/layout/head.php';
    ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
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
                <div class="login-card">
                    <div class="login-title">LOG IN</div>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger py-2 px-3 small mb-3" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <?php if (isset($_GET['redirect'])): ?>
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Email address" aria-label="Email address" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-2 position-relative">
                            <input id="passwordInput" type="password" name="password" class="form-control pe-5" placeholder="Password" aria-label="Password">
                            <button id="togglePassword" class="toggle-eye-btn" type="button" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="mb-4 text-start">
                            <a href="#" class="forgot-link">forgot password?</a>
                        </div>
                        <button class="btn btn-login-primary w-100" type="submit">LOG IN</button>
                        <div class="small text-muted mb-3" style="line-height: 1.4;">By logging in, you agree to the Terms of Service and Privacy Policy.</div>
                        <div class="mb-2 small text-muted">Not a fellow Sole Member?</div>
                        <a class="btn btn-login-secondary w-100" href="signup.php">CREATE ACCOUNT</a>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <section class="container need-help mt-5">
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
    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');
        toggleBtn?.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggleBtn.innerHTML = isHidden ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
            toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    </script>
</body>
</html>
