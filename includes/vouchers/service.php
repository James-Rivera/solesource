<?php
namespace Vouchers;

use DateInterval;
use DateTimeImmutable;
use mysqli;
use RuntimeException;

const PREFIX_SMS = 'SOLE-';
const PREFIX_REWARD = 'REWARD-';
const PREFIX_GETSOLE = 'GETSOLE-';
const EXPIRY_DAYS = 7;
const DEFAULT_SMS_DISCOUNT = 5.0;
const DEFAULT_API_DISCOUNT = 10.0;

class ClientError extends RuntimeException {}

function ensureTable(mysqli $conn): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS vouchers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(32) NOT NULL UNIQUE,
            phone VARCHAR(32) NULL,
            student_id VARCHAR(64) NULL,
            source ENUM('sms','api') NOT NULL DEFAULT 'sms',
            status ENUM('issued','redeemed','expired') NOT NULL DEFAULT 'issued',
            usage_limit TINYINT UNSIGNED NOT NULL DEFAULT 1,
            usage_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
            discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
            discount_value DECIMAL(8,2) NOT NULL DEFAULT 5.00,
            expiry_date DATETIME NOT NULL,
            redeemed_at DATETIME NULL,
            redeemed_by VARCHAR(64) NULL,
            metadata JSON NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_vouchers_phone (phone),
            KEY idx_vouchers_student (student_id),
            KEY idx_vouchers_status (status),
            KEY idx_vouchers_expiry (expiry_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ";
    if (!$conn->query($sql)) {
        throw new RuntimeException('vouchers_table_failed');
    }
}

function randomSuffix(int $length = 4): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

function generateCode(string $channel): string
{
    if ($channel === 'sms') {
        return PREFIX_SMS . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }
    $prefix = random_int(0, 1) === 0 ? PREFIX_REWARD : PREFIX_GETSOLE;
    return $prefix . randomSuffix();
}

function createVoucher(mysqli $conn, array $options): array
{
    ensureTable($conn);
    $channel = strtolower((string)($options['channel'] ?? 'sms')) === 'sms' ? 'sms' : 'api';
    $phone = trim((string)($options['phone'] ?? ''));
    if ($channel === 'sms' && $phone === '') {
        throw new ClientError('phone_required');
    }
    $phoneForInsert = $channel === 'sms' ? $phone : ($phone === '' ? null : $phone);
    $studentId = trim((string)($options['student_id'] ?? ''));
    $usageLimit = (int) ($options['usage_limit'] ?? 1);
    if ($usageLimit < 1) {
        $usageLimit = 1;
    } elseif ($usageLimit > 10) {
        $usageLimit = 10;
    }
    $metadata = $options['metadata'] ?? null;
    $metaJson = json_encode($metadata ?? (object)[], JSON_UNESCAPED_SLASHES);
    $discountType = strtolower((string)($options['discount_type'] ?? 'percent')) === 'fixed' ? 'fixed' : 'percent';
    $defaultValue = $channel === 'sms' ? DEFAULT_SMS_DISCOUNT : DEFAULT_API_DISCOUNT;
    $discountValue = (float) ($options['discount_value'] ?? $defaultValue);
    if ($discountValue < 0) {
        $discountValue = 0;
    }

    $expiresAt = (new DateTimeImmutable('now'))->add(new DateInterval('P' . EXPIRY_DAYS . 'D'));
    $expiryString = $expiresAt->format('Y-m-d H:i:s');

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $code = generateCode($channel);
        $stmt = $conn->prepare('INSERT INTO vouchers (code, phone, student_id, source, usage_limit, discount_type, discount_value, expiry_date, metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('stmt_prepare_failed');
        }
        $stmt->bind_param('ssssisdss', $code, $phoneForInsert, $studentId, $channel, $usageLimit, $discountType, $discountValue, $expiryString, $metaJson);
        if ($stmt->execute()) {
            $stmt->close();
            return [$code, $expiryString, $usageLimit, $discountType, $discountValue];
        }
        $errno = $conn->errno;
        $stmt->close();
        if ($errno !== 1062) {
            throw new RuntimeException('voucher_insert_failed');
        }
    }

    throw new RuntimeException('voucher_unique_failed');
}

