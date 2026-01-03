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
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$label = trim($input['label'] ?? '');
$fullName = trim($input['full_name'] ?? '');
$phone = trim($input['phone'] ?? '');
$addressLine = trim($input['address_line'] ?? '');
$city = trim($input['city'] ?? '');
$province = trim($input['province'] ?? '');
$region = trim($input['region'] ?? '');
$barangay = trim($input['barangay'] ?? '');
$zipCode = trim($input['zip_code'] ?? '');
$country = trim($input['country'] ?? 'Philippines');
$isDefault = !empty($input['is_default']) ? 1 : 0;

$missing = [];
foreach ([
    'full_name' => $fullName,
    'phone' => $phone,
    'address_line' => $addressLine,
    'city' => $city,
    'province' => $province,
    'region' => $region,
    'barangay' => $barangay,
    'zip_code' => $zipCode,
] as $field => $value) {
    if ($value === '') {
        $missing[] = $field;
    }
}

if ($missing) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Missing required fields', 'fields' => $missing]);
    exit;
}

if ($isDefault) {
    $reset = $conn->prepare('UPDATE user_addresses SET is_default = 0 WHERE user_id = ?');
    $reset->bind_param('i', $userId);
    $reset->execute();
    $reset->close();
}

$stmt = $conn->prepare('INSERT INTO user_addresses (user_id, label, full_name, phone, address_line, city, province, region, barangay, zip_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('issssssssssi', $userId, $label, $fullName, $phone, $addressLine, $city, $province, $region, $barangay, $zipCode, $country, $isDefault);
$stmt->execute();
$newId = $stmt->insert_id;
$stmt->close();

http_response_code(201);
echo json_encode([
    'ok' => true,
    'id' => $newId,
]);
