<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/signup.css">
    <?php include 'includes/head-meta.php'; ?>
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
                <div class="signup-card">
                    <div class="signup-title">Create a SoleSource Account</div>
                    <form>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Full Name" aria-label="Full Name">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Email address" aria-label="Email address">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" placeholder="Password" aria-label="Password">
                        </div>
                        <div class="mb-3">
                            <input type="password" class="form-control" placeholder="Confirm Password" aria-label="Confirm Password">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="newsletterCheck">
                            <label class="form-check-label newsletter-label" for="newsletterCheck">
                                Sign up to receive SOLESOURCE's email newsletter with special promotions, news and more.
                            </label>
                        </div>
                        <div class="signup-legal mb-3">By creating an account, you agree to the Terms of Service and Privacy Policy.</div>
                        <button class="btn btn-signup-primary w-100 mb-3" type="button">Create Account</button>
                        <div class="switch-copy mb-2">Already have an account?</div>
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
