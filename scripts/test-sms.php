<?php
// Helper to test inbound webhook and SMSGate without UI
//php [test-sms.php](http://_vscodecontentref_/1) inbound "+15551234567" "BOOST" to test forwarder
//php [test-sms.php](http://_vscodecontentref_/2) smsgate "+15551234567" "Hello from test"


require_once __DIR__ . '/../includes/env.php';

$mode = $argv[1] ?? '';
$to = $argv[2] ?? '';
$text = $argv[3] ?? '';

if (!in_array($mode, ['inbound', 'smsgate'], true) || $to === '' || $text === '') {
    fwrite(STDERR, "Usage:\n  php scripts/test-sms.php inbound \"+15551234567\" \"BOOST\"\n  php scripts/test-sms.php smsgate \"+15551234567\" \"Test message\"\n");
    exit(1);
}

$gatewayUrl  = rtrim(getenv('SMS_GATEWAY_URL') ?: 'http://192.168.0.251:8080', '/');
$gatewayUser = getenv('SMS_GATEWAY_USER') ?: 'sms';
$gatewayPass = getenv('SMS_GATEWAY_PASS') ?: '88888888';
$webhook     = rtrim(getenv('APP_URL') ?: 'http://localhost/solesource', '/') . '/api/sms-handler.php';

if ($mode === 'inbound') {
    $payload = ['from' => $to, 'text' => $text];
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($payload),
            'timeout' => 10,
        ],
    ]);
    $res = @file_get_contents($webhook, false, $ctx);
    echo "Webhook response:\n" . ($res !== false ? $res : 'no response') . "\n";
    exit(0);
}

// smsgate mode
$url = $gatewayUrl . '/messages';
$payload = [
    'phoneNumbers' => [$to],
    'message'      => $text,
];
$headers = [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode("$gatewayUser:$gatewayPass"),
];
$ctx = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => implode("\r\n", $headers),
        'content' => json_encode($payload),
        'timeout' => 10,
    ],
]);
$res = @file_get_contents($url, false, $ctx);
echo "SMSGate response:\n" . ($res !== false ? $res : 'no response') . "\n";
exit(0);
