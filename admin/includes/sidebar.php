<aside class="admin-sidebar">
    <nav class="admin-nav">
        <a href="index.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
            <i class="bi bi-house-door-fill nav-icon"></i>
            <span>Home</span>
        </a>
        <a href="products.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">
            <i class="bi bi-box2-fill nav-icon"></i>
            <span>Products</span>
        </a>
        <a href="orders.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : ''; ?>">
            <i class="bi bi-receipt-cutoff nav-icon"></i>
            <span>Orders</span>
        </a>
        <a href="users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
            <i class="bi bi-people-fill nav-icon"></i>
            <span>Customers</span>
        </a>
        <a href="settings.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
            <i class="bi bi-gear-fill nav-icon"></i>
            <span>Settings</span>
        </a>
        <a href="sms-log.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'sms-log.php' ? 'active' : ''; ?>">
            <i class="bi bi-chat-dots-fill nav-icon"></i>
            <span>SMS Log</span>
        </a>
    </nav>
</aside>
