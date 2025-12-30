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
    <?php include 'includes/head-meta.php'; ?>
    <style>
        .checkout-hero {
            background: #E35926;
            color: #fff;
            padding: 32px 0 28px;
        }

        .checkout-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .checkout-hero .sub {
            font-size: 0.95rem;
            margin-top: 4px;
            opacity: 0.9;
        }

        .checkout-page {
            background: #f6f6f6;
            color: #111;
            font-size: 1rem;
        }

        .section-title {
            font-size: 1.6rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 18px;
            letter-spacing: 0.3px;
        }

        .form-label {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.2px;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: #1a1a1a;
        }

        .form-control,
        .form-check-input {
            border-radius: 4px;
            border: 1px solid #d6d6d6;
            font-size: 1.05rem;
            padding: 14px 14px;
            min-height: 20px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #111;
        }

        .helper-text {
            font-size: 0.85rem;
            color: #666;
        }

        .helper-text a {
            font-weight: 700;
            text-decoration: underline;
            color: #111;
        }

        .orange-check:checked {
            background-color: #E35926;
            border-color: #E35926;
        }

        .card-choice input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .card-choice label {
            display: block;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            padding: 14px 16px;
            height: 100%;
            cursor: pointer;
            background: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .card-choice input:checked+label {
            border-color: #E35926;
            box-shadow: 0 0 0 2px rgba(227, 89, 38, 0.15);
        }

        .delivery-title {
            font-weight: 700;
            font-size: 0.95rem;
            margin: 0;
            text-transform: lowercase;
        }

        .delivery-price {
            font-weight: 700;
            font-size: 0.95rem;
        }

        .delivery-note {
            font-size: 0.8rem;
            color: #555;
            margin-top: 6px;
            line-height: 1.3;
        }

        .payment-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            padding: 12px;
            height: 64px;
            background: #fff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .payment-choice input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-choice input:checked+.payment-pill {
            border-color: #E35926;
            box-shadow: 0 0 0 2px rgba(227, 89, 38, 0.15);
        }

        .payment-icon-img {
            height: 26px;
            object-fit: contain;
            display: block;
        }

        .payment-cod {
            font-size: 0.95rem;
            font-weight: 600;
            color: #111;
        }

        .place-order-btn {
            background: #E35926;
            border: none;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 14px 16px;
            font-weight: 700;
            border-radius: 4px;
        }

        .place-order-btn:hover {
            background: #cc4e21;
            color: var(--brand-white);
        }

        .summary-card {
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            padding: 16px;
            background: #fff;
            position: sticky;
            top: 24px;
            color: #111;
        }

        .summary-title {
            font-size: 1.40rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            margin-bottom: 14px;
        }

        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            margin-bottom: 15px;
        }

        .summary-link {
            font-size: 0.85rem;
            color: #5f5f5f;
            text-decoration: underline;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .summary-row span:last-child {
            font-weight: 600;
        }

        .summary-total span:first-child,
        .summary-total span:last-child {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .summary-divider {
            border: none;
            border-top: 1px solid #e5e5e5;
            margin: 16px 0 18px;
        }

        .summary-question {
            font-size: 0.95rem;
            color: #6d6d6d;
        }

        .est-title {
            font-size: 1rem;
            color: #6d6d6d;
            margin-bottom: 12px;
        }

        .mini-product {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .mini-thumb {
            width: 140px;
            height: 110px;
            object-fit: contain;
            border: 1px solid #e5e5e5;
            background: #fafafa;
        }

        .mini-meta {
            font-size: 0.9rem;
            line-height: 1.4;
            color: #444;
        }

        .mini-meta .mini-brand {
            text-transform: uppercase;
            font-weight: 700;
            color: #6d6d6d;
        }

        .mini-meta .mini-name {
            text-transform: uppercase;
            font-weight: 800;
            color: #111;
            font-size: 1rem;
        }

        .mini-meta .mini-attr {
            color: #6d6d6d;
            font-size: 0.95rem;
        }

        .mini-price {
            font-size: 0.95rem;
            font-weight: 700;
            text-align: right;
            color: #111;
        }

        .section-block {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            padding: 18px;
        }

        .return-link {
            opacity: 0.85;
        }

        .return-link:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .return-text{
            color: #fff;
            opacity: 0.85;
        }

        .return-text:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .checkout-secure-bar {
            background: #121212;
            color: #fff;
            padding: 20px 0;
        }

        .checkout-secure-footer {
            background: #000;
            color: #f5f5f5;
            padding: 22px 0;
            font-size: 0.95rem;
        }

        .checkout-footer-inner {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        @media (min-width: 768px) {
            .checkout-footer-inner {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        .footer-left {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            color: #f5f5f5;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .footer-links a {
            color: #f5f5f5;
            font-weight: 300;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .footer-dot {
            opacity: 0.6;
        }

        .footer-payments {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        @media (min-width: 768px) {
            .footer-payments {
                justify-content: flex-end;
            }
        }

        .footer-pay-icon {
            height: 22px;
            width: auto;
            display: block;
        }

    </style>
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