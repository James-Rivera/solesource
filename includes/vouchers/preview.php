<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/connect.php';
require_once __DIR__ . '/service.php';

use Vouchers\ClientError;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method-not-allowed']);
    exit;
}

$code = strtoupper(trim((string)($_POST['voucher_code'] ?? '')));
if ($code === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'voucher-code-required']);
    exit;
}

try {
    $voucher = Vouchers\previewVoucher($conn, $code);
    echo json_encode([
        'ok' => true,
        'discount_type' => $voucher['discount_type'],
        'discount_value' => $voucher['discount_value'],
        'code' => $voucher['code'],
        'source' => $voucher['source'] ?? null,
    ]);
    exit;
} catch (ClientError $e) {
    $msg = $e->getMessage();
    $map = [
        'voucher_not_found' => 404,
        'voucher_unavailable' => 409,
        'voucher_expired' => 410,
        'voucher_limit_hit' => 409,
    ];
    $status = $map[$msg] ?? 400;
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => str_replace('_', '-', $msg)]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'internal-error']);
    exit;
}
