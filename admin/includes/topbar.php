<header class="admin-topbar">
    <div class="topbar-left">
        <button class="icon-button ghost d-none d-md-inline" type="button" aria-label="Toggle sidebar" data-toggle-sidebar>
            <i class="bi bi-layout-sidebar"></i>
        </button>
        <div class="topbar-brand" aria-label="SoleSource Admin">
            <img src="../assets/img/logo-big.png" alt="SoleSource">
        </div>
    </div>
    <div class="topbar-actions">
        <a class="topbar-link d-none d-md-inline" href="../index.php" target="_blank" rel="noopener">View Store</a>
        <a class="topbar-link d-none d-md-inline" href="orders.php">Orders</a>
        <a class="topbar-link d-none d-md-inline" href="products.php">Products</a>
        <a class="btn-top primary d-none d-md-inline" href="products.php#addProductForm">Create Product</a>

        <!-- Mobile menu button: opens offcanvas mirror of topbar links -->
        <button class="btn icon-button ghost d-md-none mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminTopbarDrawer" aria-controls="adminTopbarDrawer" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="dropdown">
            <button class="btn-top" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu">
                <i class="bi bi-person-circle"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                <li><a class="dropdown-item" href="../logout.php">Log out</a></li>
            </ul>
        </div>
    </div>

    <!-- Offcanvas: Admin topbar mobile menu -->
    <div class="offcanvas offcanvas-top admin-topbar-drawer" tabindex="-1" id="adminTopbarDrawer" aria-labelledby="adminTopbarDrawerLabel">
        <div class="offcanvas-header">
            <h5 id="adminTopbarDrawerLabel">Menu</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-2">
            <?php // mirror the admin sidebar links for mobile offcanvas ?>
            <a class="offcanvas-link" href="index.php"><i class="bi bi-house-door-fill me-2"></i>Home</a>
            <a class="offcanvas-link" href="products.php"><i class="bi bi-box2-fill me-2"></i>Products</a>
            <a class="offcanvas-link" href="orders.php"><i class="bi bi-receipt-cutoff me-2"></i>Orders</a>
            <a class="offcanvas-link" href="users.php"><i class="bi bi-people-fill me-2"></i>Customers</a>
            <a class="offcanvas-link" href="settings.php"><i class="bi bi-gear-fill me-2"></i>Settings</a>
            <a class="offcanvas-link" href="sms-log.php"><i class="bi bi-chat-dots-fill me-2"></i>SMS Log</a>
            <div class="offcanvas-divider"></div>
            <a class="offcanvas-link btn btn-primary text-white d-flex align-items-center" href="products.php#addProductForm"><i class="bi bi-plus-circle me-2"></i>Create Product</a>
            <div class="offcanvas-divider"></div>
            <a class="offcanvas-link" href="../index.php" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-2"></i>View Store</a>
            <a class="offcanvas-link" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Log out</a>
        </div>
    </div>
</header>
