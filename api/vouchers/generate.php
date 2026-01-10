<?php
require_once __DIR__ . '/../../includes/connect.php';
require_once __DIR__ . '/../../includes/vouchers/service.php';
require_once __DIR__ . '/../../includes/api/auth.php';

use Vouchers\ClientError;
use function Vouchers\createVoucher;
use function Vouchers\dispatchSmsVoucher;

header('Content-Type: application/json');
require_course_partner_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method-not-allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid-json']);
    exit;
}

$phone = trim((string)($body['phone'] ?? ($body['phone-number'] ?? '')));
$studentId = trim((string)($body['student_id'] ?? ($body['student-id'] ?? '')));
$channelRaw = strtolower((string)($body['channel'] ?? 'api'));
$channel = $channelRaw === 'sms' ? 'sms' : 'api';
$usageLimit = isset($body['usage_limit']) ? (int)$body['usage_limit'] : (isset($body['usage-limit']) ? (int)$body['usage-limit'] : 1);
$discountType = $body['discount_type'] ?? ($body['discount-type'] ?? null);
$discountValue = $body['discount_value'] ?? ($body['discount-value'] ?? null);

if ($studentId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'student-id-required']);
    exit;
}

if ($channel === 'sms' && $phone === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'phone-required-for-sms']);
    exit;
}

try {
    [$code, $expiresAt, $limit, $discountTypeUsed, $discountValueUsed] = createVoucher($conn, [
        'phone' => $channel === 'sms' ? $phone : '',
        'student_id' => $studentId,
        'channel' => $channel,
        'usage_limit' => $usageLimit,
        'discount_type' => $discountType,
        'discount_value' => $discountValue,
    ]);

    if ($channel === 'sms') {
        dispatchSmsVoucher($phone, $code);
    }

    http_response_code(201);
    echo json_encode([
        'ok' => true,
        'code' => $code,
        'expires-at' => $expiresAt,
        'usage-limit' => $limit,
        'discount-type' => $discountTypeUsed,
        'discount-value' => $discountValueUsed,
    ]);
} catch (ClientError $e) {
    http_response_code(400);
    $message = str_replace('_', '-', $e->getMessage());
    echo json_encode(['ok' => false, 'error' => $message]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'internal-error']);
}
