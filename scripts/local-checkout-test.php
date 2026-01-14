<?php
require __DIR__ . '/../includes/orders/receipt-email.php';

$data = [
    'orderId' => 12345,
    'orderNumber' => 'SO-LOCAL-TEST-0002',
    'orderDate' => date('F j, Y'),
    'fullName' => 'Local Tester',
    'paymentMethod' => 'COD',
    'shippingAddress' => "123 Local Rd\nLocal City, PH 1000",
    'cartItems' => [
        ['name' => 'Mock Shoe', 'size' => 'US 9', 'qty' => 1, 'line_total' => 1999.00, 'image' => 'https://via.placeholder.com/600x400.png?text=Mock+Shoe'],
        ['name' => 'Mock Shoe 2', 'size' => 'US 10', 'qty' => 1, 'line_total' => 2999.00, 'image' => 'https://via.placeholder.com/600x400.png?text=Mock+Shoe+2'],
    ],
    'totalAmount' => 1999.00 + 2999.00 - 150.00,
    'subtotalAmount' => 1999.00 + 2999.00,
    'voucherCode' => 'TEST150',
    'voucherDiscount' => 150.00,
];

$emailData = build_receipt_email($data);
$outFile = __DIR__ . '/../logs/receipt_test_checkout.html';
file_put_contents($outFile, $emailData['html']);
file_put_contents(__DIR__ . '/../logs/receipt_test_checkout_alt.txt', $emailData['alt']);

echo "Wrote HTML to: $outFile\n";
echo "Wrote alt text to: " . __DIR__ . "/../logs/receipt_test_checkout_alt.txt\n";
