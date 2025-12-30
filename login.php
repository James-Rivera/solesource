<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <?php include 'includes/head-meta.php'; ?>
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
                    <form>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Email address" aria-label="Email address">
                        </div>
                        <div class="mb-2 position-relative">
                            <input id="passwordInput" type="password" class="form-control pe-5" placeholder="Password" aria-label="Password">
                            <button id="togglePassword" class="toggle-eye-btn" type="button" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="mb-4 text-start">
                            <a href="#" class="forgot-link">forgot password?</a>
                        </div>
                        <a class="btn btn-login-primary w-100" href="index.php">LOG IN</a>
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
