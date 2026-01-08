<?php
require_once __DIR__ . '/includes/connect.php';

$routes = [
    'cart' => __DIR__ . '/pages/cart.php',
    'checkout' => __DIR__ . '/pages/checkout.php',
    'confirmation' => __DIR__ . '/pages/confirmation.php',
    'login' => __DIR__ . '/pages/login.php',
    'logout' => __DIR__ . '/pages/logout.php',
    'product' => __DIR__ . '/pages/product-details.php',
    'product-details' => __DIR__ . '/pages/product-details.php',
    'profile' => __DIR__ . '/pages/profile.php',
    'shop' => __DIR__ . '/pages/shop.php',
    'signup' => __DIR__ . '/pages/signup.php',
    'view_order' => __DIR__ . '/pages/view_order.php',
    'view-order' => __DIR__ . '/pages/view_order.php',
    'test-mail' => __DIR__ . '/pages/test-mail.php',
];

$pageKey = $_GET['page'] ?? null;
if ($pageKey !== null) {
    if (isset($routes[$pageKey]) && is_file($routes[$pageKey])) {
        require $routes[$pageKey];
    } else {
        http_response_code(404);
        echo 'Page not found';
    }
    exit;
}

$title = 'SoleSource | Premium Sneakers';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . '/includes/layout/head.php'; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <?php include __DIR__ . '/includes/layout/header.php'; ?>


    <?php
    $format_price = function ($price) {
        return '₱' . number_format((float)$price, 2, '.', ',');
    };

    $fetch_products = function ($orderClause, $limit = 4) use ($conn, $format_price) {
        $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY $orderClause LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $items = [];
        while ($row = $res->fetch_assoc()) {
            $row['price'] = $format_price($row['price']);
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    };

    $new_releases = $fetch_products("release_date DESC, created_at DESC", 4);
    $best_sellers = $fetch_products("total_sold DESC, is_featured DESC, created_at DESC", 4);

    $brands = [
        ['name' => 'Nike', 'logo' => 'assets/img/brands/nike.svg'],
        ['name' => 'Asics', 'logo' => 'assets/img/brands/asics.png'],
        ['name' => 'Onitsuka Tiger', 'logo' => 'assets/img/brands/onitsuka.png'],
        ['name' => 'Adidas', 'logo' => 'assets/img/brands/adidas.png'],
    ];
    ?>


    <section class="hero-section position-relative overflow-hidden">
        
        <div class="container h-100 py-5">
            <div class="row h-100 align-items-center">
                <div class="col-lg-6 col-md-8">
                    <h1 class="hero-title mb-4">
                        <span class="d-block">ADIDAS</span>
                        <span class="d-block">GAZELLE</span>
                    </h1>
                    <a class="hero-cta" href="#">shop now</a>
                </div>
            </div>
        </div>
        <div class="hero-media"></div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-6">
                    <h2 class="mb-0 fw-bold text-uppercase text-brand-black">NEW RELEASE</h2>
                </div>
                <div class="col-6 text-end">
                    <a href="shop.php?sort=new" class="text-decoration-underline text-lowercase text-brand-black fw-semibold">find more</a>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($new_releases as $shoe): ?>
                    <?php include __DIR__ . '/includes/partials/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Carousel Section - fix functionality later -->
    <section class="py-5">
        <div class="container-xxl">
            <div id="retroCarousel" class="carousel slide retro-carousel" data-bs-ride="carousel">
                <div class="carousel-inner rounded-5 overflow-hidden">
                    <div class="carousel-item active">
                        <div class="retro-slide d-flex align-items-center justify-content-center text-center">
                            <div class="retro-overlay position-absolute top-0 start-0 w-100 h-100"></div>
                            <div class="position-relative text-white">
                                <h2 class="retro-title mb-2">RETRO ARCHIVE</h2>
                                <p class="retro-subtitle mb-0">Timeless silhouettes. Verified authentic.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="carousel-control-prev retro-control" type="button" data-bs-target="#retroCarousel" data-bs-slide="prev">
                    <span class="retro-control-btn" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next retro-control" type="button" data-bs-target="#retroCarousel" data-bs-slide="next">
                    <span class="retro-control-btn" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>


    <section class="py-5">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-6">
                    <h2 class="mb-0 fw-bold text-lowercase text-brand-black">best selling</h2>
                </div>
                <div class="col-6 text-end">
                    <a href="shop.php?sort=best" class="text-decoration-underline text-lowercase text-brand-black fw-semibold">find more</a>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($best_sellers as $shoe): ?>
                    <?php include __DIR__ . '/includes/partials/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <section class="py-5 mb-5">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-6">
                    <h2 class="mb-0 fw-bold text-lowercase text-brand-black">brands</h2>
                </div>
                <div class="col-6 text-end">
                    <a href="#" class="text-decoration-underline text-lowercase text-brand-black fw-semibold">find more</a>
                </div>
            </div>

            <div class="row mt-4 g-4">
                <?php foreach ($brands as $brand): ?>
                    <div class="col-6 col-md-3">
                        <a href="shop.php?brand=<?php echo urlencode($brand['name']); ?>" class="text-decoration-none">
                            <div class="brand-card d-flex align-items-center justify-content-center h-100">
                                <img src="<?php echo htmlspecialchars($brand['logo']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" class="img-fluid brand-logo">
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <section class="py-5 mt-5 bg-brand-dark-gray" style="background-color: #333333;">
        <div class="container my-4">
            <div class="row gy-4">
                <div class="col-lg-6">
                    <div class="d-flex flex-column h-100">
                        <div class="img-wrapper mb-5 w-100 bg-transparent">
                            <img src="assets/img/editorial/quality.jpg" alt="Authentication" class="img-fluid w-100">
                        </div>
                        <h3 class="fw-bold text-white mb-3">100% Verified Authentic</h3>
                        <p class="editorial-text mb-4" style="text-align: justify;">
                            Every item sold on SoleSource goes through our rigorous multi-point inspection process. If it's not real, it never leaves our warehouse.
                        </p>

                        <div class="mt-auto">
                            <a href="#" class="text-white text-decoration-underline text-lowercase">our process</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="d-flex flex-column h-100">
                        <div class="img-wrapper mb-5 w-100 bg-transparent">
                            <img src="assets/img/editorial/rotation.jpg" alt="The Rotation" class="img-fluid w-100">
                        </div>
                        <h3 class="editorial-title fw-bold mb-3">THE ROTATION.</h3>
                        <p class="editorial-text mb-4" style="text-align: justify;">
                            Streetwear is evolving. From the terrace-culture revival of the Adidas Samba to the rugged utility of Gorpcore, 2025 is defined by versatility. We dive deep into the data to bring you the silhouettes that matter right now. Explore the definitive guide to this year's essential rotation.
                        </p>

                        <div class="mt-auto">
                            <a href="#" class="text-white text-decoration-underline text-lowercase">read story</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php include __DIR__ . '/includes/layout/footer.php'; ?>


</body>

</html>