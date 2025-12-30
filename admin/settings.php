<?php
session_start();

$last_backup = "December 29, 2025 at 3:45 PM";
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <style>
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

        /* Settings Sections */
        .settings-section {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--brand-black);
            letter-spacing: 0.5px;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e5e5;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border: 1px solid #d9d9d9;
            border-radius: 4px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--brand-black);
            box-shadow: 0 0 0 2px rgba(18, 18, 18, 0.1);
            outline: none;
        }

        .btn-save {
            background: var(--brand-black);
            color: #fff;
            padding: 0.75rem 2.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .btn-save:hover {
            background: #000;
        }

        /* Backup Section */
        .backup-info {
            background: #f9f9f9;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .backup-label {
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .backup-date {
            font-size: 1rem;
            color: var(--brand-black);
            font-weight: 600;
        }

        .btn-backup {
            background: transparent;
            color: var(--brand-black);
            padding: 0.875rem 2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid var(--brand-black);
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn-backup:hover {
            background: var(--brand-black);
            color: #fff;
        }

        .file-input-wrapper {
            position: relative;
            display: block;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .btn-restore {
            background: transparent;
            color: #666;
            padding: 0.875rem 2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid #d9d9d9;
            border-radius: 4px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            width: 100%;
            text-align: center;
            display: block;
        }

        .btn-restore:hover {
            border-color: var(--brand-black);
            color: var(--brand-black);
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 2rem;
            font-weight: 600;
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
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1 class="admin-page-title">System Control</h1>
            </div>

            <?php if ($message === 'backup_success'): ?>
                <div class="alert-success">
                    Backup downloaded successfully
                </div>
            <?php endif; ?>

            <!-- Store Configuration -->
            <div class="settings-section">
                <h2 class="section-title">Store Configuration</h2>
                
                <form method="POST" action="save-settings.php">
                    <div class="form-group">
                        <label for="storeName" class="form-label">Store Name</label>
                        <input type="text" class="form-control" id="storeName" name="store_name" value="SoleSource">
                    </div>

                    <div class="form-group">
                        <label for="supportEmail" class="form-label">Support Email</label>
                        <input type="email" class="form-control" id="supportEmail" name="support_email" value="support@solesource.com">
                    </div>

                    <div class="form-group">
                        <label for="currency" class="form-label">Currency</label>
                        <select class="form-control" id="currency" name="currency">
                            <option value="PHP" selected>PHP (₱)</option>
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-save">Save Changes</button>
                </form>
            </div>

            <!-- Database & Maintenance -->
            <div class="settings-section">
                <h2 class="section-title">Data Management</h2>
                
                <div class="backup-info">
                    <div class="backup-label">Last Backup</div>
                    <div class="backup-date"><?php echo $last_backup; ?></div>
                </div>

                <form method="POST" action="backup_logic.php">
                    <button type="submit" class="btn-backup">Download SQL Backup</button>
                </form>

                <form method="POST" action="restore_logic.php" enctype="multipart/form-data">
                    <div class="file-input-wrapper">
                        <input type="file" name="sql_file" accept=".sql" id="sqlFile" required>
                        <label for="sqlFile" class="btn-restore">Upload SQL to Restore</label>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
