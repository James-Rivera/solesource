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

$check = $conn->prepare('SELECT id, is_default FROM user_addresses WHERE id = ? AND user_id = ? LIMIT 1');
$check->bind_param('ii', $addressId, $userId);
$check->execute();
$res = $check->get_result();
$existing = $res ? $res->fetch_assoc() : null;
$check->close();

if (!$existing) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Address not found.']);
    exit;
}

$del = $conn->prepare('DELETE FROM user_addresses WHERE id = ? AND user_id = ?');
$del->bind_param('ii', $addressId, $userId);
$del->execute();
$del->close();

if ((int) $existing['is_default'] === 1) {
    $nextStmt = $conn->prepare('SELECT id FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
    $nextStmt->bind_param('i', $userId);
    $nextStmt->execute();
    $nextRes = $nextStmt->get_result();
    $next = $nextRes ? $nextRes->fetch_assoc() : null;
    $nextStmt->close();

    if ($next) {
        $promote = $conn->prepare('UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?');
        $promote->bind_param('ii', $next['id'], $userId);
        $promote->execute();
        $promote->close();
    }
}

echo json_encode(['ok' => true]);
