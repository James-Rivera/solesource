<?php
require_once __DIR__ . '/../../includes/connect.php';
require_once __DIR__ . '/../../includes/vouchers/service.php';
require_once __DIR__ . '/../../includes/api/auth.php';

use Vouchers\ClientError;
use function Vouchers\previewVoucher;

header('Content-Type: application/json');
require_course_partner_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method-not-allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$code = strtoupper(trim((string)($body['voucher-code'] ?? ($body['code'] ?? ''))));
if ($code === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'voucher-code-required']);
    exit;
}

try {
    $voucher = previewVoucher($conn, $code);
    http_response_code(200);
    echo json_encode([
        'ok' => true,
        'voucher' => [
            'code' => $voucher['code'],
            'discount_type' => $voucher['discount_type'],
            'discount_value' => $voucher['discount_value'],
        ],
    ]);
} catch (ClientError $e) {
    $status = $e->getMessage() === 'voucher_not_found' ? 404 : 409;
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => str_replace('_', '-', $e->getMessage())]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'internal-error']);
}
