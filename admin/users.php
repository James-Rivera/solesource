<?php
session_start();

// Mock customer data sorted by lifetime spend
$customers = [
    ['id' => 1, 'name' => 'Juan Dela Cruz', 'spent' => '₱45,200.00', 'last_order' => 'Dec 30, 2025', 'order_count' => 8],
    ['id' => 2, 'name' => 'Maria Santos', 'spent' => '₱38,900.00', 'last_order' => 'Dec 28, 2025', 'order_count' => 6],
    ['id' => 3, 'name' => 'Pedro Garcia', 'spent' => '₱32,150.00', 'last_order' => 'Dec 25, 2025', 'order_count' => 5],
    ['id' => 4, 'name' => 'Ana Reyes', 'spent' => '₱28,700.00', 'last_order' => 'Dec 20, 2025', 'order_count' => 4],
    ['id' => 5, 'name' => 'Carlos Mendoza', 'spent' => '₱22,450.00', 'last_order' => 'Dec 15, 2025', 'order_count' => 3],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <style>
        /* Reuse base styles */
        body {
            font-family: var(--brand-font);
            background: #f6f6f6;
            color: var(--brand-black);
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 280px;
            background: var(--brand-dark-gray);
            color: #fff;
            padding: 2rem 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .admin-logo {
            padding: 0 2rem;
            margin-bottom: 3rem;
        }

        .admin-logo img {
            height: 32px;
            filter: brightness(0) invert(1);
        }

        .admin-sidebar-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--brand-orange);
            padding: 0 2rem;
            margin-bottom: 1.5rem;
        }

        .admin-nav {
            display: flex;
            flex-direction: column;
        }

        .admin-nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 0.875rem 2rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.2s ease;
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
            border: none;
            background: transparent;
        }

        .admin-nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 2rem;
            right: 2rem;
            height: 2px;
            background-color: var(--brand-orange);
            width: 0;
            transition: width 0.3s ease;
        }

        .admin-nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-nav-link:hover::after {
            width: calc(100% - 4rem);
        }

        .admin-nav-link.active {
            color: #fff;
            font-weight: 700;
            background: rgba(233, 113, 63, 0.1);
        }

        .admin-nav-link.active::after {
            width: calc(100% - 4rem);
        }

        .admin-nav-link.logout {
            color: #ff6b6b;
            margin-top: auto;
        }

        .admin-nav-link.logout:hover {
            color: #ff5252;
            background: rgba(255, 107, 107, 0.1);
        }

        .admin-content {
            flex: 1;
            margin-left: 280px;
            padding: 3rem 2rem;
        }

        .admin-header {
            margin-bottom: 3rem;
        }

        .admin-page-title {
            font-size: 2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
            letter-spacing: 1px;
        }

        .admin-page-subtitle {
            color: #666;
            font-size: 1rem;
            margin-top: 0.5rem;
        }

        /* Customers Table */
        .customers-table-container {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
        }

        .customer-table-row {
            display: grid;
            grid-template-columns: 2fr 180px 180px 140px;
            gap: 1.5rem;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e5e5;
            align-items: center;
            transition: background-color 0.2s ease;
        }

        .customer-table-row:last-child {
            border-bottom: none;
        }

        .customer-table-row:hover {
            background-color: #fafafa;
        }

        .customer-table-header {
            background: #f9f9f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #666;
            letter-spacing: 1px;
        }

        .customer-table-header:hover {
            background: #f9f9f9;
        }

        .customer-name-cell {
            font-weight: 600;
            color: var(--brand-black);
            font-size: 1rem;
        }

        .spent-cell {
            font-weight: 700;
            color: var(--brand-black);
            font-size: 1.05rem;
        }

        .last-order-cell {
            font-size: 0.875rem;
            color: #666;
        }

        .history-link {
            color: var(--brand-black);
            text-decoration: underline;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .history-link:hover {
            opacity: 0.7;
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .admin-content {
                margin-left: 0;
            }

            .customer-table-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .customer-table-header {
                display: none;
            }

            .customer-table-row > div:before {
                content: attr(data-label);
                font-weight: 700;
                text-transform: uppercase;
                font-size: 0.75rem;
                color: #666;
                display: block;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1 class="admin-page-title">VIP List</h1>
                <p class="admin-page-subtitle">Your highest-value customers, sorted by lifetime spend</p>
            </div>

            <div class="customers-table-container">
                <!-- Table Header -->
                <div class="customer-table-row customer-table-header">
                    <div>Customer</div>
                    <div>Lifetime Spend</div>
                    <div>Last Order</div>
                    <div>History</div>
                </div>

                <!-- Customer Rows -->
                <?php foreach ($customers as $customer): ?>
                    <div class="customer-table-row">
                        <div class="customer-name-cell" data-label="Customer">
                            <?php echo $customer['name']; ?>
                        </div>
                        <div class="spent-cell" data-label="Lifetime Spend">
                            <?php echo $customer['spent']; ?>
                        </div>
                        <div class="last-order-cell" data-label="Last Order">
                            <?php echo $customer['last_order']; ?>
                        </div>
                        <div data-label="History">
                            <a href="customer-orders.php?id=<?php echo $customer['id']; ?>" class="history-link">
                                View <?php echo $customer['order_count']; ?> Orders
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
