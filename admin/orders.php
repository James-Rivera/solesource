<?php
session_start();
require_once '../includes/connect.php';

// Security gate: admins only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$filter = isset($_GET['filter']) ? strtolower($_GET['filter']) : 'all';
$search = trim($_GET['q'] ?? '');
$allowedFilters = ['all', 'pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$orders = [];
$sql = "SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at, o.tracking_number, o.courier, o.payment_method, u.full_name, u.email, COALESCE(SUM(oi.quantity), 0) AS item_count FROM orders o JOIN users u ON u.id = o.user_id LEFT JOIN order_items oi ON oi.order_id = o.id";
$params = [];
$types = '';
$where = [];

if ($filter !== 'all') {
    $where[] = 'o.status = ?';
    $params[] = $filter;
    $types .= 's';
}

if ($search !== '') {
    $where[] = '(o.order_number LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $row['total_formatted'] = '₱' . number_format((float) ($row['total_amount'] ?? 0), 2, '.', ',');
        $row['date_formatted'] = $row['created_at'] ? date('M d, Y', strtotime($row['created_at'])) : '';
        $orders[] = $row;
    }
    $stmt->close();
}

$msg = $_GET['msg'] ?? '';
$toastMessage = '';
$toastVariant = 'primary';
if ($msg === 'updated') {
    $toastMessage = 'Order updated successfully.';
    $toastVariant = 'success';
} elseif ($msg === 'invalid') {
    $toastMessage = 'Invalid request.';
    $toastVariant = 'danger';
} elseif ($msg === 'notfound') {
    $toastMessage = 'Order not found.';
    $toastVariant = 'warning';
} elseif ($msg === 'error') {
    $toastMessage = 'Update failed. Please try again.';
    $toastVariant = 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SoleSource | Orders</title>
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
                    <h1 class="admin-page-title">Orders</h1>
                    <p class="admin-page-subtitle">Track payments, fulfillment, and bulk updates</p>
                </div>

                <div class="orders-toolbar-actions">
                    <button type="button" class="admin-action-btn" data-export-orders>
                        <i class="bi bi-download"></i>
                        <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <div class="orders-toolbar">
                <div class="orders-tabs">
                    <a href="orders.php" class="orders-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                    <a href="orders.php?filter=pending" class="orders-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="orders.php?filter=confirmed" class="orders-tab <?php echo $filter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                    <a href="orders.php?filter=shipped" class="orders-tab <?php echo $filter === 'shipped' ? 'active' : ''; ?>">Shipped</a>
                    <a href="orders.php?filter=delivered" class="orders-tab <?php echo $filter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
                    <a href="orders.php?filter=cancelled" class="orders-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
                </div>
                <div class="orders-tab-meta">
                    <span class="pill pill-neutral pill-sm"><?php echo count($orders); ?> shown</span>
                </div>
            </div>

            <form class="orders-search-row" method="get" action="orders.php">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                <div class="orders-search-group">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" class="form-control" placeholder="Search orders or customers" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="orders-search-actions">
                    <?php if ($search !== ''): ?>
                        <a class="orders-search-clear" href="orders.php?filter=<?php echo urlencode($filter); ?>">Clear</a>
                    <?php endif; ?>
                    <button type="submit" class="admin-action-btn sm">
                        <i class="bi bi-funnel"></i>
                        <span>Apply</span>
                    </button>
                </div>
            </form>

            <div class="orders-bulk-bar">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="selectAllOrders" data-select-all>
                    <label class="form-check-label" for="selectAllOrders">Select all</label>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <select class="form-select form-select-sm" data-bulk-action>
                        <option value="">Bulk actions</option>
                        <option value="confirmed">Mark as Confirmed</option>
                        <option value="shipped">Mark as Shipped</option>
                        <option value="delivered">Mark as Delivered</option>
                        <option value="cancelled">Cancel Orders</option>
                    </select>
                    <button type="button" class="admin-action-btn sm" data-bulk-apply disabled>
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Apply</span>
                    </button>
                </div>
            </div>

            <?php if ($toastMessage): ?>
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 2000;">
                    <div id="statusToast" class="toast align-items-center text-bg-<?php echo htmlspecialchars($toastVariant); ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2600">
                        <div class="d-flex">
                            <div class="toast-body"><?php echo htmlspecialchars($toastMessage); ?></div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="orders-table-container">
                <!-- Table Header -->
                <div class="order-table-row order-table-header">
                    <div>
                        <input class="form-check-input" type="checkbox" value="1" aria-label="Select all" data-select-all-top>
                    </div>
                    <div>Order</div>
                    <div>Customer</div>
                    <div>Payment</div>
                    <div>Fulfillment</div>
                    <div>Total</div>
                    <div>Action</div>
                </div>

                <!-- Order Rows -->
                <?php foreach ($orders as $order): ?>
                    <?php
                        $status = strtolower($order['status'] ?? '');
                        $fulfillmentClass = in_array($status, ['shipped', 'delivered'], true) ? 'fulfillment-shipped' : 'fulfillment-pending';
                        if ($status === 'cancelled') {
                            $fulfillmentClass = 'fulfillment-pending';
                        }
                        $statusLabel = ucfirst($status);
                        $itemsLabel = ((int) ($order['item_count'] ?? 0)) . ' item' . (((int) ($order['item_count'] ?? 0)) === 1 ? '' : 's');
                    ?>
                    <div class="order-table-row" data-order-id="<?php echo (int) $order['id']; ?>">
                        <div data-label="Select">
                            <input class="form-check-input" type="checkbox" value="<?php echo (int) $order['id']; ?>" data-order-checkbox>
                        </div>
                        <div class="order-id-cell" data-label="Order">
                            <div class="fw-bold">#<?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="text-muted small"><?php echo htmlspecialchars($order['date_formatted']); ?> • <?php echo htmlspecialchars($itemsLabel); ?></div>
                        </div>
                        <div class="customer-cell" data-label="Customer">
                            <div class="customer-name"><?php echo htmlspecialchars($order['full_name']); ?></div>
                            <div class="customer-email"><?php echo htmlspecialchars($order['email']); ?></div>
                        </div>
                        <div class="payment-cell" data-label="Payment">
                            <span class="pill pill-sm pill-neutral"><?php echo htmlspecialchars(strtoupper($order['payment_method'] ?? 'COD')); ?></span>
                        </div>
                        <div class="fulfillment-cell" data-label="Fulfillment">
                            <span class="status-pill status-<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <div class="text-muted small">Tracking: <?php echo htmlspecialchars($order['tracking_number']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="total-cell text-end fw-bold" data-label="Total">
                            <?php echo htmlspecialchars($order['total_formatted']); ?>
                        </div>
                        <div data-label="Action" class="order-actions">
                            <div class="dropdown">
                                <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end action-dropdown-menu">
                                    <a class="dropdown-item" href="order-details.php?id=<?php echo urlencode($order['id']); ?>">View details</a>
                                    <?php if (in_array($status, ['pending', 'confirmed', 'shipped'], true)): ?>
                                        <?php if ($status === 'pending'): ?>
                                            <form method="POST" action="order-update.php" class="dropdown-item p-0">
                                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="dropdown-action">Mark Confirmed</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($status === 'confirmed'): ?>
                                            <form method="POST" action="order-update.php" class="dropdown-item p-0">
                                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                                <input type="hidden" name="status" value="shipped">
                                                <div class="dropdown-form-group">
                                                    <input type="text" name="tracking_number" class="form-control form-control-sm" placeholder="Tracking number (optional)" value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>">
                                                    <input type="text" name="courier" class="form-control form-control-sm" placeholder="Courier (optional)" value="<?php echo htmlspecialchars($order['courier'] ?? ''); ?>">
                                                </div>
                                                <button type="submit" class="dropdown-action">Mark Shipped</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($status === 'shipped'): ?>
                                            <form method="POST" action="order-update.php" class="dropdown-item p-0">
                                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                                <input type="hidden" name="status" value="delivered">
                                                <button type="submit" class="dropdown-action">Mark Delivered</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (in_array($status, ['pending', 'confirmed'], true)): ?>
                                            <form method="POST" action="order-update.php" class="dropdown-item p-0">
                                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="dropdown-action text-danger">Cancel Order</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($toastMessage): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastEl = document.getElementById('statusToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
    <?php endif; ?>
    <script>
        const ordersData = <?php echo json_encode($orders, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const layout = document.querySelector('.admin-container');
            const sidebarToggles = document.querySelectorAll('[data-toggle-sidebar]');
            const selectAllControls = document.querySelectorAll('[data-select-all], [data-select-all-top]');
            const orderCheckboxes = Array.from(document.querySelectorAll('[data-order-checkbox]'));
            const bulkAction = document.querySelector('[data-bulk-action]');
            const bulkApply = document.querySelector('[data-bulk-apply]');
            const exportBtn = document.querySelector('[data-export-orders]');

            sidebarToggles.forEach(btn => {
                btn.addEventListener('click', () => layout.classList.toggle('sidebar-collapsed'));
            });

            function selectedIds() {
                return orderCheckboxes.filter(cb => cb.checked).map(cb => cb.value);
            }

            function syncSelectAllState() {
                const allChecked = orderCheckboxes.length > 0 && orderCheckboxes.every(cb => cb.checked);
                selectAllControls.forEach(ctrl => { ctrl.checked = allChecked; });
                syncBulkState();
            }

            function syncBulkState() {
                if (!bulkApply) return;
                const hasSelection = selectedIds().length > 0;
                const hasAction = bulkAction && bulkAction.value !== '';
                bulkApply.disabled = !(hasSelection && hasAction);
            }

            selectAllControls.forEach(ctrl => {
                ctrl.addEventListener('change', () => {
                    orderCheckboxes.forEach(cb => { cb.checked = ctrl.checked; });
                    syncSelectAllState();
                });
            });

            orderCheckboxes.forEach(cb => cb.addEventListener('change', syncSelectAllState));
            if (bulkAction) { bulkAction.addEventListener('change', syncBulkState); }

            if (bulkApply) {
                bulkApply.addEventListener('click', async () => {
                    const action = bulkAction ? bulkAction.value : '';
                    const ids = selectedIds();
                    if (!action || ids.length === 0) return;

                    const confirmMessage = `Apply "${action}" to ${ids.length} order(s)?`;
                    if (!confirm(confirmMessage)) {
                        return;
                    }

                    bulkApply.disabled = true;
                    bulkApply.innerText = 'Applying...';

                    try {
                        await Promise.all(ids.map(id => {
                            const formData = new FormData();
                            formData.append('order_id', id);
                            formData.append('status', action);
                            return fetch('order-update.php', { method: 'POST', body: formData });
                        }));
                        window.location = 'orders.php?msg=updated';
                    } catch (err) {
                        console.error(err);
                        window.location = 'orders.php?msg=error';
                    }
                });
            }

            function toCsvValue(val) {
                const str = (val ?? '').toString().replace(/\s+/g, ' ').trim();
                if (str.includes(',') || str.includes('"')) {
                    return '"' + str.replace(/"/g, '""') + '"';
                }
                return str;
            }

            function exportCsv() {
                if (!ordersData || ordersData.length === 0) return;
                const header = ['Order', 'Date', 'Customer', 'Email', 'Payment', 'Status', 'Total', 'Items', 'Tracking'];
                const lines = [header.join(',')];
                ordersData.forEach(o => {
                    lines.push([
                        '#' + (o.order_number || ''),
                        o.date_formatted || '',
                        o.full_name || '',
                        o.email || '',
                        (o.payment_method || 'COD').toUpperCase(),
                        (o.status || '').toUpperCase(),
                        o.total_formatted || '',
                        o.item_count || 0,
                        o.tracking_number || ''
                    ].map(toCsvValue).join(','));
                });

                const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'orders.csv';
                link.click();
                URL.revokeObjectURL(link.href);
            }

            if (exportBtn) {
                exportBtn.addEventListener('click', exportCsv);
            }

            syncSelectAllState();
        });
    </script>
</body>
</html>
