<?php
// SMS inbound webhook: BOOST keyword -> 6-digit voucher + outbound reply via SMSGate
require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/connect.php';

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
    log_line($logFile, ['direction' => 'inbound', 'raw' => $payload]);

    if (!is_array($payload)) {
        throw new RuntimeException('invalid_json');
    }

    $from = trim((string)($payload['from'] ?? ''));
    $text = trim((string)($payload['text'] ?? ''));
    if ($from === '' || $text === '') {
        throw new RuntimeException('missing_from_or_text');
    }

    if (strcasecmp($text, 'BOOST') !== 0) {
        echo json_encode(['ok' => true, 'message' => 'ignored']);
        http_response_code(200);
        return;
    }

    // Generate 6-digit voucher and persist with 7-day expiry
    $expiry = (new DateTime('+7 days'))->format('Y-m-d H:i:s');
    $voucherCode = '';
    $inserted = false;

    $tableSql = "
        CREATE TABLE IF NOT EXISTS vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            voucherCode VARCHAR(32) NOT NULL UNIQUE,
            phone VARCHAR(32) NOT NULL,
            expiry_date DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_phone (phone),
            INDEX idx_expiry (expiry_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    $conn->query($tableSql);

    for ($i = 0; $i < 5; $i++) {
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare('INSERT INTO vouchers (voucherCode, phone, expiry_date) VALUES (?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('prepare_failed');
        }
        $stmt->bind_param('sss', $code, $from, $expiry);
        $ok = $stmt->execute();
        if ($ok) {
            $voucherCode = $code;
            $inserted = true;
            $stmt->close();
            break;
        }
        $errno = $conn->errno;
        $stmt->close();
        if ($errno !== 1062) {
            throw new RuntimeException('insert_failed');
        }
    }

    if (!$inserted) {
        throw new RuntimeException('could_not_generate_unique_voucher');
    }


    // SMS sending config
    $sms_provider = getenv('SMS_PROVIDER') ?: 'philsms';
    $philsms_token = getenv('PHILSMS_TOKEN') ?: '897|RclyFQhD0mYNyUDRvzc4LcaoN6eGKjxxGrvAXJe6598f040a';
    $philsms_sender = getenv('PHILSMS_SENDER') ?: 'SoleSource';
    $gateway_url = getenv('SMS_GATEWAY_URL') ?: 'http://192.168.0.251:8080';
    $gateway_user = getenv('SMS_GATEWAY_USER') ?: 'sms';
    $gateway_pass = getenv('SMS_GATEWAY_PASS') ?: '88888888';

    // Format phone for PHILsms (639XXXXXXXXX)
    $philsms_recipient = ltrim($from, '+');
    if (strpos($philsms_recipient, '09') === 0) {
        $philsms_recipient = '63' . substr($philsms_recipient, 1);
    }
    $sent = false;
    $error = '';
    if ($sms_provider === 'philsms') {
        $philsms_payload = [
            'recipient' => $philsms_recipient,
            'sender_id' => $philsms_sender,
            'type' => 'plain',
            'message' => "Your SoleSource code is: {$voucherCode}"
        ];
        $ch = curl_init('https://dashboard.philsms.com/api/v3/sms/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $philsms_token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($philsms_payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $outResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $sent = $outResponse && $httpCode < 400 && strpos($outResponse, 'success') !== false;
        $error = $outResponse;
    }
    if (!$sent) {
        // fallback to local gateway
        $outPayload = [
            'phoneNumbers' => [$from],
            'message' => "Your SoleSource code is: {$voucherCode}",
        ];
        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($gateway_user . ':' . $gateway_pass),
        ];
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => json_encode($outPayload),
                'timeout' => 10,
            ],
        ]);
        $outResponse = @file_get_contents(rtrim($gateway_url, '/') . '/messages', false, $context);
        $httpCode = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $httpCode = (int)$m[1];
        }
    }
    log_line($logFile, [
        'direction' => 'outbound',
        'request' => $sms_provider === 'philsms' ? ($philsms_payload ?? []) : ($outPayload ?? []),
        'status' => $httpCode,
        'response' => $outResponse,
    ]);
    if ($outResponse === false || $httpCode >= 400) {
        throw new RuntimeException('outbound_failed');
    }

    echo json_encode(['ok' => true, 'voucher' => $voucherCode, 'expires' => $expiry]);
    http_response_code(200);
} catch (Throwable $e) {
    log_line($logFile, ['direction' => 'error', 'error' => $e->getMessage()]);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    http_response_code(200);
}
