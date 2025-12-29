<?php
session_start();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['id']) || !isset($input['size'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
    exit;
}

$id = (string)$input['id'];
$size = (string)$input['size'];
$key = $id . ':' . $size;
$name = $input['name'] ?? '';
$brand = $input['brand'] ?? '';
$image = $input['image'] ?? '';
$price = isset($input['price']) ? (float)$input['price'] : 0;
$qty = isset($input['qty']) ? max(1, (int)$input['qty']) : 1;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key]['qty'] += $qty;
} else {
    $_SESSION['cart'][$key] = [
        'id' => $id,
        'size' => $size,
        'name' => $name,
        'brand' => $brand,
        'image' => $image,
        'price' => $price,
        'qty' => $qty,
    ];
}

$cart = array_values($_SESSION['cart']);
$subtotal = array_reduce($cart, function($carry, $item) {
    return $carry + ($item['price'] * $item['qty']);
}, 0);

echo json_encode(['ok' => true, 'cart' => $cart, 'subtotal' => $subtotal]);
