<?php
session_start();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid payload']);
    exit;
}

$id = (string)$input['id'];
$size = (string)($input['size'] ?? ''); // size is optional now
$sizeIdRaw = $input['size_id'] ?? '';
$sizeId = $sizeIdRaw === '' ? null : (int)$sizeIdRaw;
$key = $id . ':' . ($sizeId !== null ? $sizeId : $size);
$legacyKey = $id . ':' . $size; // support older cart entries without size_id
$name = $input['name'] ?? '';
$brand = $input['brand'] ?? '';
$image = $input['image'] ?? '';
$price = isset($input['price']) ? (float)$input['price'] : 0;
$qty = isset($input['qty']) ? max(1, (int)$input['qty']) : 1;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// If a legacy key exists for the same product/size, migrate it to the size-aware key
if ($sizeId !== null && $legacyKey !== $key && isset($_SESSION['cart'][$legacyKey]) && !isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key] = $_SESSION['cart'][$legacyKey];
    $_SESSION['cart'][$key]['size_id'] = $sizeId;
    unset($_SESSION['cart'][$legacyKey]);
}

if (isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key]['qty'] += $qty;
    $_SESSION['cart'][$key]['size_id'] = $sizeId;
} else {
    $_SESSION['cart'][$key] = [
        'id' => $id,
        'size' => $size,
        'size_id' => $sizeId,
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
