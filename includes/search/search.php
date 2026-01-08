<?php
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
    echo json_encode(['results' => []]);
    exit;
}

$like = "%{$q}%";
$sql = "SELECT id, name, brand, image, price FROM products WHERE status = 'active' AND (name LIKE ? OR brand LIKE ?) ORDER BY release_date DESC, created_at DESC LIMIT 8";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$results = [];
while ($row = $res->fetch_assoc()) {
    $row['price'] = '₱' . number_format((float)$row['price'], 2, '.', ',');
    $results[] = $row;
}
$stmt->close();

echo json_encode(['results' => $results]);
