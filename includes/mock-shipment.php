<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderNumber = $input['order_number'] ?? ($_POST['order_number'] ?? '');
$courier = $input['courier'] ?? ($_POST['courier'] ?? 'MockExpress');
$tracking = $input['tracking_number'] ?? ($_POST['tracking_number'] ?? '');

if (!$orderNumber) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'order_number is required']);
    exit;
}

if ($tracking === '') {
    $tracking = 'MOCK-' . strtoupper(bin2hex(random_bytes(4)));
}

$stmt = $conn->prepare("UPDATE orders SET status = 'shipped', tracking_number = ?, courier = ? WHERE order_number = ? AND status IN ('pending', 'confirmed')");
$stmt->bind_param('sss', $tracking, $courier, $orderNumber);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected <= 0) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Order not found or already shipped/delivered/cancelled']);
    exit;
}

$order = null;
$fetch = $conn->prepare("SELECT id, order_number, status, tracking_number, courier FROM orders WHERE order_number = ? LIMIT 1");
$fetch->bind_param('s', $orderNumber);
$fetch->execute();
$res = $fetch->get_result();
$order = $res ? $res->fetch_assoc() : null;
$fetch->close();

echo json_encode(['ok' => true, 'order' => $order]);
