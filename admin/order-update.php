<?php
session_start();
require_once '../includes/connect.php';
require_once '../includes/mailer.php';
require_once __DIR__ . '/../includes/admin-logs.php';

// Admin only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php?msg=invalid');
    exit;
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$newStatus = strtolower(trim($_POST['status'] ?? ''));
$trackingInput = trim($_POST['tracking_number'] ?? '');
$courierInput = trim($_POST['courier'] ?? '');

$allowed = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
if ($orderId <= 0 || !in_array($newStatus, $allowed, true)) {
    header('Location: orders.php?msg=invalid');
    exit;
}

// Fetch current order
$sel = $conn->prepare("SELECT o.id, o.status, o.tracking_number, o.courier, o.order_number, o.user_id, o.total_amount, u.email, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ? LIMIT 1");
$sel->bind_param('i', $orderId);
$sel->execute();
$res = $sel->get_result();
$order = $res ? $res->fetch_assoc() : null;
$sel->close();

if (!$order) {
    header('Location: orders.php?msg=notfound');
    exit;
}

$currentTracking = $order['tracking_number'] ?? '';
$currentCourier = $order['courier'] ?? '';

$newTracking = $trackingInput !== '' ? $trackingInput : $currentTracking;
$newCourier = $courierInput !== '' ? $courierInput : ($currentCourier ?: 'MockExpress');

if ($newStatus === 'shipped' && $newTracking === '') {
    $newTracking = 'MOCK-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

try {
    $conn->begin_transaction();

    $upd = $conn->prepare("UPDATE orders SET status = ?, tracking_number = ?, courier = ? WHERE id = ?");
    $upd->bind_param('sssi', $newStatus, $newTracking, $newCourier, $orderId);
    if (!$upd->execute()) {
        throw new Exception('update failed');
    }
    $upd->close();

    if ($newStatus === 'cancelled' && strtolower($order['status']) !== 'cancelled') {
        $items = $conn->prepare("SELECT product_size_id, quantity FROM order_items WHERE order_id = ? AND product_size_id IS NOT NULL");
        $items->bind_param('i', $orderId);
        $items->execute();
        $resItems = $items->get_result();
        while ($row = $resItems->fetch_assoc()) {
            $psid = (int) ($row['product_size_id'] ?? 0);
            $qty = (int) ($row['quantity'] ?? 0);
            if ($psid > 0 && $qty > 0) {
                $restock = $conn->prepare("UPDATE product_sizes SET stock_quantity = stock_quantity + ? WHERE id = ?");
                $restock->bind_param('ii', $qty, $psid);
                $restock->execute();
                $restock->close();
            }
        }
        $items->close();
    }

    $conn->commit();
    // If status changed to shipped, queue an email notification to customer
    if ($newStatus === 'shipped' && strtolower($order['status']) !== 'shipped') {
        try {
            $envAppUrl = getenv('APP_URL') ?: ($_SERVER['APP_URL'] ?? '');
            $baseUrl = $envAppUrl ? rtrim($envAppUrl, '/') : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $orderViewUrl = $baseUrl . '/index.php?page=view_order&id=' . urlencode($orderId);

            $recipient = $order['email'] ?? '';
            $customerName = $order['full_name'] ?? '';
            $subject = 'Your SoleSource order ' . ($order['order_number'] ?? '') . ' is on the way';
            $html = '<div style="font-family:Arial,sans-serif;color:#121212;max-width:600px;margin:12px auto;padding:18px;border:1px solid #efefef;border-radius:6px;">'
                . '<h2 style="color:#121212;">Your order is on the way</h2>'
                . '<p>Hi ' . htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') . ',</p>'
                . '<p>Your order <strong>' . htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8') . '</strong> has been marked as <strong>Shipping</strong>.</p>'
                . '<p>Courier: ' . htmlspecialchars($newCourier, ENT_QUOTES, 'UTF-8') . '<br/>Tracking number: ' . htmlspecialchars($newTracking, ENT_QUOTES, 'UTF-8') . '</p>'
                . '<p><a href="' . htmlspecialchars($orderViewUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:10px 14px;background:#E9713F;color:#fff;text-decoration:none;border-radius:4px;">View your order</a></p>'
                . '<p style="color:#6f6f6f;font-size:13px;">If you have questions, reply to this email or contact support.</p>'
                . '</div>';
            $alt = 'Your order ' . ($order['order_number'] ?? '') . ' is shipping. Tracking: ' . $newTracking . ' via ' . $newCourier . '. View: ' . $orderViewUrl;

            if ($recipient) {
                queueEmail($conn, $recipient, $subject, $html, $alt);
            }
        } catch (Throwable $e) {
            error_log('Failed to queue shipped email for order ' . $orderId . ': ' . $e->getMessage());
        }
    }

        // Audit log: record status change
        try {
            $adminId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
            $meta = [
                'order_id' => $orderId,
                'order_number' => $order['order_number'] ?? null,
                'old_status' => $order['status'] ?? null,
                'new_status' => $newStatus,
                'tracking' => $newTracking,
                'courier' => $newCourier,
            ];
            log_admin_action($conn, $adminId, 'order_status_change', $meta);
        } catch (Throwable $e) {
            error_log('Failed to write admin log for order ' . $orderId . ': ' . $e->getMessage());
        }

    header('Location: orders.php?msg=updated');
    exit;
} catch (Throwable $t) {
    $conn->rollback();
    header('Location: orders.php?msg=error');
    exit;
}
