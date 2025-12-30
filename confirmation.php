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
                                <div class="order-id">#SS-2401-9821</div>
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
                                <div class="shipping-label text-uppercase">Shipping to: Juan Dela Cruz</div>
                                <div class="shipping-address">123 MAKATI AVE, MANILA, PHILIPPINES</div>
                            </div>

                            <hr class="section-divider my-4">

                            <div class="mb-4 text-start">
                                <div class="order-summary-title mb-4 text-uppercase">Order Summary</div>
                                <div class="d-flex flex-column flex-md-row align-items-start gap-4">
                                    <div class="flex-shrink-0 text-center" style="width: 220px; max-width: 100%;">
                                        <img src="assets/img/products/new/jordan-11-legend-blue.png" alt="Jordan 11 Retro 'Columbia / Legend Blue' 2024" style="width: 100%; height: auto; object-fit: contain;">
                                    </div>
                                    <div class="flex-grow-1 d-flex flex-column justify-content-between gap-2 h-100">
                                        <div>
                                            <div class="product-name">Jordan 11 Retro 'Columbia / Legend Blue' 2024</div>
                                            <div class="product-meta">Jordan</div>
                                            <div class="product-meta">US MEN SIZE 9.5</div>
                                        </div>
                                        <div class="product-price fw-bold fs-4 text-start">P12,000.00</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="section-divider my-4">

                            <div class="mb-4 text-start">
                                <div class="order-summary-title mb-3 d-flex align-items-center justify-content-between text-uppercase">
                                    <span>Complete Order Details</span>
                                </div>
                                <div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">ORDER NUMBER</div>
                                        <div class="summary-value text-uppercase ms-auto">#SS-2401-9821</div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">ORDER DATE</div>
                                        <div class="summary-value text-uppercase ms-auto">DECEMBER 29, 2025</div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">CUSTOMER</div>
                                        <div class="summary-value text-uppercase ms-auto">JUAN DELA CRUZ</div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">SHIPPING ADDRESS</div>
                                        <div class="summary-value text-uppercase ms-auto">123 MAKATI AVE, MANILA, PHILIPPINES</div>
                                    </div>
                                    <div class="summary-row d-flex align-items-center">
                                        <div class="summary-label">PAYMENT</div>
                                        <div class="summary-value text-uppercase ms-auto">VISA ENDING IN 4242</div>
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
