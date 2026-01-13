<?php
session_start();
$cart = isset($_SESSION['cart']) ? array_values($_SESSION['cart']) : [];
$subtotal = array_reduce($cart, function($carry, $item) {
    return $carry + ($item['price'] * $item['qty']);
}, 0);
echo json_encode(['ok' => true, 'cart' => $cart, 'subtotal' => $subtotal]);
