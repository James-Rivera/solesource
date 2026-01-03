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
$addressId = isset($input['id']) ? (int) $input['id'] : 0;

if ($addressId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Address id is required.']);
    exit;
}

// Ensure address belongs to user
$check = $conn->prepare('SELECT id, is_default FROM user_addresses WHERE id = ? AND user_id = ? LIMIT 1');
$check->bind_param('ii', $addressId, $userId);
$check->execute();
$result = $check->get_result();
$existing = $result ? $result->fetch_assoc() : null;
$check->close();

if (!$existing) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Address not found.']);
    exit;
}

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

$stmt = $conn->prepare('UPDATE user_addresses SET label = ?, full_name = ?, phone = ?, address_line = ?, city = ?, province = ?, region = ?, barangay = ?, zip_code = ?, country = ?, is_default = ? WHERE id = ? AND user_id = ?');
$stmt->bind_param('ssssssssssiii', $label, $fullName, $phone, $addressLine, $city, $province, $region, $barangay, $zipCode, $country, $isDefault, $addressId, $userId);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true]);
