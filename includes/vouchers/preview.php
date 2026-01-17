<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/connect.php';
require_once __DIR__ . '/service.php';
session_start();

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
    // Compute cart subtotal from session if available so we can return a preview total
    $subtotal = 0.0;
    $sessionCart = $_SESSION['cart'] ?? [];
    if (!empty($sessionCart)) {
        $productIds = array_unique(array_map(function($item){ return (int)($item['id'] ?? 0); }, $sessionCart));
        $productIds = array_filter($productIds, function($id){ return $id > 0; });
        if (!empty($productIds)) {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $types = str_repeat('i', count($productIds));
            $stmt = $conn->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
            if ($stmt) {
                $bind = [$types];
                foreach ($productIds as $idx => $pid) { $bind[] = &$productIds[$idx]; }
                call_user_func_array([$stmt, 'bind_param'], $bind);
                $stmt->execute();
                $res = $stmt->get_result();
                $prices = [];
                while ($r = $res->fetch_assoc()) { $prices[(int)$r['id']] = (float)$r['price']; }
                $stmt->close();

                foreach ($sessionCart as $item) {
                    $pid = (int)($item['id'] ?? 0);
                    $qty = max(1, (int)($item['qty'] ?? 1));
                    $price = isset($prices[$pid]) ? $prices[$pid] : 0.0;
                    $subtotal += $price * $qty;
                }
            }
        }
    }

    $discount = Vouchers\computeDiscount((float)$subtotal, $voucher);
    $totalAmount = max(0.0, $subtotal - $discount);

    echo json_encode([
        'ok' => true,
        'discount_type' => $voucher['discount_type'],
        'discount_value' => $voucher['discount_value'],
        'code' => $voucher['code'],
        'source' => $voucher['source'] ?? null,
        'subtotal' => $subtotal,
        'discount_amount' => $discount,
        'total_amount' => $totalAmount,
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
