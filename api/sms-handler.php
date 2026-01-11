<?php
// SMS inbound webhook: BOOST keyword -> 6-digit voucher + outbound reply via SMSGate
require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/vouchers/service.php';

use function Vouchers\createVoucher;
use function Vouchers\dispatchSmsVoucher;

header('Content-Type: application/json');

$logFile = __DIR__ . '/../logs/sms_log.txt';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}

function log_line(string $file, array $data): void
{
    $line = '[' . date('c') . '] ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
        http_response_code(200);
        return;
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    
    // Make log function available to voucher service
    $GLOBALS['logFile'] = $logFile;
    
    log_line($logFile, ['direction' => 'inbound', 'raw' => $payload]);

    if (!is_array($payload)) {
        throw new RuntimeException('invalid_json');
    }

    $from = trim((string)($payload['from'] ?? ''));
    $text = trim((string)($payload['text'] ?? ''));
    if ($from === '' || $text === '') {
        throw new RuntimeException('missing_from_or_text');
    }

    // Ignore echoes coming from SMS forwarder when it re-injects outbound replies
    $forwarderEchoMarkers = [
        'Your SoleSource code is',
    ];
    foreach ($forwarderEchoMarkers as $marker) {
        if (stripos($text, $marker) === 0) {
            echo json_encode(['ok' => true, 'message' => 'outbound_echo_ignored']);
            http_response_code(200);
            return;
        }
    }

    if (strcasecmp($text, 'BOOST') !== 0) {
        echo json_encode(['ok' => true, 'message' => 'ignored']);
        http_response_code(200);
        return;
    }

    [$voucherCode, $expiry] = createVoucher($conn, [
        'phone' => $from,
        'student_id' => '',
        'channel' => 'sms',
        'usage_limit' => 1,
    ]);

    dispatchSmsVoucher($from, $voucherCode);

    log_line($logFile, [
        'direction' => 'outbound',
        'message' => "voucher_sent",
        'code' => $voucherCode,
        'expires' => $expiry,
    ]);

    echo json_encode(['ok' => true, 'voucher' => $voucherCode, 'expires' => $expiry]);
    http_response_code(200);
} catch (Throwable $e) {
    log_line($logFile, ['direction' => 'error', 'error' => $e->getMessage()]);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    http_response_code(200);
}
