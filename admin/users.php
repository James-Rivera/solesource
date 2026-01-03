<?php
session_start();
require_once '../includes/connect.php';

// Security gate: admins only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$flash = $_SESSION['admin_flash'] ?? '';
unset($_SESSION['admin_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

    if ($userId > 0) {
        $stmt = null;
        $successMessage = '';

        if (in_array($action, ['deactivate', 'demote'], true) && $userId === $currentUserId) {
            $_SESSION['admin_flash'] = 'You cannot deactivate or demote your own account.';
            header('Location: users.php');
            exit;
        }

        switch ($action) {
            case 'activate':
                $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                $successMessage = 'User activated.';
                break;
            case 'deactivate':
                $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
                $successMessage = 'User suspended.';
                break;
            case 'promote':
                $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
                $successMessage = 'User promoted to admin.';
                break;
            case 'demote':
                $stmt = $conn->prepare("UPDATE users SET role = 'customer' WHERE id = ?");
                $successMessage = 'User set to customer.';
                break;
            default:
                $_SESSION['admin_flash'] = 'Unknown action requested.';
                header('Location: users.php');
                exit;
        }

        if ($stmt) {
            $stmt->bind_param('i', $userId);
            if ($stmt->execute()) {
                $_SESSION['admin_flash'] = $successMessage;
            } else {
                $_SESSION['admin_flash'] = 'Unable to update user right now.';
            }
            $stmt->close();
        }
    } else {
        $_SESSION['admin_flash'] = 'Invalid user selected.';
    }

    header('Location: users.php');
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
    <?php include 'includes/topbar.php'; ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <h1 class="admin-page-title">Customers</h1>
                    <p class="admin-page-subtitle">All registered users</p>
                </div>
            </div>

            <?php if (!empty($flash)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($flash); ?>
                </div>
            <?php endif; ?>

            <div class="products-table-container">
                <div class="user-table-row user-table-header">
                    <div>ID</div>
                    <div>Name</div>
                    <div>Email</div>
                    <div>Role</div>
                    <div>Status</div>
                    <div>Joined</div>
                    <div>Actions</div>
                </div>
                <?php foreach ($users as $user): ?>
                    <?php
                        $isAdmin = strtolower($user['role'] ?? '') === 'admin';
                        $isActive = (int) ($user['is_active'] ?? 0) === 1;
                        $toggleActiveAction = $isActive ? 'deactivate' : 'activate';
                        $toggleActiveLabel = $isActive ? 'Suspend' : 'Activate';
                        $toggleActiveClass = $isActive ? 'danger' : 'success';
                        $roleAction = $isAdmin ? 'demote' : 'promote';
                        $roleLabel = $isAdmin ? 'Set Customer' : 'Make Admin';
                    ?>
                    <div class="user-table-row">
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
                        <div data-label="Actions">
                            <div class="user-actions">
                                <a class="admin-action-btn sm" href="orders.php?user=<?php echo urlencode($user['id']); ?>">
                                    <i class="bi bi-receipt"></i>
                                    <span>Orders</span>
                                </a>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo htmlspecialchars($toggleActiveAction); ?>">
                                    <button type="submit" class="admin-action-btn sm <?php echo $toggleActiveClass; ?>" data-confirm="<?php echo $isActive ? 'Suspend this user?' : 'Activate this user?'; ?>">
                                        <i class="bi <?php echo $isActive ? 'bi-pause-circle' : 'bi-play-circle'; ?>"></i>
                                        <span><?php echo $toggleActiveLabel; ?></span>
                                    </button>
                                </form>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo htmlspecialchars($roleAction); ?>">
                                    <button type="submit" class="admin-action-btn sm" data-confirm="<?php echo $isAdmin ? 'Set this admin back to customer?' : 'Promote this user to admin?'; ?>">
                                        <i class="bi <?php echo $isAdmin ? 'bi-person' : 'bi-person-gear'; ?>"></i>
                                        <span><?php echo $roleLabel; ?></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <div class="p-4 text-center text-muted">No users found.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const layout = document.querySelector('.admin-container');
            const toggles = document.querySelectorAll('[data-toggle-sidebar]');
            const confirmButtons = document.querySelectorAll('[data-confirm]');

            toggles.forEach(btn => {
                btn.addEventListener('click', () => {
                    layout.classList.toggle('sidebar-collapsed');
                });
            });

            confirmButtons.forEach(btn => {
                btn.addEventListener('click', (event) => {
                    const message = btn.getAttribute('data-confirm');
                    if (message && !confirm(message)) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
