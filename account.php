<?php
session_start();
// Mock user data - replace with actual database query
$user = [
    'name' => 'JUAN DELA CRUZ',
    'email' => 'juandelacruz@gmail.com',
    'member_since' => 'December 29, 2025',
    'birthdate' => '1990 - 25 - 03',
    'gender' => 'Male'
];

// Mock wishlist and purchases
$wishlist = [
    ['id' => 1, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 2, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 3, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 4, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
];

$purchases = [
    ['id' => 1, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 2, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 3, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 4, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 5, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 6, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 7, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 8, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | My Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/head-meta.php'; ?>
    <style>
        /* Account Navigation */
        .account-nav {
            background: #E35926;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .account-nav .nav-link {
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.5rem;
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
        }
        
        .account-nav .nav-link:hover {
            color: #fff;
        }
        
        .account-nav .nav-link.active {
            color: #fff;
            border-bottom-color: #fff;
        }

        /* Profile Hero */
        .profile-hero {
            background: #E35926;
            color: #fff;
            padding: 3rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .profile-hero-content h1 {
            font-size: 2.5rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .profile-hero-content .member-since {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .profile-hero-icon {
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.15;
        }
        
        .profile-hero-icon img {
            width: 150px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        /* Product Grids */
        .account-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: capitalize;
            color: var(--brand-black);
            margin-bottom: 1.5rem;
        }
        
        .account-product-card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .account-product-card:hover {
            border-color: var(--brand-black);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .account-product-img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            padding: 1rem;
            background: #fafafa;
            border-radius: 8px 8px 0 0;
        }
        
        .account-product-body {
            padding: 1rem;
        }
        
        .account-product-brand {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }
        
        .account-product-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--brand-black);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .account-product-price {
            font-size: 1rem;
            font-weight: 600;
            color: var(--brand-black);
        }

        /* Orders Empty State */
        .orders-empty {
            text-align: center;
            padding: 5rem 0;
        }
        
        .orders-empty h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--brand-black);
            margin-bottom: 1rem;
        }
        
        .orders-empty p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .orders-empty .btn-shop {
            background: var(--brand-orange);
            color: #fff;
            padding: 0.875rem 3rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 8px;
            border: none;
            letter-spacing: 0.5px;
        }
        
        .orders-empty .btn-shop:hover {
            background: #cc4e21;
        }

        /* Settings Layout */
        .settings-sidebar {
            padding-right: 2rem;
        }
        
        .settings-sidebar-title {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--brand-black);
            margin-bottom: 1.5rem;
        }
        
        .settings-sidebar .nav-link {
            color: #666;
            padding: 0.75rem 0;
            border: none;
            text-align: left;
            font-size: 1rem;
            transition: color 0.2s ease;
        }
        
        .settings-sidebar .nav-link:hover {
            color: var(--brand-black);
        }
        
        .settings-sidebar .nav-link.active {
            color: var(--brand-orange);
            font-weight: 600;
        }

        /* Settings Content */
        .settings-content-title {
            font-size: 2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
            margin-bottom: 1rem;
        }
        
        .settings-subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .settings-section {
            margin-bottom: 3rem;
        }
        
        .settings-section-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
            margin-bottom: 1.5rem;
        }
        
        .settings-detail-item {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e5e5;
        }
        
        .settings-detail-label {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 0.25rem;
        }
        
        .settings-detail-value {
            font-size: 1.1rem;
            color: var(--brand-black);
        }
        
        .settings-edit-link {
            color: var(--brand-black);
            text-decoration: underline;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .settings-edit-link:hover {
            color: var(--brand-orange);
        }
        
        .btn-delete-account {
            background: #6c757d;
            color: #fff;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 6px;
            border: none;
            letter-spacing: 0.5px;
        }
        
        .btn-delete-account:hover {
            background: #5a6268;
        }

        @media (max-width: 991px) {
            .profile-hero-icon {
                display: none;
            }
            
            .settings-sidebar {
                padding-right: 0;
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Account Navigation Tabs -->
    <nav class="account-nav sticky-top">
        <div class="container-xxl">
            <ul class="nav nav-tabs border-0 justify-content-center" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Orders</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Settings</button>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Profile Tab -->
        <div class="tab-pane fade show active" id="profile" role="tabpanel">
            <!-- Hero Banner -->
            <section class="profile-hero">
                <div class="container-xxl position-relative">
                    <div class="profile-hero-content">
                        <h1>HI <?php echo strtoupper($user['name']); ?></h1>
                        <p class="member-since">Member since <?php echo $user['member_since']; ?></p>
                    </div>
                    <div class="profile-hero-icon d-none d-lg-block">
                        <img src="assets/svg/logo-big-white.svg" alt="SoleSource Icon">
                    </div>
                </div>
            </section>

            <!-- Wishlist Section -->
            <section class="py-5 bg-light">
                <div class="container-xxl">
                    <h2 class="account-section-title">My wishlist</h2>
                    <div class="row g-4">
                        <?php foreach ($wishlist as $item): ?>
                            <div class="col-6 col-md-3">
                                <div class="account-product-card">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="account-product-img">
                                    <div class="account-product-body">
                                        <div class="account-product-brand"><?php echo $item['brand']; ?></div>
                                        <div class="account-product-name"><?php echo $item['name']; ?></div>
                                        <div class="account-product-price"><?php echo $item['price']; ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Purchases Section -->
            <section class="py-5">
                <div class="container-xxl">
                    <h2 class="account-section-title">My purchases</h2>
                    <div class="row g-4">
                        <?php foreach ($purchases as $item): ?>
                            <div class="col-6 col-md-3">
                                <div class="account-product-card">
                                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="account-product-img">
                                    <div class="account-product-body">
                                        <div class="account-product-brand"><?php echo $item['brand']; ?></div>
                                        <div class="account-product-name"><?php echo $item['name']; ?></div>
                                        <div class="account-product-price"><?php echo $item['price']; ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- Orders Tab -->
        <div class="tab-pane fade" id="orders" role="tabpanel">
            <section class="py-5">
                <div class="container-xxl">
                    <h2 class="account-section-title">ORDERS</h2>
                    <hr class="mb-5">
                    <div class="orders-empty">
                        <h2>NO ORDERS YET</h2>
                        <p>time to start the collection</p>
                        <a href="shop.php" class="btn btn-shop">Shop now</a>
                    </div>
                    
                    <hr class="mt-5">
                    <div class="mt-4">
                        <h5 class="fw-bold text-brand-black">looking for your order?</h5>
                        <a href="#" class="text-decoration-underline text-brand-black">Check the status of your order</a>
                    </div>
                </div>
            </section>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings" role="tabpanel">
            <section class="py-5">
                <div class="container-xxl">
                    <div class="row">
                        <!-- Sidebar -->
                        <div class="col-lg-3 settings-sidebar">
                            <div class="settings-sidebar-title">ACCOUNT OVERVIEW</div>
                            <nav class="nav flex-column">
                                <a class="nav-link active" href="#personal-info" data-bs-toggle="pill">Personal Information</a>
                                <a class="nav-link" href="#address" data-bs-toggle="pill">Address</a>
                                <a class="nav-link" href="logout.php">Log out</a>
                            </nav>
                        </div>

                        <!-- Main Content -->
                        <div class="col-lg-9">
                            <div class="tab-content">
                                <!-- Personal Information -->
                                <div class="tab-pane fade show active" id="personal-info">
                                    <h1 class="settings-content-title">MY DETAILS</h1>
                                    <p class="settings-subtitle">Feel free to edit any of your details below so your account is up to date</p>

                                    <!-- Personal Details -->
                                    <div class="settings-section">
                                        <h3 class="settings-section-title">DETAILS</h3>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">NAME</div>
                                            <div class="settings-detail-value"><?php echo $user['name']; ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">DATE OF BIRTH</div>
                                            <div class="settings-detail-value"><?php echo $user['birthdate']; ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">GENDER</div>
                                            <div class="settings-detail-value"><?php echo $user['gender']; ?></div>
                                        </div>
                                        <a href="#" class="settings-edit-link">EDIT</a>
                                    </div>

                                    <!-- Account Details -->
                                    <div class="settings-section">
                                        <h3 class="settings-section-title">ACCOUNT DETAILS</h3>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">EMAIL</div>
                                            <div class="settings-detail-value"><?php echo $user['email']; ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">PASSWORD</div>
                                            <div class="settings-detail-value">*****************</div>
                                        </div>
                                        <a href="#" class="settings-edit-link">EDIT</a>
                                    </div>

                                    <!-- Manage Account -->
                                    <div class="settings-section">
                                        <h3 class="settings-section-title">Manage Account</h3>
                                        <button type="button" class="btn btn-delete-account">Delete Account</button>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="tab-pane fade" id="address">
                                    <h1 class="settings-content-title">MY ADRESS</h1>
                                    <p class="settings-subtitle">Please add your address for easier shopping</p>

                                    <div class="card p-4" style="border: 2px dashed #ddd; min-height: 300px;">
                                        <div class="d-flex align-items-center justify-content-center h-100">
                                            <div class="text-center">
                                                <button type="button" class="btn btn-link text-brand-black" style="font-size: 3rem;">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                                <p class="mt-2 fw-bold">ADDRESS 1</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
