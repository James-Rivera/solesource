<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Premium Sneakers</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include 'includes/header.php'; ?>


    <?php
        $new_releases = [
            [
                'brand' => 'Asics',
                'name'  => 'GEL-KAYANO 14',
                'price' => '₱ 9,490.00',
                'image' => 'assets/img/products/new/asics-gel-kayano-14.png',
            ],
            [
                'brand' => 'Adidas',
                'name'  => 'ADIDAS GAZELLE INDOOR',
                'price' => '₱ 4,700.00',
                'image' => 'assets/img/products/new/adidas-gazelle-indoor.png',
            ],
            [
                'brand' => 'Jordan',
                'name'  => "JORDAN 11 RETRO 'COLUMBIA / LEGEND BLUE' 2024",
                'price' => '₱ 12,000.00',
                'image' => 'assets/img/products/new/jordan-11-legend-blue.png',
            ],
            [
                'brand' => 'Nike',
                'name'  => 'AIR FORCE 1',
                'price' => '₱ 4,999.00',
                'image' => 'assets/img/products/new/air-force-1.png',
            ],
        ];

        $best_selling = [];
        for ($i = 0; $i < 8; $i++) {
            $best_selling[] = [
                'brand' => 'Nike',
                'name'  => 'AIR FORCE 1',
                'price' => '₱ 4,999.00',
                'image' => 'assets/img/products/best/air-force-1.png',
            ];
        }

        $brands = [
            ['name' => 'Nike', 'logo' => 'assets/img/brands/nike.svg'],
            ['name' => 'Asics', 'logo' => 'assets/img/brands/asics.png'],
            ['name' => 'Onitsuka Tiger', 'logo' => 'assets/img/brands/onitsuka.png'],
            ['name' => 'Adidas', 'logo' => 'assets/img/brands/adidas.png'],
        ];
    ?>


    <section class="hero-section">
        <div class="container h-100">
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
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-6">
                    <h2 class="mb-0 fw-bold text-uppercase text-brand-black">NEW RELEASE</h2>
                </div>
                <div class="col-6 text-end">
                    <a href="#" class="text-decoration-underline text-lowercase text-brand-black fw-semibold">find more</a>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($new_releases as $shoe): ?>
                    <?php include 'includes/product-card.php'; ?>
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
                    <a href="#" class="text-decoration-underline text-lowercase text-brand-black fw-semibold">find more</a>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($best_selling as $shoe): ?>
                    <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <section class="py-5">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-6">
                    <h2 class="mb-0 fw-bold text-lowercase text-brand-black">brands</h2>
                </div>
                <div class="col-6 text-end">
                    <a href="#" class="text-decoration-underline text-lowercase text-brand-black fw-semibold">find more</a>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($brands as $brand): ?>
                    <div class="col-6 col-md-3">
                        <div class="brand-card d-flex align-items-center justify-content-center h-100">
                            <img src="<?php echo htmlspecialchars($brand['logo']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" class="img-fluid brand-logo">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <section class="py-5 bg-brand-dark-gray" style="background-color: #333333;">
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


    <?php include 'includes/footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>