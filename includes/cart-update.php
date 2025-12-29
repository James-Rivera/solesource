<?php
session_start();
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['id']) || !isset($input['size']) || !isset($input['qty'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
    exit;
}
$id = (string)$input['id'];
$size = (string)$input['size'];
$qty = (int)$input['qty'];
$key = $id . ':' . $size;
if (isset($_SESSION['cart'][$key])) {
    if ($qty <= 0) {
        unset($_SESSION['cart'][$key]);
    } else {
        $_SESSION['cart'][$key]['qty'] = $qty;
    }
}
$cart = isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : [];
$subtotal = array_reduce($cart, function($carry, $item) {
    return $carry + ($item['price'] * $item['qty']);
}, 0);
echo json_encode(['ok' => true, 'cart' => $cart, 'subtotal' => $subtotal]);
