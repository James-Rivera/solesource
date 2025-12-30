<?php
session_start();
// Mock user data - replace with actual database query
$user = [
    'name' => 'JUAN DELA CRUZ',
    'email' => 'juandelacruz@gmail.com',
    'member_since' => 'December 29, 2025',
    'birthdate' => '1990-03-25',
    'gender' => 'Male'
];

// Mock wishlist - using product structure
$wishlist_products = [
    ['id' => 1, 'brand' => 'NIKE', 'name' => 'AIR FORCE 1', 'price' => '₱4,999.00', 'image' => 'assets/img/products/best/air-force-1.png'],
    ['id' => 2, 'brand' => 'ADIDAS', 'name' => 'GAZELLE INDOOR', 'price' => '₱5,500.00', 'image' => 'assets/img/products/new/adidas-gazelle.png'],
    ['id' => 3, 'brand' => 'JORDAN', 'name' => 'AIR JORDAN 1 HIGH', 'price' => '₱8,295.00', 'image' => 'assets/img/products/best/jordan-1-high.png'],
];

// Mock order history
$recent_orders = [
    [
        'order_number' => '#2401-9921',
        'date' => 'Dec 30, 2025',
        'status' => 'Processing',
        'status_class' => 'bg-warning text-dark',
        'image' => 'assets/img/products/new/jordan-11-legend-blue.png'
    ],
    [
        'order_number' => '#2401-9820',
        'date' => 'Dec 28, 2025',
        'status' => 'Shipped',
        'status_class' => 'bg-info text-dark',
        'image' => 'assets/img/products/best/air-force-1.png'
    ],
    [
        'order_number' => '#2401-9819',
        'date' => 'Dec 25, 2025',
        'status' => 'Delivered',
        'status_class' => 'bg-success text-white',
        'image' => 'assets/img/products/new/adidas-gazelle.png'
    ],
];