function previewVoucher(mysqli $conn, string $code): array
{
    ensureTable($conn);
    $code = trim($code);
    if ($code === '') {
        throw new ClientError('code_required');
    }
    $stmt = $conn->prepare('SELECT id, code, status, expiry_date, usage_limit, usage_count, discount_type, discount_value, source, student_id FROM vouchers WHERE code = ? LIMIT 1');
    if (!$stmt) {
        throw new RuntimeException('stmt_prepare_failed');
    }
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$row) {
        throw new ClientError('voucher_not_found');
    }
    $now = new DateTimeImmutable('now');
    if ($row['status'] !== 'issued') {
        throw new ClientError('voucher_unavailable');
    }
    if ($now > new DateTimeImmutable($row['expiry_date'])) {
        throw new ClientError('voucher_expired');
    }
    if ((int)$row['usage_count'] >= (int)$row['usage_limit']) {
        throw new ClientError('voucher_limit_hit');
    }
    return [
        'id' => (int)$row['id'],
        'code' => $row['code'],
        'discount_type' => $row['discount_type'],
        'discount_value' => (float)$row['discount_value'],
        'source' => $row['source'],
        'student_id' => $row['student_id'],
    ];
}

function computeDiscount(float $subtotal, array $voucher): float
{
    if ($subtotal <= 0) {
        return 0.0;
    }
    $value = (float)($voucher['discount_value'] ?? 0);
    if (($voucher['discount_type'] ?? 'percent') === 'fixed') {
        return min($subtotal, max(0.0, $value));
    }
    return min($subtotal, $subtotal * (max(0.0, $value) / 100));
}

function markRedeemed(mysqli $conn, string $code, string $studentId, string $orderNumber, float $discountApplied = 0.0): array
{
    ensureTable($conn);
    $code = trim($code);
    if ($code === '') {
        throw new ClientError('code_required');
    }

    $conn->begin_transaction();
    $stmt = $conn->prepare('SELECT id, status, expiry_date, usage_limit, usage_count FROM vouchers WHERE code = ? FOR UPDATE');
    if (!$stmt) {
        $conn->rollback();
        throw new RuntimeException('stmt_prepare_failed');
    }
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$voucher) {
        $conn->rollback();
        throw new ClientError('voucher_not_found');
    }

    $now = new DateTimeImmutable('now');
    if ($voucher['status'] !== 'issued') {
        $conn->rollback();
        throw new ClientError('voucher_unavailable');
    }
    if ($now > new DateTimeImmutable($voucher['expiry_date'])) {
        $stmtExp = $conn->prepare("UPDATE vouchers SET status = 'expired' WHERE id = ?");
        if ($stmtExp) {
            $stmtExp->bind_param('i', $voucher['id']);
            $stmtExp->execute();
            $stmtExp->close();
        }
        $conn->commit();
        throw new ClientError('voucher_expired');
    }
    if ((int)$voucher['usage_count'] >= (int)$voucher['usage_limit']) {
        $conn->rollback();
        throw new ClientError('voucher_limit_hit');
    }

    $newUsage = (int)$voucher['usage_count'] + 1;
    $newStatus = $newUsage >= (int)$voucher['usage_limit'] ? 'redeemed' : 'issued';

    $stmtUp = $conn->prepare('UPDATE vouchers SET usage_count = ?, status = ?, redeemed_at = NOW(), redeemed_by = ?, metadata = JSON_SET(COALESCE(metadata, \'{}\'), \'$.order_number\', ?, \'$.discount_amount\', ?), student_id = CASE WHEN student_id IS NULL OR student_id = \'\' THEN ? ELSE student_id END WHERE id = ?');
    if (!$stmtUp) {
        $conn->rollback();
        throw new RuntimeException('stmt_prepare_failed');
    }
    $stmtUp->bind_param('isssdsi', $newUsage, $newStatus, $studentId, $orderNumber, $discountApplied, $studentId, $voucher['id']);
    $stmtUp->execute();
    $stmtUp->close();
    $conn->commit();

    $remaining = max(0, (int)$voucher['usage_limit'] - $newUsage);

    return [
        'status' => $newStatus,
        'remaining_uses' => $remaining,
        'canReuse' => $remaining > 0,
    ];
}

