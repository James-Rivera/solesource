<?php
session_start();
include 'includes/connect.php';
include 'includes/products.php'; // still used for recommendations/search

$normalizeGender = static function ($gender) {
    return in_array($gender, ['Men', 'Women', 'Both'], true) ? $gender : 'Men';
};

// Securely fetch product by id from DB
$product = null;
$requested_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
if ($requested_id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $requested_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result ? $result->fetch_assoc() : null;
    $stmt->close();
}

if (!$product) {
    header('Location: shop.php');
    exit;
}

$primaryGender = $normalizeGender($product['gender'] ?? 'Men');
$secondaryGender = in_array($product['secondary_gender'] ?? 'None', ['Men','Women'], true) ? $product['secondary_gender'] : 'None';
$product['gender'] = $primaryGender;
$product['secondary_gender'] = $secondaryGender;

// Dynamic size label based on gender
$size_label = "SELECT US " . ($primaryGender === 'Women' ? "WOMEN'S" : "MEN'S") . " SIZE";

// Gallery images (reuse main if no additional)
$gallery_images = [$product['image'], $product['image'], $product['image']];

// Recommended items (shuffle and take 4 excluding current)
$recommended = array_values(array_filter($all_products, function($p) use ($requested_id) {
    return (int)($p['id'] ?? 0) !== $requested_id;
}));
shuffle($recommended);
$recommended = array_slice($recommended, 0, 4);

// Load available sizes for this product (US as source of truth; EU derived client-side)
$sizeOptions = [];
$sizeStmt = $conn->prepare("SELECT id, size_label, size_system, gender, stock_quantity, is_active FROM product_sizes WHERE product_id = ? AND size_system = 'US' AND is_active = 1 ORDER BY stock_quantity > 0 DESC, size_label ASC");
$sizeStmt->bind_param('i', $requested_id);
$sizeStmt->execute();
$sizeRes = $sizeStmt->get_result();
while ($row = $sizeRes->fetch_assoc()) {
    if (empty($row['size_label'])) { continue; }
    $row['gender'] = $normalizeGender($row['gender'] ?? $product['gender']);
    $sizeOptions[] = $row;
}
$sizeStmt->close();

if (empty($sizeOptions)) {
    $sizeOptions[] = [
        'id' => null,
        'size_label' => 'Default',
        'size_system' => 'US',
        'gender' => $product['gender'] ?? 'Men',
        'stock_quantity' => $product['stock_quantity'] ?? 0,
        'is_active' => 1,
    ];
}

$selectedSizeId = null;
$selectedSizeLabel = '';
$selectedSystem = 'US';
$selectedGender = $sizeOptions[0]['gender'] ?? $primaryGender;
foreach ($sizeOptions as $opt) {
    if ((int) ($opt['stock_quantity'] ?? 0) > 0 && (int) ($opt['is_active'] ?? 0) === 1) {
        $selectedSizeId = $opt['id'];
        $selectedSizeLabel = $opt['size_label'];
        $selectedSystem = $opt['size_system'];
        $selectedGender = $opt['gender'];
        break;
    }
}
if ($selectedSizeLabel === '' && !empty($sizeOptions)) {
    $selectedSizeId = $sizeOptions[0]['id'];
    $selectedSizeLabel = $sizeOptions[0]['size_label'];
    $selectedSystem = $sizeOptions[0]['size_system'];
    $selectedGender = $sizeOptions[0]['gender'];
}
if ($selectedGender === 'Both') { $selectedGender = $primaryGender; }

