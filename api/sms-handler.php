<?php
// SMS inbound webhook: create BOOST voucher and reply via SMS gateway
require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: application/json');

$logFile = __DIR__ . '/../logs/sms_debug.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}

function log_line(string $file, array $data): void
{
    $line = '[' . date('c') . '] ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $line . PHP_EOL, FILE_APPEND);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
        return;
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        throw new RuntimeException('Invalid JSON payload');
    }

    $from = trim((string)($payload['from'] ?? ''));
    $text = trim((string)($payload['text'] ?? ''));
    if ($from === '' || $text === '') {
        throw new RuntimeException('Missing from/text');
    }

    log_line($logFile, ['direction' => 'inbound', 'payload' => $payload]);

    if (strcasecmp($text, 'BOOST') !== 0) {
        echo json_encode(['ok' => true, 'message' => 'ignored']);
        return;
    }

    // Generate a voucher code and persist with 7-day expiry
    $expiry = (new DateTime('+7 days'))->format('Y-m-d H:i:s');
    $voucherCode = '';
    $inserted = false;

    // Ensure vouchers table exists
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

    for ($i = 0; $i < 3; $i++) {
        $code = 'SOLE-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $stmt = $conn->prepare('INSERT INTO vouchers (voucherCode, phone, expiry_date) VALUES (?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . $conn->error);
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
        if ($errno !== 1062) { // not duplicate
            throw new RuntimeException('Insert failed: ' . $conn->error);
        }
    }

    if (!$inserted) {
        throw new RuntimeException('Could not generate unique voucher');
    }

    // Outbound SMS via gateway using env configuration
    $outUrl = rtrim((string)getenv('SMS_GATEWAY_URL'), '/');
    $outUser = (string)getenv('SMS_GATEWAY_USER');
    $outPass = (string)getenv('SMS_GATEWAY_PASS');
    if ($outUrl === '' || $outUser === '' || $outPass === '') {
        throw new RuntimeException('SMS gateway env vars missing');
    }

    $outPayload = [
        'phoneNumbers' => [$from],
        'message' => "Your SoleSource code is: {$voucherCode}",
    ];

    $ch = curl_init($outUrl . '/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_USERPWD => "{$outUser}:{$outPass}",
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($outPayload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $outResponse = curl_exec($ch);
    $outErr = curl_error($ch);
    $outStatus = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    log_line($logFile, [
        'direction' => 'outbound',
        'request' => $outPayload,
        'status' => $outStatus,
        'response' => $outResponse,
        'error' => $outErr,
    ]);

    if ($outErr || $outStatus >= 400) {
        throw new RuntimeException('Outbound SMS failed: ' . ($outErr ?: 'HTTP ' . $outStatus));
    }

    echo json_encode(['ok' => true, 'voucher' => $voucherCode, 'expires' => $expiry]);
} catch (Throwable $e) {
    log_line($logFile, ['direction' => 'error', 'error' => $e->getMessage()]);
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
