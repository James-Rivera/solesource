<div id="globalLoader" class="global-loader-backdrop" aria-hidden="true">
    <div class="global-loader-content">
        <div class="global-loader-ring"></div>
        <img src="assets/img/svg/white-logo.svg" alt="Loading" class="global-loader-logo">
    </div>
</div>

<footer class="bg-brand-black text-white py-5">
    <div class="container-xxl">
        <div class="row gy-4">
            
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-start">
                <a href="index.php" class="d-inline-block text-decoration-none p-0 m-0 mb-3">
                    <img src="assets/svg/logo-big-white.svg" alt="SoleSource" height="35" class="d-block" style="margin-left: -5px;"> 
                    </a>
                <p class="text-white small m-0">Verify. Buy. Flex.</p>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="fw-bold mb-3 text-white">Shop</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small opacity-75">
                    <li><a href="#" class="text-reset text-decoration-none">New Arrivals</a></li>
                    <li><a href="#" class="text-reset text-decoration-none">Best Sellers</a></li>
                    <li><a href="#" class="text-reset text-decoration-none">Men</a></li>
                    <li><a href="#" class="text-reset text-decoration-none">Women</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="fw-bold mb-3 text-white">Support</h6>
                <ul class="list-unstyled d-flex flex-column gap-2 small opacity-75">
                    <li><a href="#" class="text-reset text-decoration-none">Help Center</a></li>
                    <li><a href="#" class="text-reset text-decoration-none">Shipping</a></li>
                    <li><a href="#" class="text-reset text-decoration-none">Returns</a></li>
                    <li><a href="#" class="text-reset text-decoration-none">Contact</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h6 class="fw-bold mb-3 text-white">Newsletter</h6>
                <form action="#" class="w-100">
                    <input type="email" class="form-control w-100 border-0 mb-2" placeholder="Enter your email" style="height: 45px; border-radius: 4px;">
                    
                    <button class="btn btn-primary-orange w-100 fw-bold border-0" type="button" style="height: 45px; border-radius: 4px; background-color: #FF5007; color: white;">
                        SUBSCRIBE
                    </button>
                </form>
            </div>
        </div>

        <div class="border-top border-secondary mt-5 pt-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <p class="small opacity-75 m-0">&copy; 2025 SOLESOURCE All rights reserved.</p>
            
            <div class="d-flex gap-2">
                <a href="#" class="bg-white text-black d-flex align-items-center justify-content-center text-decoration-none" style="width: 32px; height: 32px; border-radius: 4px;">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="bg-white text-black d-flex align-items-center justify-content-center text-decoration-none" style="width: 32px; height: 32px; border-radius: 4px;">
                    <i class="bi bi-instagram"></i>
                </a>
                <a href="#" class="bg-white text-black d-flex align-items-center justify-content-center text-decoration-none" style="width: 32px; height: 32px; border-radius: 4px;">
                    <i class="bi bi-twitter-x"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<div class="offcanvas offcanvas-end offcanvas-cart" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="cartDrawerLabel">Your Bag</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="cart-empty text-center text-muted py-4">
                <p class="mb-1">The cart is empty.</p>
                <small>Add items to see them here.</small>
        </div>
        <div class="cart-items d-flex flex-column gap-3 flex-grow-1"></div>

        <div class="mt-4 border-top pt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold text-uppercase small">Subtotal</div>
                <div id="cartSubtotal" class="fw-bold text-brand-black">₱0.00</div>
            </div>
            <a class="btn btn-primary-orange w-100 fw-bold" href="cart.php">View Shopping Bag</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/cart.js"></script>
<script>
(function() {
    const loader = document.getElementById('globalLoader');
    if (!loader) { return; }

    function showLoader() {
        loader.classList.add('active');
    }
    function hideLoader() {
        loader.classList.remove('active');
    }

    // Expose to window for optional manual control
    window.showGlobalLoader = showLoader;
    window.hideGlobalLoader = hideLoader;

    // Show on initial load until full page ready
    showLoader();
    window.addEventListener('load', hideLoader);

    // Optional: show briefly on navigation clicks
    document.addEventListener('click', function (e) {
        const target = e.target.closest('a, button');
        if (!target) return;
        const href = target.getAttribute('href');
        const isLocalNav = href && !href.startsWith('#') && !href.startsWith('javascript:') && !target.hasAttribute('data-bs-toggle');
        if (isLocalNav) {
            showLoader();
        }
    });
})();
</script>