$purchases_ids = [1, 2, 3, 4, 5, 6, 7, 8];
$hasOrders = true; // Toggle to show/hide order history
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
    <link rel="stylesheet" href="assets/css/account.css">
    <?php include 'includes/head-meta.php'; ?>
    <style>
        /* Force HR visibility */
        hr {
            background-color: #000 !important;
            opacity: 1 !important;
            border: none;
            height: 1px;
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
                        <img src="assets/img/svg/white-logo.svg" alt="SoleSource Icon" style="width: 100px; height: 100px;">
                    </div>
                </div>
            </section>

            <!-- Wishlist Section -->
            <section class="py-5 bg-light">
                <div class="container-xxl">
                    <h2 class="account-section-title">My wishlist</h2>
                    <div class="row row-cols-2 row-cols-md-4 g-4">
                        <?php foreach ($wishlist_products as $product): ?>
                            <div class="col">
                                <div class="wishlist-product-card h-100">
                                    <div class="wishlist-product-image-wrapper position-relative">
                                        <button class="wishlist-remove-btn" data-product-id="<?php echo $product['id']; ?>" aria-label="Remove from wishlist">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <div class="ratio ratio-1x1 wishlist-product-media">
                                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="wishlist-product-image">
                                        </div>
                                    </div>
                                    <div class="wishlist-product-body">
                                        <div class="wishlist-product-brand text-muted small fw-bold text-uppercase"><?php echo $product['brand']; ?></div>
                                        <div class="wishlist-product-name fw-bold text-dark"><?php echo $product['name']; ?></div>
                                        <div class="wishlist-product-price"><?php echo $product['price']; ?></div>
                                        <button class="btn btn-add-to-cart btn-sm" data-product-id="<?php echo $product['id']; ?>">
                                            Add to Cart
                                        </button>
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
                        <?php 
                        foreach ($purchases_ids as $id) {
                            $shoe = null;
                            foreach ($all_products as $p) {
                                if ($p['id'] == $id) {
                                    $shoe = $p;
                                    break;
                                }
                            }
                            if ($shoe):
                                include 'includes/product-card.php';
                            endif;
                        }
                        ?>
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
                    
                    <?php if ($hasOrders): ?>
                        <!-- Recent Orders List -->
                        <div class="mb-5">
                            <div class="order-history-list">
                                <?php foreach ($recent_orders as $index => $order): ?>
                                    <div class="order-history-row d-flex align-items-center py-4 <?php echo $index !== count($recent_orders) - 1 ? 'border-bottom border-light' : ''; ?>">
                                        <!-- Product Image -->
                                        <div class="order-history-image flex-shrink-0 me-4">
                                            <img src="<?php echo $order['image']; ?>" alt="Order Item" class="rounded">
                                        </div>
                                        
                                        <!-- Order Details -->
                                        <div class="order-history-details flex-grow-1">
                                            <div class="order-history-number fw-bold text-brand-black mb-1">Order <?php echo $order['order_number']; ?></div>
                                            <div class="order-history-meta text-muted small mb-2"><?php echo $order['date']; ?> • 1 Item</div>
                                            <div class="order-history-status">
                                                <?php if ($order['status'] === 'Processing'): ?>
                                                    <span class="text-warning fs-6 me-1">●</span><span class="small">Processing</span>
                                                <?php elseif ($order['status'] === 'Shipped'): ?>
                                                    <span class="text-info fs-6 me-1">●</span><span class="small">Shipped</span>
                                                <?php elseif ($order['status'] === 'Delivered'): ?>
                                                    <span class="text-success fs-6 me-1">●</span><span class="small">Delivered</span>
                                                <?php else: ?>
                                                    <span class="text-secondary fs-6 me-1">●</span><span class="small"><?php echo $order['status']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Price & Action -->
                                        <div class="order-history-right ms-auto text-end">
                                            <div class="order-history-price fw-bold text-brand-black mb-2">₱12,000</div>
                                            <a href="order-details.php?id=<?php echo urlencode($order['order_number']); ?>" class="order-history-link text-brand-black text-decoration-underline small d-inline-flex align-items-center gap-1">
                                                View Details
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="orders-empty">
                            <h2>NO ORDERS YET</h2>
                            <p>time to start the collection</p>
                            <a href="shop.php" class="btn btn-shop">Shop now</a>
                        </div>
                    <?php endif; ?>
                    
                    <hr class="mt-5">
                    <div class="mt-4">
                        <h5 class="fw-bold text-brand-black">order not appearing?</h5>
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
                                <a class="nav-link" href="#address-view" data-bs-toggle="pill">Address</a>
                                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Log out</a>
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
                                            <div class="settings-detail-value"><?php echo date('F d, Y', strtotime($user['birthdate'])); ?></div>
                                        </div>
                                        <div class="settings-detail-item">
                                            <div class="settings-detail-label">GENDER</div>
                                            <div class="settings-detail-value"><?php echo $user['gender']; ?></div>
                                        </div>
                                        <a href="#" class="settings-edit-link" data-bs-toggle="modal" data-bs-target="#editPersonalModal">EDIT</a>
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
                                        <a href="#" class="settings-edit-link" data-bs-toggle="modal" data-bs-target="#editSecurityModal">EDIT</a>
                                    </div>

                                    <!-- Manage Account -->
                                    <div class="settings-section">
                                        <h3 class="settings-section-title">Manage Account</h3>
                                        <button type="button" class="btn btn-delete-account" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Delete Account</button>
                                    </div>
                                </div>

                                <!-- Address View -->
                                <div class="tab-pane fade" id="address-view">
                                    <h1 class="settings-content-title">MY ADRESS</h1>
                                    <p class="settings-subtitle">Please add your address for easier shopping</p>

                                    <div class="address-card-wrapper">
                                        <button type="button" class="address-add-card" data-bs-toggle="modal" data-bs-target="#addressModal">
                                            <i class="bi bi-plus-circle"></i>
                                            <div class="address-card-label">ADDRESS 1</div>
                                        </button>
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

    <!-- Edit Personal Information Modal -->
    <div class="modal fade" id="editPersonalModal" tabindex="-1" aria-labelledby="editPersonalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="editPersonalModalLabel">Edit Personal Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <form id="personalInfoForm">
                        <div class="mb-4">
                            <label for="fullName" class="form-label text-muted small fw-bold text-uppercase mb-1">Full Name</label>
                            <input type="text" class="form-control form-control-lg" id="fullName" value="<?php echo $user['name']; ?>">
                        </div>
                        <div class="mb-4">
                            <label for="birthdate" class="form-label text-muted small fw-bold text-uppercase mb-1">Date of Birth</label>
                            <input type="date" class="form-control form-control-lg" id="birthdate" value="<?php echo $user['birthdate']; ?>">
                        </div>
                        <div class="mb-4">
                            <label for="gender" class="form-label text-muted small fw-bold text-uppercase mb-1">Gender</label>
                            <select class="form-select form-select-lg" id="gender">
                                <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                <option value="Prefer not to say">Prefer not to say</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="personalInfoForm" class="btn px-4" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Security Modal -->
    <div class="modal fade" id="editSecurityModal" tabindex="-1" aria-labelledby="editSecurityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="editSecurityModalLabel">Account Security</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <form id="securityForm">
                        <div class="mb-4">
                            <label for="emailAddress" class="form-label text-muted small fw-bold text-uppercase mb-1">Email Address</label>
                            <input type="email" class="form-control form-control-lg" id="emailAddress" value="<?php echo $user['email']; ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                            <small class="text-muted">Email cannot be changed. Contact support if needed.</small>
                        </div>
                        <div class="mb-4">
                            <label for="currentPassword" class="form-label text-muted small fw-bold text-uppercase mb-1">Current Password</label>
                            <input type="password" class="form-control form-control-lg" id="currentPassword" placeholder="Enter current password">
                        </div>
                        <div class="mb-4">
                            <label for="newPassword" class="form-label text-muted small fw-bold text-uppercase mb-1">New Password</label>
                            <input type="password" class="form-control form-control-lg" id="newPassword" placeholder="Enter new password">
                        </div>
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label text-muted small fw-bold text-uppercase mb-1">Confirm New Password</label>
                            <input type="password" class="form-control form-control-lg" id="confirmPassword" placeholder="Confirm new password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="securityForm" class="btn px-4" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Update Password</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Address Management Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="addressModalLabel">Add Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <form id="addressForm">
                        <div class="row g-3">
                            <!-- First Name -->
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="First Name" style="height: 60px;">
                            </div>
                            
                            <!-- Last name -->
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Last name" style="height: 60px;">
                            </div>
                            
                            <!-- Adress Line 1 -->
                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="Adress Line 1" style="height: 60px;">
                            </div>
                            
                            <!-- Adress Line 2 -->
                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="Adress Line 2" style="height: 60px;">
                            </div>
                            
                            <!-- Province/State -->
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Province/State" style="height: 60px;">
                            </div>
                            
                            <!-- City/Municipality -->
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="City/Municipality" style="height: 60px;">
                            </div>
                            
                            <!-- Postal Code -->
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Postal Code" style="height: 60px;">
                            </div>
                            
                            <!-- Barangay/District -->
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Barangay/District" style="height: 60px;">
                            </div>
                            
                            <!-- Country -->
                            <div class="col-12">
                                <input type="text" class="form-control" placeholder="Country" style="height: 60px;">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addressForm" class="btn px-4" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="deleteAccountModalLabel">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <p class="mb-0 text-brand-black">Are you sure you want to permanently delete your SoleSource account? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn px-4" style="background: #dc3545; color: #fff; font-weight: 600;">Delete Account</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-uppercase text-brand-black" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <p class="mb-0 text-brand-black">Are you sure you want to log out of your SoleSource account?</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4 text-brand-black" data-bs-dismiss="modal">Cancel</button>
                    <a href="logout.php" class="btn px-4" style="background: var(--brand-orange); color: #fff; font-weight: 600;">Yes, Log Out</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Wishlist remove functionality
        document.querySelectorAll('.wishlist-remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.dataset.productId;
                const card = this.closest('.col');
                
                // Add fade out animation
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                
                // Remove from DOM after animation
                setTimeout(() => {
                    card.remove();
                    // Here you would also make an API call to remove from wishlist
                    // fetch('includes/wishlist-remove.php', { method: 'POST', body: JSON.stringify({ id: productId }) })
                }, 300);
            });
        });

        // Add to cart from wishlist
        document.querySelectorAll('.btn-add-to-cart').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                // Add your add to cart logic here
                console.log('Adding product to cart:', productId);
                // You can integrate with your existing cart system
            });
        });
    </script>
</body>
</html>
