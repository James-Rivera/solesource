<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/head-meta.php'; ?>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <?php
        // Get filter parameters (products are loaded globally via header include)
        $current_brand = isset($_GET['brand']) ? $_GET['brand'] : null;
        $current_sort = isset($_GET['sort']) ? $_GET['sort'] : null;
        $search_term = isset($_GET['search']) ? trim($_GET['search']) : null;

        // Initialize hero defaults and breadcrumb data
        $hero_title = "THE COLLECTION";
        $hero_desc = "Choose from a variety of premium sneakers. Verified Authentic.";
        $breadcrumb_active = "Shop";

        // Scenario A: Search (Priority)
        if ($search_term) {
            $hero_title = "SEARCH RESULTS";
            $hero_desc = "Showing results for \"" . htmlspecialchars($search_term) . "\". Verified authentic sneakers ready to ship.";
            $breadcrumb_active = "Search Results";
            $display_items = array_filter($all_products, function($product) use ($search_term) {
                return stripos($product['name'], $search_term) !== false || 
                       stripos($product['brand'], $search_term) !== false;
            });
        }
        // Scenario B: Brand Filter
        elseif ($current_brand) {
            $hero_title = strtoupper(htmlspecialchars($current_brand));
            $hero_desc = "Explore our collection of authentic " . htmlspecialchars($current_brand) . " sneakers. Premium quality, verified authentic.";
            $breadcrumb_active = htmlspecialchars($current_brand);
            $display_items = array_filter($all_products, function($product) use ($current_brand) {
                return strcasecmp($product['brand'], $current_brand) === 0;
            });
        }
        // Scenario C: Sort = New Releases
        elseif ($current_sort === 'new') {
            $hero_title = "NEW RELEASES";
            $hero_desc = "The freshest drops. Secure your pair before they're gone.";
            $breadcrumb_active = "New Releases";
            $display_items = $all_products; // In production, filter by release date
        }
        // Scenario D: Sort = Best Sellers
        elseif ($current_sort === 'best') {
            $hero_title = "BEST SELLERS";
            $hero_desc = "The community's favorites. Verified authentic and ready to ship.";
            $breadcrumb_active = "Best Sellers";
            $display_items = $all_products; // In production, filter by sales volume
        }
        // Scenario E: Default (All)
        else {
            $display_items = $all_products;
        }

        // Reindex array after filtering
        $display_items = array_values($display_items);
    ?>

    <!-- Hero Section -->
    <section class="catalogue-hero bg-brand-orange text-center py-5">
        <div class="container py-5">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb justify-content-center bg-transparent mb-2">
                    <li class="breadcrumb-item">
                        <a href="index.php" class="text-white-50 text-decoration-none text-uppercase" style="font-size: 0.85rem;">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="shop.php" class="text-white-50 text-decoration-none text-uppercase" style="font-size: 0.85rem;">Shop</a>
                    </li>
                    <?php if ($search_term || $current_brand || $current_sort): ?>
                        <li class="breadcrumb-item active text-white text-uppercase" aria-current="page" style="font-size: 0.85rem;">
                            <?php echo htmlspecialchars($breadcrumb_active); ?>
                        </li>
                    <?php endif; ?>
                </ol>
            </nav>
            
            <h1 class="catalogue-title text-white fw-bold text-uppercase mb-3"><?php echo $hero_title; ?></h1>
            <p class="catalogue-subtitle text-white-50 mx-auto" style="max-width: 600px;">
                <?php echo $hero_desc; ?>
            </p>
        </div>
    </section>

    <!-- Filter Bar (Global search lives in header) -->
    <section class="py-4 bg-light">
        <div class="container">
            <div class="row align-items-center g-3">
                <div class="col-12">
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <a href="shop.php" class="filter-pill <?php echo !$current_brand ? 'active' : ''; ?>">ALL</a>
                        <a href="shop.php?brand=Nike" class="filter-pill <?php echo $current_brand === 'Nike' ? 'active' : ''; ?>">NIKE</a>
                        <a href="shop.php?brand=Adidas" class="filter-pill <?php echo $current_brand === 'Adidas' ? 'active' : ''; ?>">ADIDAS</a>
                        <a href="shop.php?brand=Asics" class="filter-pill <?php echo $current_brand === 'Asics' ? 'active' : ''; ?>">ASICS</a>
                        <a href="shop.php?brand=Puma" class="filter-pill <?php echo $current_brand === 'Puma' ? 'active' : ''; ?>">PUMA</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Grid -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($display_items as $shoe): ?>
                    <?php include 'includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Pagination -->
    <section class="py-4">
        <div class="container">
            <div class="pagination-wrapper d-flex justify-content-between align-items-center">
                <a href="#" class="pagination-arrow text-brand-black text-decoration-none">
                    <i class="bi bi-chevron-left"></i> Back
                </a>
                <span class="pagination-info text-brand-black fw-semibold">1 of 7</span>
                <a href="#" class="pagination-arrow text-brand-black text-decoration-none">
                    Next <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

</body>
</html>
