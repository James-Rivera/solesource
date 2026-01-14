<?php
require_once __DIR__ . '/../includes/connect.php';

$brand_options = [];
$sport_options = [];
$size_options = [];
$gender_options = [];
$allowed_genders = ['Men','Women'];
$gender_counts = [];
$brand_counts = [];
$sport_counts = [];
$current_brand = null;
$current_sport = null;
$price_min = null;
$price_max = null;
$price_ranges = [
    ['label' => '₱0 - ₱3000', 'min' => 0, 'max' => 3000],
    ['label' => '₱3000 - ₱6000', 'min' => 3000, 'max' => 6000],
    ['label' => '₱6000 and up', 'min' => 6000, 'max' => null],
];
$price_counts = [];

$brand_rs = $conn->query("SELECT DISTINCT brand FROM products WHERE status='active' ORDER BY brand ASC");
if ($brand_rs) {
    while ($row = $brand_rs->fetch_assoc()) {
        if (!empty($row['brand'])) { $brand_options[] = $row['brand']; }
    }
}

$sport_rs = $conn->query("SELECT DISTINCT sport FROM products WHERE status='active' AND sport IS NOT NULL ORDER BY sport ASC");
if ($sport_rs) {
    while ($row = $sport_rs->fetch_assoc()) {
        if (!empty($row['sport'])) { $sport_options[] = $row['sport']; }
    }
}

$size_rs = $conn->query("SELECT DISTINCT size_label FROM product_sizes WHERE is_active = 1 ORDER BY size_label ASC");
if ($size_rs) {
    while ($row = $size_rs->fetch_assoc()) {
        if (!empty($row['size_label'])) { $size_options[] = $row['size_label']; }
    }
}

$gender_rs = $conn->query("(
    SELECT gender AS g, COUNT(*) AS c FROM products WHERE status='active' GROUP BY gender
) UNION ALL (
    SELECT secondary_gender AS g, COUNT(*) AS c FROM products WHERE status='active' AND secondary_gender <> 'None' GROUP BY secondary_gender
)");
if ($gender_rs) {
    while ($row = $gender_rs->fetch_assoc()) {
        $g = $row['g'];
        $c = (int) $row['c'];
        if ($g === 'Men') { $gender_counts['Men'] = ($gender_counts['Men'] ?? 0) + $c; $gender_options[] = 'Men'; }
        if ($g === 'Women') { $gender_counts['Women'] = ($gender_counts['Women'] ?? 0) + $c; $gender_options[] = 'Women'; }
    }
}

$brand_rs_counts = $conn->query("SELECT brand, COUNT(*) AS c FROM products WHERE status='active' GROUP BY brand");
if ($brand_rs_counts) {
    while ($row = $brand_rs_counts->fetch_assoc()) {
        $brand_counts[$row['brand']] = (int)$row['c'];
    }
}

$sport_rs_counts = $conn->query("SELECT sport, COUNT(*) AS c FROM products WHERE status='active' AND sport IS NOT NULL GROUP BY sport");
if ($sport_rs_counts) {
    while ($row = $sport_rs_counts->fetch_assoc()) {
        $sport_counts[$row['sport']] = (int)$row['c'];
    }
}

