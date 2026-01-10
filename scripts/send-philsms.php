<?php
// send-philsms.php: Send SMS using PHILsms API
require_once __DIR__ . '/../includes/env.php';

$apiToken = getenv('PHILSMS_TOKEN') ?: '897|RclyFQhD0mYNyUDRvzc4LcaoN6eGKjxxGrvAXJe6598f040a';
$recipient = $argv[1] ?? '';
$message = $argv[2] ?? '';
$senderId = getenv('PHILSMS_SENDER') ?: 'SoleSource'; // Up to 11 chars, customize as needed

if (!$recipient || !$message) {
    fwrite(STDERR, "Usage: php send-philsms.php <recipient> <message>\n");
    exit(1);
}

// PHILsms expects 63XXXXXXXXXX format
if (strpos($recipient, '09') === 0) {
    $recipient = '63' . substr($recipient, 1);
}

$payload = [
    'recipient' => $recipient,
    'sender_id' => $senderId,
    'type' => 'plain',
    'message' => $message
];

$ch = curl_init('https://dashboard.philsms.com/api/v3/sms/send');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiToken,
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
