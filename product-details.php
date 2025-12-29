<?php
include 'includes/products.php';

// Locate product by id from query
$product = null;
$requested_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($requested_id) {
    foreach ($all_products as $p) {
        if ((int) ($p['id'] ?? 0) === $requested_id) {
            $product = $p;
            break;
        }
    }
}

if (!$product) {
    header('Location: shop.php');
    exit;
}

// Gallery images (reuse main if no additional)
$gallery_images = [$product['image'], $product['image'], $product['image']];

// Recommended items (shuffle and take 4 excluding current)
$recommended = array_values(array_filter($all_products, function($p) use ($requested_id) {
    return (int)($p['id'] ?? 0) !== $requested_id;
}));
shuffle($recommended);
$recommended = array_slice($recommended, 0, 4);

$breadcrumb_active = $product['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/head-meta.php'; ?>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="py-5">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bg-transparent mb-0">
                    <li class="breadcrumb-item"><a class="text-decoration-none text-brand-black" href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a class="text-decoration-none text-brand-black" href="shop.php">Shop</a></li>
                    <li class="breadcrumb-item"><a class="text-decoration-none text-brand-black" href="shop.php?brand=<?php echo urlencode($product['brand']); ?>"><?php echo htmlspecialchars($product['brand']); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>

            <div class="row g-5">
                <!-- Left: Gallery -->
                <div class="col-lg-7">
                    <div class="product-gallery position-relative mb-3">
                        <div class="ratio ratio-1x1 bg-white d-flex align-items-center justify-content-center border rounded">
                            <img id="productMainImage" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid product-gallery-main">
                        </div>
                        <button class="btn btn-light product-gallery-nav prev" type="button"><i class="bi bi-chevron-left"></i></button>
                        <button class="btn btn-light product-gallery-nav next" type="button"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="d-flex gap-3 product-thumbs">
                        <?php foreach ($gallery_images as $idx => $img): ?>
                            <button class="product-thumb btn btn-outline-light p-1 <?php echo $idx === 0 ? 'active' : ''; ?>" data-img="<?php echo htmlspecialchars($img); ?>">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="Thumb <?php echo $idx + 1; ?>" class="img-fluid rounded">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Right: Details -->
                <div class="col-lg-5">
                    <div class="mb-2 text-uppercase text-muted small fw-semibold"><?php echo htmlspecialchars($product['brand']); ?></div>
                    <h1 class="product-title fw-bold text-uppercase mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-price display-6 fw-bold text-brand-black mb-4"><?php echo htmlspecialchars($product['price']); ?></div>

                    <div class="mb-3 text-uppercase small fw-semibold">Select US Men's Size</div>
                    <?php $sizes = ['7','7.5','8','8.5','9','9.5','10','10.5','11','11.5','12','13']; ?>
                    <div class="size-grid mb-4" id="sizeSelector">
                        <?php foreach ($sizes as $i => $size): ?>
                            <button type="button" class="btn-size<?php echo $i === 0 ? ' active' : ''; ?>" data-size="<?php echo $size; ?>"><?php echo $size; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedSize" name="size" value="<?php echo $sizes[0]; ?>">

                    <button class="btn-cta mb-4" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer">Add to Cart</button>

                    <p class="text-muted mb-4">
                        Originally released in 1996, the 2024 edition of the Air Jordan 11 Retro 'Legend Blue / Columbia' sports a white leather upper, accented with a matching patent leather mudguard, and contrasted with a Columbia Blue embroidered Jumpman hit on the lateral side. It features an ice-blue translucent outsole and full-length Air for comfort.
                    </p>

                    <div class="accordion" id="shippingAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingShip">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShip" aria-expanded="false" aria-controls="collapseShip">
                                    Shipping & Returns
                                </button>
                            </h2>
                            <div id="collapseShip" class="accordion-collapse collapse" aria-labelledby="headingShip" data-bs-parent="#shippingAccordion">
                                <div class="accordion-body text-muted small">
                                    Delivery usually takes 3-5 business days. Returns accepted within 14 days of receipt. Items must be unworn and in original packaging.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommended -->
            <section class="mt-5">
                <h3 class="fw-bold text-brand-black mb-4">Recommended For You</h3>
                <div class="row g-4">
                    <?php foreach ($recommended as $shoe): ?>
                        <?php include 'includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <!-- Cart Drawer Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="cartDrawerLabel">Your Bag</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="rounded" style="width:72px; height:72px; object-fit:contain;">
                <div class="flex-grow-1">
                    <div class="fw-bold text-uppercase small mb-1"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="text-muted small">Size: <span id="cartSizePreview"><?php echo $sizes[0]; ?></span></div>
                </div>
                <div class="fw-bold"><?php echo htmlspecialchars($product['price']); ?></div>
            </div>

            <div class="d-flex align-items-center gap-2 mb-4">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="qtyMinus">-</button>
                <input type="number" id="qtyInput" class="form-control form-control-sm" value="1" min="1" style="width:70px; text-align:center;">
                <button class="btn btn-outline-secondary btn-sm" type="button" id="qtyPlus">+</button>
            </div>

            <div class="mt-auto">
                <div class="d-flex justify-content-between fw-bold mb-3">
                    <span>Subtotal</span>
                    <span id="cartSubtotal"><?php echo htmlspecialchars($product['price']); ?></span>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary-orange">Checkout</button>
                    <a href="shop.php" class="text-decoration-underline text-center text-brand-black">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Thumbnail switching
    const mainImg = document.getElementById('productMainImage');
    const thumbs = document.querySelectorAll('.product-thumb');
    thumbs.forEach(btn => {
        btn.addEventListener('click', () => {
            thumbs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const src = btn.getAttribute('data-img');
            if (src) mainImg.src = src;
        });
    });

    // Size selector
    const sizeBtns = document.querySelectorAll('.btn-size');
    const cartSizePreview = document.getElementById('cartSizePreview');
    const hiddenSize = document.getElementById('selectedSize');
    sizeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            if (cartSizePreview) cartSizePreview.textContent = btn.dataset.size;
            if (hiddenSize) hiddenSize.value = btn.dataset.size;
        });
    });

    // Quantity controls and subtotal update
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const itemPrice = <?php echo (float) filter_var($product['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); ?>;

    function updateSubtotal() {
        const qty = Math.max(1, parseInt(qtyInput.value || '1', 10));
        qtyInput.value = qty;
        const total = itemPrice * qty;
        cartSubtotal.textContent = '₱' + total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    qtyMinus.addEventListener('click', () => { qtyInput.value = Math.max(1, (parseInt(qtyInput.value || '1', 10) - 1)); updateSubtotal(); });
    qtyPlus.addEventListener('click', () => { qtyInput.value = (parseInt(qtyInput.value || '1', 10) + 1); updateSubtotal(); });
    qtyInput.addEventListener('input', updateSubtotal);
    updateSubtotal();

    // Simple carousel nav (cycles thumbs)
    const prevBtn = document.querySelector('.product-gallery-nav.prev');
    const nextBtn = document.querySelector('.product-gallery-nav.next');
    function currentIndex() {
        return Array.from(thumbs).findIndex(b => b.classList.contains('active'));
    }
    function activateIndex(idx) {
        const target = thumbs[idx];
        if (target) target.click();
    }
    prevBtn?.addEventListener('click', () => {
        const idx = currentIndex();
        const next = (idx - 1 + thumbs.length) % thumbs.length;
        activateIndex(next);
    });
    nextBtn?.addEventListener('click', () => {
        const idx = currentIndex();
        const next = (idx + 1) % thumbs.length;
        activateIndex(next);
    });
    </script>
</body>
</html>