foreach ($price_ranges as $range) {
    $min = (float)$range['min'];
    $max = $range['max'];
    $sql = "SELECT COUNT(*) AS c FROM products WHERE status='active' AND price >= ?";
    $params = [$min];
    $types = 'd';
    if ($max !== null) { $sql .= " AND price < ?"; $params[] = $max; $types .= 'd'; }
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $price_counts[$range['label']] = (int)($res->fetch_assoc()['c'] ?? 0);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $title = 'SoleSource | Shop';
    include __DIR__ . '/../includes/layout/head.php';
    ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/filter.css">
</head>
<body>

    <?php include __DIR__ . '/../includes/layout/header.php'; ?>

    <?php
        // Helpers
        $format_price = function($price) {
            return '₱' . number_format((float)$price, 2, '.', ',');
        };

        // Get filter parameters
        $selected_brands  = isset($_GET['brand']) ? array_filter((array)$_GET['brand']) : [];
        $selected_genders = isset($_GET['gender']) ? array_filter((array)$_GET['gender']) : [];
        $selected_genders = array_values(array_intersect($selected_genders, $allowed_genders));
        $selected_sports  = isset($_GET['sport']) ? array_filter((array)$_GET['sport']) : [];
        $current_sort  = isset($_GET['sort'])  ? trim($_GET['sort'])  : null;
        $search_term   = isset($_GET['search']) ? trim($_GET['search']) : null;
        $price_min     = isset($_GET['min']) && is_numeric($_GET['min']) ? (float) $_GET['min'] : null;
        $price_max     = isset($_GET['max']) && is_numeric($_GET['max']) ? (float) $_GET['max'] : null;
        $selected_sizes = isset($_GET['size']) && is_array($_GET['size']) ? array_filter($_GET['size']) : [];
        $selected_price_ranges = isset($_GET['prange']) ? array_filter((array)$_GET['prange']) : [];

        // Initialize hero defaults and breadcrumb data
        $hero_title = "THE COLLECTION";
        $hero_desc = "Choose from a variety of premium sneakers. Verified Authentic.";
        $breadcrumb_active = "Shop";

        $conditions = ["status = 'active'"];
        $params = [];
        $types = '';

        if ($search_term) {
            $hero_title = "SEARCH RESULTS";
            $hero_desc = "Showing results for \"" . htmlspecialchars($search_term) . "\". Verified authentic sneakers ready to ship.";
            $breadcrumb_active = "Search Results";
            $conditions[] = "(name LIKE ? OR brand LIKE ?)";
            $like = "%{$search_term}%";
            $params[] = $like; $types .= 's';
            $params[] = $like; $types .= 's';
        }
        if (!empty($selected_brands)) {
            $hero_title = "BRANDS";
            $hero_desc = "Explore our collection of authentic brands curated for you.";
            $breadcrumb_active = "Brands";
            $placeholders = implode(',', array_fill(0, count($selected_brands), '?'));
            $conditions[] = "brand IN ($placeholders)";
            foreach ($selected_brands as $b) { $params[] = $b; $types .= 's'; }
        }
        if (!empty($selected_sports)) {
            $hero_title = "SPORT";
            $hero_desc = "Shop silhouettes curated for your sport preferences.";
            $breadcrumb_active = "Sport";
            $placeholders = implode(',', array_fill(0, count($selected_sports), '?'));
            $conditions[] = "sport IN ($placeholders)";
            foreach ($selected_sports as $s) { $params[] = $s; $types .= 's'; }
        }
        if (!empty($selected_genders)) {
            $hero_title = "GENDER COLLECTION";
            $hero_desc = "Shop styles curated for your selection.";
            $breadcrumb_active = "Gender";
            if (count($selected_genders) === 1) {
                $g = $selected_genders[0];
                if ($g === 'Women') {
                    $conditions[] = "(gender = 'Women' OR secondary_gender = 'Women')";
                } elseif ($g === 'Men') {
                    $conditions[] = "(gender = 'Men' OR secondary_gender = 'Men')";
                }
            } else {
                $conditions[] = "(gender IN ('Men','Women') OR secondary_gender IN ('Men','Women'))";
            }
        }
        elseif ($current_sort === 'new') {
            $hero_title = "NEW RELEASES";
            $hero_desc = "The freshest drops. Secure your pair before they're gone.";
            $breadcrumb_active = "New Releases";
        }
        elseif ($current_sort === 'best') {
            $hero_title = "BEST SELLERS";
            $hero_desc = "The community's favorites. Verified authentic and ready to ship.";
            $breadcrumb_active = "Best Sellers";
        }
        // Filter: featured
        elseif (isset($_GET['filter']) && $_GET['filter'] === 'featured') {
            $hero_title = "FEATURED";
            $hero_desc = "Hand-picked favourites curated by our team.";
            $breadcrumb_active = "Featured";
            $conditions[] = "is_featured = 1";
        }

        if (!empty($selected_price_ranges)) {
            $range_clauses = [];
            foreach ($selected_price_ranges as $label) {
                foreach ($price_ranges as $range) {
                    if ($range['label'] === $label) {
                        $clause = "(price >= ?";
                        $params[] = (float)$range['min'];
                        $types .= 'd';
                        if ($range['max'] !== null) {
                            $clause .= " AND price < ?";
                            $params[] = (float)$range['max'];
                            $types .= 'd';
                        }
                        $clause .= ")";
                        $range_clauses[] = $clause;
                    }
                }
            }
            if (!empty($range_clauses)) {
                $conditions[] = '(' . implode(' OR ', $range_clauses) . ')';
            }
        }

        if ($price_min !== null) { $conditions[] = "price >= ?"; $params[] = $price_min; $types .= 'd'; }
        if ($price_max !== null) { $conditions[] = "price <= ?"; $params[] = $price_max; $types .= 'd'; }
        if (!empty($selected_sizes)) {
            $placeholders = implode(',', array_fill(0, count($selected_sizes), '?'));
            $conditions[] = "id IN (SELECT product_id FROM product_sizes WHERE size_label IN ($placeholders) AND is_active = 1)";
            foreach ($selected_sizes as $sz) { $params[] = $sz; $types .= 's'; }
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        // Sorting
        $order = "ORDER BY created_at DESC";
        if ($current_sort === 'new') {
            $order = "ORDER BY release_date DESC, created_at DESC";
        } elseif ($current_sort === 'best') {
            $order = "ORDER BY total_sold DESC, is_featured DESC, created_at DESC";
        }

        // Pagination
        $per_page = 24;
        $page = max(1, (int)($_GET['page_num'] ?? 1));
        $offset = ($page - 1) * $per_page;

        // Total count with current filters
        $count_sql = "SELECT COUNT(*) AS total FROM products $where";
        $count_stmt = $conn->prepare($count_sql);
        if ($types) { $count_stmt->bind_param($types, ...$params); }
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_rows = (int) ($count_result->fetch_assoc()['total'] ?? 0);
        $count_stmt->close();

        $total_pages = max(1, (int)ceil($total_rows / $per_page));
        if ($page > $total_pages) {
            $page = $total_pages;
            $offset = ($page - 1) * $per_page;
        }

        $sql = "SELECT p.*, (SELECT COALESCE(SUM(ps.stock_quantity), p.stock_quantity) FROM product_sizes ps WHERE ps.product_id = p.id AND ps.is_active = 1) AS stock_total FROM products p $where $order LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);

        $select_params = $params;
        $select_types = $types . 'ii';
        $select_params[] = $per_page;
        $select_params[] = $offset;

        $stmt->bind_param($select_types, ...$select_params);
        $stmt->execute();
        $result = $stmt->get_result();

        $display_items = [];
        while ($row = $result->fetch_assoc()) {
            $row['price'] = $format_price($row['price']);
            $display_items[] = $row;
        }
        $stmt->close();

        $build_page_link = function($targetPage) {
            $query = $_GET;
            $query['page_num'] = $targetPage;
            unset($query['page']); // Remove router param if present
            return 'shop.php?' . http_build_query($query);
        };

        $gender_labels = array_keys($gender_counts);
        sort($gender_labels);
        if (empty($gender_labels) && !empty($gender_options)) {
            $gender_labels = $gender_options;
            sort($gender_labels);
        }
        if (empty($gender_labels)) {
            $gender_labels = $allowed_genders;
        }
    ?>

    <!-- Hero Section -->
    <section class="catalogue-hero bg-brand-orange text-center py-5">
        <div class="container py-5">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb justify-content-center bg-transparent mb-2">
                    <li class="breadcrumb-item">
                        <a href="index.php" class="text-white-50 text-decoration-none text-uppercase small">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="shop.php" class="text-white-50 text-decoration-none text-uppercase small">Shop</a>
                    </li>
                    <?php if ($breadcrumb_active && $breadcrumb_active !== 'Shop'): ?>
                        <li class="breadcrumb-item active text-white text-uppercase small" aria-current="page">
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

    <!-- Product Grid with Sidebar Filters -->
    <section class="py-5">
        <div class="container">

            <!-- Mobile Filter Trigger -->
            <div class="d-lg-none mb-3">
                <button class="btn btn-dark w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterDrawer" aria-controls="filterDrawer">
                    <span>Filter &amp; Sort</span>
                    <i class="bi bi-sliders2-vertical"></i>
                </button>
            </div>

            <!-- Mobile Filter Drawer -->
            <div class="offcanvas offcanvas-end filter-drawer" tabindex="-1" id="filterDrawer" aria-labelledby="filterDrawerLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="filterDrawerLabel">Filter &amp; Sort</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <form class="filters-form" id="filtersFormMobile" method="get">
                        <?php if ($search_term): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>"><?php endif; ?>
                        <?php if ($current_sort): ?><input type="hidden" name="sort" value="<?php echo htmlspecialchars($current_sort); ?>"><?php endif; ?>

                        <div class="accordion-item">
                            <button type="button" class="accordion-button" data-target="#m-acc-gender">Gender<span class="caret">+</span></button>
                            <div id="m-acc-gender" class="accordion-body">
                                <?php foreach ($gender_labels as $g): ?>
                                    <?php $checked = in_array($g, $selected_genders, true) ? 'checked' : ''; ?>
                                    <label><input type="checkbox" name="gender[]" value="<?php echo htmlspecialchars($g); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($g); ?> (<?php echo $gender_counts[$g] ?? 0; ?>)</label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <button type="button" class="accordion-button" data-target="#m-acc-sport">Sport<span class="caret">+</span></button>
                            <div id="m-acc-sport" class="accordion-body">
                                <?php foreach ($sport_options as $sport): ?>
                                    <?php $checked = in_array($sport, $selected_sports, true) ? 'checked' : ''; ?>
                                    <label><input type="checkbox" name="sport[]" value="<?php echo htmlspecialchars($sport); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($sport); ?> (<?php echo $sport_counts[$sport] ?? 0; ?>)</label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <button type="button" class="accordion-button" data-target="#m-acc-brand">Brand<span class="caret">+</span></button>
                            <div id="m-acc-brand" class="accordion-body">
                                <?php foreach ($brand_counts as $brand => $count): ?>
                                    <?php $checked = in_array($brand, $selected_brands, true) ? 'checked' : ''; ?>
                                    <label><input type="checkbox" name="brand[]" value="<?php echo htmlspecialchars($brand); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($brand); ?> (<?php echo $count; ?>)</label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <button type="button" class="accordion-button" data-target="#m-acc-price">Price Range<span class="caret">+</span></button>
                            <div id="m-acc-price" class="accordion-body">
                                <div class="price-range mb-2">
                                    <input type="number" step="0.01" name="min" placeholder="Min" value="<?php echo htmlspecialchars($price_min ?? ''); ?>">
                                    <span>-</span>
                                    <input type="number" step="0.01" name="max" placeholder="Max" value="<?php echo htmlspecialchars($price_max ?? ''); ?>">
                                </div>
                                <?php foreach ($price_ranges as $range): ?>
                                    <?php $checked = in_array($range['label'], $selected_price_ranges, true) ? 'checked' : ''; ?>
                                    <label><input type="checkbox" name="prange[]" value="<?php echo htmlspecialchars($range['label']); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($range['label']); ?> (<?php echo $price_counts[$range['label']] ?? 0; ?>)</label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <button type="button" class="accordion-button" data-target="#m-acc-size">Size<span class="caret">+</span></button>
                            <div id="m-acc-size" class="accordion-body">
                                <div class="size-grid">
                                    <?php foreach ($size_options as $size): ?>
                                        <?php $checked = in_array($size, $selected_sizes, true) ? 'checked' : ''; ?>
                                        <label><input type="checkbox" name="size[]" value="<?php echo htmlspecialchars($size); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($size); ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="filter-drawer-actions">
                    <button type="button" class="btn btn-outline-dark w-50" id="filterClearMobile">Clear All</button>
                    <button type="submit" form="filtersFormMobile" class="btn btn-dark w-50" id="filterApplyMobile">Apply</button>
                </div>
            </div>

            <div class="row g-4">

                <!-- Sidebar Filters -->
                <div class="col-12 col-lg-3">
                    <div class="filters-sidebar">
                        <h5 class="mb-3">Filters</h5>
                        <form class="filters-form" id="filtersForm" method="get">
                            <?php if ($search_term): ?><input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>"><?php endif; ?>
                            <?php if ($current_sort): ?><input type="hidden" name="sort" value="<?php echo htmlspecialchars($current_sort); ?>"><?php endif; ?>

                            <div class="accordion-item">
                                <button type="button" class="accordion-button" data-target="#acc-gender">Gender<span class="caret">+</span></button>
                                <div id="acc-gender" class="accordion-body">
                                    <?php foreach ($gender_labels as $g): ?>
                                        <?php $checked = in_array($g, $selected_genders, true) ? 'checked' : ''; ?>
                                        <label><input type="checkbox" name="gender[]" value="<?php echo htmlspecialchars($g); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($g); ?> (<?php echo $gender_counts[$g] ?? 0; ?>)</label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <button type="button" class="accordion-button" data-target="#acc-sport">Sport<span class="caret">+</span></button>
                                <div id="acc-sport" class="accordion-body">
                                    <?php foreach ($sport_options as $sport): ?>
                                        <?php $checked = in_array($sport, $selected_sports, true) ? 'checked' : ''; ?>
                                        <label><input type="checkbox" name="sport[]" value="<?php echo htmlspecialchars($sport); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($sport); ?> (<?php echo $sport_counts[$sport] ?? 0; ?>)</label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <button type="button" class="accordion-button" data-target="#acc-brand">Brand<span class="caret">+</span></button>
                                <div id="acc-brand" class="accordion-body">
                                    <?php foreach ($brand_counts as $brand => $count): ?>
                                        <?php $checked = in_array($brand, $selected_brands, true) ? 'checked' : ''; ?>
                                        <label><input type="checkbox" name="brand[]" value="<?php echo htmlspecialchars($brand); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($brand); ?> (<?php echo $count; ?>)</label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <button type="button" class="accordion-button" data-target="#acc-price">Price Range<span class="caret">+</span></button>
                                <div id="acc-price" class="accordion-body">
                                    <div class="price-range mb-2">
                                        <input type="number" step="0.01" name="min" placeholder="Min" value="<?php echo htmlspecialchars($price_min ?? ''); ?>">
                                        <span>-</span>
                                        <input type="number" step="0.01" name="max" placeholder="Max" value="<?php echo htmlspecialchars($price_max ?? ''); ?>">
                                    </div>
                                    <?php foreach ($price_ranges as $range): ?>
                                        <?php $checked = in_array($range['label'], $selected_price_ranges, true) ? 'checked' : ''; ?>
                                        <label><input type="checkbox" name="prange[]" value="<?php echo htmlspecialchars($range['label']); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($range['label']); ?> (<?php echo $price_counts[$range['label']] ?? 0; ?>)</label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <button type="button" class="accordion-button" data-target="#acc-size">Size<span class="caret">+</span></button>
                                <div id="acc-size" class="accordion-body">
                                    <div class="size-grid">
                                        <?php foreach ($size_options as $size): ?>
                                            <?php $checked = in_array($size, $selected_sizes, true) ? 'checked' : ''; ?>
                                            <label><input type="checkbox" name="size[]" value="<?php echo htmlspecialchars($size); ?>" <?php echo $checked; ?>> <?php echo htmlspecialchars($size); ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-actions">
                                <button type="submit" class="btn-apply">Apply</button>
                                <a class="btn-reset" href="shop.php">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Product Grid -->
                <div class="col-12 col-lg-9">
                    <?php if (empty($display_items)): ?>
                        <div class="card border-0 shadow-sm p-4 h-100 d-flex align-items-center justify-content-center text-center">
                            <h5 class="fw-bold text-brand-black mb-2">No products found</h5>
                            <p class="text-muted mb-3">Try adjusting filters or search to find a match.</p>
                            <a href="shop.php" class="btn btn-dark">Clear filters</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3 plp-grid">
                            <?php foreach ($display_items as $shoe): ?>
                                <?php include __DIR__ . '/../includes/partials/product-card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>

    <script>
    document.querySelectorAll('.accordion-button').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.querySelector(btn.dataset.target);
            if (!target) return;
            target.classList.toggle('open');
            target.style.display = target.classList.contains('open') ? 'block' : 'none';
        });
    });

    const filterForm = document.getElementById('filtersForm');
    const filterFormMobile = document.getElementById('filtersFormMobile');

    [filterForm, filterFormMobile].forEach(form => {
        if (!form) return;
        form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.addEventListener('change', () => form.submit());
        });
        form.querySelectorAll('input[type="number"]').forEach(inp => {
            inp.addEventListener('change', () => form.submit());
        });
    });

    document.getElementById('filterClearMobile')?.addEventListener('click', () => {
        filterFormMobile?.reset();
        filterFormMobile?.submit();
    });

    // Open all accordions by default on load
    document.querySelectorAll('.accordion-body').forEach(body => {
        body.classList.add('open');
        body.style.display = 'block';
    });
    </script>

    <!-- Pagination -->
    <section class="py-4">
        <div class="container">
            <div class="pagination-wrapper d-flex justify-content-between align-items-center">
                <?php if ($page > 1): ?>
                    <a href="<?php echo htmlspecialchars($build_page_link($page - 1)); ?>" class="pagination-arrow text-brand-black text-decoration-none">
                        <i class="bi bi-chevron-left"></i> Back
                    </a>
                <?php else: ?>
                    <span class="pagination-arrow text-muted text-decoration-none opacity-50">
                        <i class="bi bi-chevron-left"></i> Back
                    </span>
                <?php endif; ?>
                <span class="pagination-info text-brand-black fw-semibold">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo htmlspecialchars($build_page_link($page + 1)); ?>" class="pagination-arrow text-brand-black text-decoration-none">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-arrow text-muted text-decoration-none opacity-50">
                        Next <i class="bi bi-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/layout/footer.php'; ?>

</body>
</html>
