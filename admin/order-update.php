<?php
session_start();
require_once '../includes/connect.php';

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
$sel = $conn->prepare("SELECT id, status, tracking_number, courier FROM orders WHERE id = ? LIMIT 1");
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
    header('Location: orders.php?msg=updated');
    exit;
} catch (Throwable $t) {
    $conn->rollback();
    header('Location: orders.php?msg=error');
    exit;
}