function dispatchSmsVoucher(string $phone, string $code): void
{
    $logFile = $GLOBALS['logFile'] ?? null;
    
    if ($phone === '') {
        return;
    }
    $smsProvider = getenv('SMS_PROVIDER') ?: 'philsms';
    $message = "Your SoleSource code is {$code}. Enjoy 5% off authentic pairs—keep this message safe.";

    if ($smsProvider === 'philsms') {
        $recipient = ltrim($phone, '+');
        if (strpos($recipient, '09') === 0) {
            $recipient = '63' . substr($recipient, 1);
        } elseif (strpos($recipient, '63') !== 0) {
            $recipient = ltrim($recipient, '0');
            $recipient = '63' . $recipient;
        }
        $payload = [
            'recipient' => $recipient,
            'sender_id' => getenv('PHILSMS_SENDER') ?: 'SoleSource',
            'type' => 'plain',
            'message' => $message,
        ];
        $ch = curl_init('https://dashboard.philsms.com/api/v3/sms/send');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . (getenv('PHILSMS_TOKEN') ?: ''),
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($logFile) {
            file_put_contents($logFile, '[' . date('c') . '] ' . json_encode([
                'direction' => 'outbound',
                'provider' => 'philsms',
                'request' => $payload,
                'status' => $status,
                'response' => $response,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        return;
    }

    $payload = [
        'phoneNumbers' => [$phone],
        'message' => $message,
    ];
    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode((getenv('SMS_GATEWAY_USER') ?: 'sms') . ':' . (getenv('SMS_GATEWAY_PASS') ?: 'pass')),
    ];
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => json_encode($payload),
            'timeout' => 10,
        ],
    ]);
    $fallbackUrl = getenv('SMS_GATEWAY_URL') ?: 'http://127.0.0.1:8080';
    $response = @file_get_contents(rtrim($fallbackUrl, '/') . '/messages', false, $context);
    if ($logFile) {
        file_put_contents($logFile, '[' . date('c') . '] ' . json_encode([
            'direction' => 'outbound',
            'provider' => 'smsgate',
            'request' => $payload,
            'response' => $response ?: false,
            'http_response_header' => isset($http_response_header) ? $http_response_header : null,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function notifyCollaborator(array $payload): void
{
    $url = getenv('COLLAB_WEBHOOK_URL');
    if (!$url) {
        return;
    }

    // Add integration type so receivers can identify source
    $payload['integration'] = 'course';

    // Logging setup (helpful for debugging webhook deliveries)
    $logDir = __DIR__ . '/../../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    $logFile = $logDir . '/webhooks.log';

    $secret = getenv('COLLAB_WEBHOOK_SECRET') ?: '';
    $headers = ['Content-Type: application/json'];
    if ($secret !== '') {
        $headers[] = 'Authorization: Bearer ' . $secret;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => 1,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 10,
        // Allow self-signed certs in development tunnels; remove in production
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $requestBody = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $start = microtime(true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $entry = [
        'time' => date('c'),
        'url' => $url,
        'headers' => $headers,
        'request' => $payload,
        'response' => $response,
        'http_code' => $httpCode,
        'curl_error' => $curlErr,
        'duration_ms' => round((microtime(true) - $start) * 1000),
    ];
    @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
