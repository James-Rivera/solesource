<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT id, label, full_name, phone, address_line, city, province, region, barangay, zip_code, country, is_default, created_at FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$addresses = [];
while ($row = $res->fetch_assoc()) {
    $row['id'] = (int) $row['id'];
    $row['is_default'] = (int) $row['is_default'];
    $addresses[] = $row;
}
$stmt->close();

echo json_encode([
    'ok' => true,
    'data' => $addresses,
]);