// Build unique lists for system and gender toggles
$availableSystems = ['US','EU'];
$genderPool = [];
foreach ($sizeOptions as $o) {
    $g = $normalizeGender($o['gender']);
    if ($g === 'Both') { $genderPool[] = 'Men'; $genderPool[] = 'Women'; }
    else { $genderPool[] = $g; }
}
$availableGenders = array_values(array_unique(array_filter(array_merge(
    $genderPool,
    [$primaryGender],
    $secondaryGender !== 'None' ? [$secondaryGender] : []
), fn($g) => in_array($g, ['Men','Women'], true))));

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

    <style>
        /* Stabilize size grid layout and disabled styling */
        .size-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
            gap: 10px;
            justify-content: center;
        }
        .size-grid .size-tile { position: relative; }
        .size-grid .btn-size {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 50px;
            width: 100%;
            padding: 8px 10px;
        }
        .size-grid .btn-size.disabled,
        .size-grid .btn-size:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #f4f4f4;
            color: #9aa0a6;
            pointer-events: none;
            text-decoration: line-through;
        }
    </style>

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
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="text-uppercase text-muted small fw-semibold mb-0"><?php echo htmlspecialchars($product['brand']); ?></div>
                    </div>
                    <h1 class="product-title-detail mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-price-detail mb-4">₱<?php echo number_format((float)$product['price'], 2); ?></div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <?php if (count($availableSystems) > 1): ?>
                            <div class="size-label text-uppercase small mb-0">Size System:</div>
                            <?php foreach ($availableSystems as $sys): ?>
                                <button type="button" class="size-toggle btn-system<?php echo $sys === $selectedSystem ? ' active' : ''; ?>" data-system="<?php echo htmlspecialchars($sys); ?>"><?php echo htmlspecialchars($sys); ?></button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (count($availableGenders) > 1): ?>
                            <div class="size-label text-uppercase small mb-0 ms-2">Gender:</div>
                            <?php foreach ($availableGenders as $g): ?>
                                <button type="button" class="size-toggle btn-gender<?php echo $g === $selectedGender ? ' active' : ''; ?>" data-gender="<?php echo htmlspecialchars($g); ?>"><?php echo htmlspecialchars($g); ?></button>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="size-grid mb-4" id="sizeSelector">
                        <?php foreach ($sizeOptions as $opt): 
                            $outOfStock = (int) ($opt['stock_quantity'] ?? 0) <= 0;
                            $isSelected = $selectedSizeId === $opt['id'] && $selectedSizeLabel === $opt['size_label'];
                        ?>
                            <div class="size-tile d-flex flex-column align-items-start gap-1">
                                <button
                                    type="button"
                                    class="btn-size<?php echo $isSelected ? ' active' : ''; ?><?php echo $outOfStock ? ' disabled' : ''; ?>"
                                    data-size="<?php echo htmlspecialchars($opt['size_label']); ?>"
                                    data-us-label="<?php echo htmlspecialchars($opt['size_label']); ?>"
                                    data-size-id="<?php echo htmlspecialchars((string) $opt['id']); ?>"
                                    data-size-system="<?php echo htmlspecialchars($opt['size_system']); ?>"
                                    data-size-gender="<?php echo htmlspecialchars($opt['gender']); ?>"
                                    data-stock="<?php echo (int) ($opt['stock_quantity'] ?? 0); ?>"
                                    <?php echo $outOfStock ? 'disabled aria-disabled="true"' : ''; ?>
                                >
                                    <span class="size-text"><?php echo htmlspecialchars($opt['size_label']); ?></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedSize" name="size" value="<?php echo htmlspecialchars($selectedSizeLabel); ?>">
                    <input type="hidden" id="selectedSizeId" name="size_id" value="<?php echo htmlspecialchars((string) $selectedSizeId); ?>">
                    <input type="hidden" id="selectedSystem" name="size_system" value="<?php echo htmlspecialchars($selectedSystem); ?>">
                    <input type="hidden" id="selectedGender" name="size_gender" value="<?php echo htmlspecialchars($selectedGender); ?>">

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
                        <?php echo htmlspecialchars($product['description']); ?>
                    </p>
                    <div class="text-muted small lh-lg">
                        <?php
                            $genderLabel = $product['gender'];
                            if (!empty($product['secondary_gender']) && $product['secondary_gender'] !== 'None' && $product['secondary_gender'] !== $product['gender']) {
                                $genderLabel .= ' / ' . $product['secondary_gender'];
                            }
                        ?>
                        <?php if (!empty($genderLabel)): ?>
                            <div>Gender: <?php echo htmlspecialchars($genderLabel); ?></div>
                        <?php endif; ?>
                        <div>SKU: <?php echo htmlspecialchars($product['sku'] ?? ''); ?></div>
                        <div>Colorway: <?php echo htmlspecialchars($product['colorway'] ?? ''); ?></div>
                        <div>Release Date: <?php echo htmlspecialchars($product['release_date'] ?? ''); ?></div>
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
