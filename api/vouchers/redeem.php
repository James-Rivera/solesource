<?php
require_once __DIR__ . '/../../includes/connect.php';
require_once __DIR__ . '/../../includes/vouchers/service.php';
require_once __DIR__ . '/../../includes/api/auth.php';

use Vouchers\ClientError;
use function Vouchers\notifyCollaborator;
use function Vouchers\markRedeemed;

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

$code = strtoupper(trim((string)($body['code'] ?? ($body['voucher-code'] ?? ''))));
$studentId = trim((string)($body['student_id'] ?? ($body['student-id'] ?? '')));
$orderNumber = trim((string)($body['order_number'] ?? ($body['order-number'] ?? '')));
$discountApplied = isset($body['discount_applied']) ? (float)$body['discount_applied'] : (isset($body['discount-applied']) ? (float)$body['discount-applied'] : 0.0);

if ($code === '' || $studentId === '' || $orderNumber === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing-required-fields']);
    exit;
}

try {
    $result = markRedeemed($conn, $code, $studentId, $orderNumber, $discountApplied);
    notifyCollaborator([
        'code' => $code,
        'student-id' => $studentId,
        'order-number' => $orderNumber,
        'redeemed-at' => date(DATE_ATOM),
        'remaining-uses' => $result['remaining_uses'],
        'can-reuse' => $result['canReuse'],
        'discount-applied' => $discountApplied,
    ]);

    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'status' => $result['status'],
        'remaining-uses' => $result['remaining_uses'],
        'can-reuse' => $result['canReuse'],
        'discount-applied' => $discountApplied,
    ]);
} catch (ClientError $e) {
    $status = $e->getMessage() === 'voucher_not_found' ? 404 : 409;
    http_response_code($status);
    $message = str_replace('_', '-', $e->getMessage());
    echo json_encode(['ok' => false, 'error' => $message]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'internal-error']);
}
