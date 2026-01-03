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

$upd = $conn->prepare("UPDATE orders SET status = ?, tracking_number = ?, courier = ? WHERE id = ?");
$upd->bind_param('sssi', $newStatus, $newTracking, $newCourier, $orderId);
$ok = $upd->execute();
$upd->close();

if (!$ok) {
    header('Location: orders.php?msg=error');
    exit;
}

header('Location: orders.php?msg=updated');
exit;
