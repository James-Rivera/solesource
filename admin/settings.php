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
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/topbar.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="admin-page-title">Settings</h1>
                    <p class="admin-page-subtitle">Manage admin preferences</p>
                </div>
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

                <form method="POST" action="backup-logic.php">
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
