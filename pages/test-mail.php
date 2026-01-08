<?php
require __DIR__ . '/../includes/mailer.php';
require __DIR__ . '/../includes/receipt_email.php';

// Use the shared receipt builder to validate styling and inline images
$to = getenv('MAIL_TEST_TO') ?: getenv('MAIL_FROM_EMAIL') ?: 'constantinodyu11@gmail.com';
$sampleCart = [
    ['name' => 'Test Sneaker A', 'size' => 'US 9', 'qty' => 1, 'line_total' => 1234.00, 'image' => 'https://via.placeholder.com/600x400.png?text=Product+A'],
    ['name' => 'Test Sneaker B', 'size' => 'US 10', 'qty' => 2, 'line_total' => 2345.00, 'image' => 'https://via.placeholder.com/600x400.png?text=Product+B'],
];

$emailData = build_receipt_email([
    'orderId' => 999,
    'orderNumber' => 'SO-TEST-1234',
    'fullName' => 'Test User',
    'paymentMethod' => 'COD',
    'shippingAddress' => "123 Test St, Test City\nPH 1000",
    'cartItems' => $sampleCart,
    'totalAmount' => 1234.00 + 2345.00,
]);

$result = sendEmail(
    $to,
    $emailData['subject'],
    $emailData['html'],
    $emailData['alt'],
    $emailData['embedded']
);

if ($result === true) {
    echo "Mail sent to {$to}\n";
} else {
    echo "Mail failed: {$result}\n";
}
