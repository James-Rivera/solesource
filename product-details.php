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
                        <div class="product-gallery-main bg-white">
                            <img id="productMainImage" src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-gallery-img">
                        </div>
                        <button class="product-gallery-nav prev" type="button"><i class="bi bi-chevron-left"></i></button>
                        <button class="product-gallery-nav next" type="button"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="product-thumbs d-flex gap-3 justify-content-center">
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
                    <h1 class="product-title-detail mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-price-detail mb-5"><?php echo htmlspecialchars($product['price']); ?></div>

                    <div class="size-label mb-3 text-uppercase small fw-bold">SELECT US MEN'S SIZE</div>
                    <?php $sizes = ['7','7.5','8','8.5','9','9.5','10','10.5','11','11.5','12','13']; ?>
                    <div class="size-grid mb-4" id="sizeSelector">
                        <?php foreach ($sizes as $i => $size): ?>
                            <button type="button" class="btn-size<?php echo $i === 0 ? ' active' : ''; ?>" data-size="<?php echo $size; ?>"><?php echo $size; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedSize" name="size" value="<?php echo $sizes[0]; ?>">

                    <button
                        class="btn-cta mb-5"
                        id="addToCartBtn"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#cartDrawer"
                        data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                        data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                        data-product-brand="<?php echo htmlspecialchars($product['brand']); ?>"
                        data-product-price="<?php echo htmlspecialchars($product['price']); ?>"
                        data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                    >Add to Cart</button>

                    <div class="mb-3 size-label text-uppercase fw-bold small">About This Product</div>
                    <p class="text-description lh-lg mb-3">
                        Originally released in 1996, the 2024 edition of the Air Jordan 11 Retro 'Legend Blue / Columbia' sports a white leather upper, accented with a matching patent leather mudguard, and contrasted with a Columbia Blue embroidered Jumpman hit on the lateral side. It features an ice-blue translucent outsole and full-length Air for comfort.
                    </p>
                    <div class="text-muted small lh-lg">
                        <div>SKU: CT8012-116</div>
                        <div>Colorway: White/Legend Blue/Black</div>
                        <div>Release Date: 12/13/24</div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Returns Full Width -->
            <div class="mt-5 pt-4 border-top">
                <div class="accordion accordion-flush shipping-accordion" id="shippingAccordionFull">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingShippingFull">
                            <button class="accordion-button collapsed px-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShippingFull" aria-expanded="false" aria-controls="collapseShippingFull">
                                <span class="accordion-text text-uppercase">Shipping & Returns</span>
                            </button>
                        </h2>
                        <div id="collapseShippingFull" class="accordion-collapse collapse" aria-labelledby="headingShippingFull" data-bs-parent="#shippingAccordionFull">
                            <div class="accordion-body px-0">
                                <div class="row g-4">
                                    <div class="col-lg-4">
                                        <div class="fw-bold text-uppercase small mb-2">Cancellations</div>
                                        <p class="text-muted small lh-lg mb-0">For sneakers, you may cancel your order within 3 hours of placing it or before it is confirmed by the seller. If the order is already confirmed, the order cannot be canceled.</p>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="fw-bold text-uppercase small mb-2">Delivery</div>
                                        <p class="text-muted small lh-lg mb-0">Delivery and processing speeds vary by pricing option. Shipping estimates apply to the contiguous US and exclude delivery to P.O. boxes and military bases. Estimates are not guaranteed and may be impacted by weather or carrier delays.</p>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="fw-bold text-uppercase small mb-2">Returns</div>
                                        <p class="text-muted small lh-lg mb-0">All sales with SOLESOURCE are final. Please verify sizing and condition before completing your purchase.</p>
                                    </div>
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

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/product-details.js"></script>
</body>
</html>
