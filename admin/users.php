<?php
session_start();
require_once '../includes/connect.php';

// Security gate: admins only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$users = [];
$stmt = $conn->prepare("SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['created_formatted'] = $row['created_at'] ? date('M d, Y', strtotime($row['created_at'])) : '';
        $users[] = $row;
    }
    $stmt->close();
}
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
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="admin-page-title">Customers</h1>
                    <p class="admin-page-subtitle">All registered users</p>
                </div>
            </div>

            <div class="products-table-container">
                <div class="product-table-row product-table-header">
                    <div>ID</div>
                    <div>Name</div>
                    <div>Email</div>
                    <div>Role</div>
                    <div>Status</div>
                    <div>Joined</div>
                </div>
                <?php foreach ($users as $user): ?>
                    <div class="product-table-row">
                        <div data-label="ID"><?php echo (int) $user['id']; ?></div>
                        <div data-label="Name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        <div data-label="Email"><?php echo htmlspecialchars($user['email']); ?></div>
                        <div data-label="Role">
                            <?php $role = strtolower($user['role'] ?? 'customer'); ?>
                            <span class="pill pill-sm <?php echo $role === 'admin' ? 'pill-dark' : 'pill-neutral'; ?>"><?php echo htmlspecialchars(strtoupper($user['role'])); ?></span>
                        </div>
                        <div data-label="Status">
                            <?php $active = (int) ($user['is_active'] ?? 0) === 1; ?>
                            <span class="pill pill-sm <?php echo $active ? 'pill-active' : 'pill-inactive'; ?>"><?php echo $active ? 'Active' : 'Inactive'; ?></span>
                        </div>
                        <div data-label="Joined"><?php echo htmlspecialchars($user['created_formatted']); ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <div class="p-4 text-center text-muted">No users found.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
