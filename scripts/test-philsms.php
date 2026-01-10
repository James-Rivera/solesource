<?php
require_once __DIR__ . '/../includes/env.php'; // <-- Add this line to load .env

// scripts/test-philsms.php: Test sending SMS via PHILsms API directly

$philsms_token = getenv('PHILSMS_TOKEN') ?: 'your-philsms-api-token';
$philsms_sender = getenv('PHILSMS_SENDER') ?: 'SoleSource';

$to = $argv[1] ?? '';
$message = $argv[2] ?? '';

if (!$to || !$message) {
    fwrite(STDERR, "Usage: php scripts/test-philsms.php <+639XXXXXXXXX> <message>\n");
    exit(1);
}

// Convert to PHILsms format (639XXXXXXXXX)
$recipient = ltrim($to, '+');
if (strpos($recipient, '09') === 0) {
    $recipient = '63' . substr($recipient, 1);
}

$payload = [
    'recipient' => $recipient,
    'sender_id' => $philsms_sender,
    'type' => 'plain',
    'message' => $message
];
$ch = curl_init('https://dashboard.philsms.com/api/v3/sms/send');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $philsms_token,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $httpCode\n";
echo $response . "\n";