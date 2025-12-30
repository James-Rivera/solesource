<aside class="admin-sidebar">
    <div class="admin-logo">
        <img src="../assets/svg/logo-big-white.svg" alt="SoleSource">
    </div>

    <div class="admin-sidebar-title">ADMIN PANEL</div>

    <nav class="admin-nav">
        <a href="index.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
            <span>DASHBOARD</span>
        </a>
        <a href="products.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">
            <span>PRODUCTS</span>
        </a>
        <a href="orders.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
            <span>ORDERS</span>
        </a>
        <a href="users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
            <span>CUSTOMERS</span>
        </a>
        <a href="settings.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
            <span>SETTINGS</span>
        </a>
        <div style="flex: 1;"></div>
        <a href="../logout.php" class="admin-nav-link logout">
            <span>LOG OUT</span>
        </a>
    </nav>
</aside>
