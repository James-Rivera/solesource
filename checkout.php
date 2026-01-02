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
            <div class="sub">(2 item) - P24,000.00</div>
        </div>
    </header>

    <main class="py-5">
        <div class="container-xxl">
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="section-block mb-4">
                        <div class="section-title">Personal Details</div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Email">
                        </div>
                        <div class="mb-3">
                            <input type="text" name="phone" class="form-control" placeholder="Phone">
                        </div>
                        <div class="helper-text">
                            Become a <a href="#">SOLESOURCE Member</a> to get Member benefits. <a href="#">Login</a> or <a href="#">Sign up</a> Now
                        </div>
                    </div>

                    <div class="section-block mb-4">
                        <div class="section-title">Shipping Details</div>
                        <div class="row g-3 mb-1">
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="First Name"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Last Name"></div>
                            <div class="col-12"><input type="text" class="form-control" placeholder="Address Line 1"></div>
                            <div class="col-12"><input type="text" class="form-control" placeholder="Address Line 2"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Province/State"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="City/Municipality"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Postal Code"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Barangay/District"></div>
                            <div class="col-12"><input type="text" class="form-control" placeholder="Country"></div>
                        </div>
                        <div class="form-check mt-4">
                            <input class="form-check-input orange-check" type="checkbox" value="" id="sameInfo" checked>
                            <label class="form-check-label" for="sameInfo" style="margin-top: 8px; margin-left: 7px; font-size: 0.9rem;">Billing address is same as shipping.</label>
                        </div>
                    </div>

                    <div id="billingDetails" class="section-block mb-4 d-none">
                        <div class="section-title">Billing Details</div>
                        <div class="row g-3 mb-1">
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="First Name"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Last Name"></div>
                            <div class="col-12"><input type="text" class="form-control" placeholder="Address Line 1"></div>
                            <div class="col-12"><input type="text" class="form-control" placeholder="Address Line 2"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Province/State"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="City/Municipality"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Postal Code"></div>
                            <div class="col-md-6"><input type="text" class="form-control" placeholder="Barangay/District"></div>
                            <div class="col-12"><input type="text" class="form-control" placeholder="Country"></div>
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
                                    <input type="radio" name="payment" id="pay-cod" checked>
                                    <div class="payment-pill">
                                        <i class="bi bi-credit-card me-2"></i>
                                        <span class="payment-cod">Cash on Delivery</span>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="payment-choice w-100 position-relative">
                                    <input type="radio" name="payment" id="pay-gcash">
                                    <div class="payment-pill">
                                        <img src="assets/img/icons/gcash-seeklogo.svg" alt="GCash" class="payment-icon-img">
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="payment-choice w-100 position-relative">
                                    <input type="radio" name="payment" id="pay-paypal">
                                    <div class="payment-pill">
                                        <img src="assets/img/icons/paypal-seeklogo.svg" alt="PayPal" class="payment-icon-img">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button class="btn place-order-btn w-100" type="button">Place Order</button>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="summary-card">
                        <div class="summary-header">
                            <span class="summary-title">ORDER SUMMARY</span>
                            <a href="#" class="summary-link">edit</a>
                        </div>
                        <div class="summary-row">
                            <span>Subtotal
                                <i class="bi bi-question-circle ms-1 summary-question" data-bs-toggle="tooltip" data-bs-placement="top" title="Items total before delivery and fees."></i>
                            </span>
                            <span>P24,000.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Delivery &amp; Handling</span>
                            <span>Free</span>
                        </div>
                        <hr class="summary-divider">
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span>P24,000.00</span>
                        </div>
                        <div class="est-title mt-3">Estimated Delivery, (DATE TIME)</div>
                        <div class="mini-product">
                            <img src="assets/img/products/new/jordan-11-legend-blue.png" alt="Product" class="mini-thumb">
                            <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                <div class="mini-meta">
                                    <div class="mini-brand">brand</div>
                                    <div class="mini-name">PRODUCT NAME</div>
                                    <div class="mini-attr">Qty 1</div>
                                    <div class="mini-attr">Size US 7.5</div>
                                </div>
                                <div class="mini-price">P24,000.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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