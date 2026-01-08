<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('DELETE FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$success = $stmt->execute();
$stmt->close();

if (!$success || $conn->affected_rows < 1) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to delete account.']);
    exit;
}

session_unset();
session_destroy();

echo json_encode(['ok' => true]);
