<?php
session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'auth_required']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$productId = isset($payload['product_id']) ? (int)$payload['product_id'] : 0;

if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_product']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Check if already in wishlist
$checkStmt = $conn->prepare('SELECT id FROM user_wishlist WHERE user_id = ? AND product_id = ? LIMIT 1');
$checkStmt->bind_param('ii', $userId, $productId);
$checkStmt->execute();
$checkRes = $checkStmt->get_result();
$existing = $checkRes && $checkRes->fetch_assoc();
$checkStmt->close();

if ($existing) {
    // Remove
    $delStmt = $conn->prepare('DELETE FROM user_wishlist WHERE user_id = ? AND product_id = ?');
    $delStmt->bind_param('ii', $userId, $productId);
    $delStmt->execute();
    $delStmt->close();
    echo json_encode(['ok' => true, 'action' => 'removed', 'product_id' => $productId]);
    exit;
}

// Add
$addStmt = $conn->prepare('INSERT INTO user_wishlist (user_id, product_id) VALUES (?, ?)');
$addStmt->bind_param('ii', $userId, $productId);
$addStmt->execute();
$addStmt->close();

echo json_encode(['ok' => true, 'action' => 'added', 'product_id' => $productId]);
