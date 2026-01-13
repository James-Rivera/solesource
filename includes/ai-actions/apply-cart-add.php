<?php
// Trusted endpoint to apply AI-requested add_to_cart actions.
// Protect with internal secret: set environment variable AI_ACTIONS_SECRET to a strong value.
require_once __DIR__ . '/../connect.php';
header('Content-Type: application/json');

// Check secret header
$secret = getenv('AI_ACTIONS_SECRET') ?: '';
$hdr = $_SERVER['HTTP_X_AI_ACTIONS_SECRET'] ?? '';
if (empty($secret) || $hdr !== $secret) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden']);
    exit;
}

// Allow cart-only writes when configured. Full DB writes may be disabled
// to prevent AI from performing destructive actions. Enable with
// ALLOW_AI_CART_WRITE=1 or ALLOW_AI_DB_WRITE=1 in .env
$allowCart = (getenv('ALLOW_AI_CART_WRITE') === '1') || (getenv('ALLOW_AI_DB_WRITE') === '1');
if (!$allowCart) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'writes_disabled']);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;
$actions = isset($input['actions']) && is_array($input['actions']) ? $input['actions'] : [];

session_start();
if (!isset($_SESSION)) $_SESSION = [];
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$results = [];
foreach ($actions as $act) {
    if (!is_array($act) || empty($act['type'])) continue;
    if ($act['type'] !== 'add_to_cart') continue;

    $pid = isset($act['product_id']) ? (int)$act['product_id'] : 0;
    $qty = isset($act['qty']) ? max(1, (int)$act['qty']) : 1;
    $sizeId = isset($act['size_id']) && $act['size_id'] !== '' ? (int)$act['size_id'] : null;
    $size = isset($act['size']) ? (string)$act['size'] : '';

    if ($pid <= 0) {
        $results[] = ['type' => 'add_to_cart', 'product_id' => $pid, 'status' => 'invalid_product_id'];
        continue;
    }

    // Validate product exists and is active
    $pstmt = @$conn->prepare('SELECT id, name, brand, price, image, status FROM products WHERE id = ? LIMIT 1');
    $prow = null;
    if ($pstmt) {
        $pstmt->bind_param('i', $pid);
        $pstmt->execute();
        $pres = $pstmt->get_result();
        $prow = $pres ? $pres->fetch_assoc() : null;
        $pstmt->close();
    }

    if (!$prow || ($prow['status'] ?? '') !== 'active') {
        $results[] = ['type' => 'add_to_cart', 'product_id' => $pid, 'status' => 'product_not_available'];
        continue;
    }

    // Optional: check stock if a stock column exists
    $stockQty = null;
    $colsRes = @$conn->query("SHOW COLUMNS FROM products");
    $stockCol = null;
    if ($colsRes) {
        while ($c = $colsRes->fetch_assoc()) {
            $f = $c['Field'] ?? null;
            if ($f && in_array($f, ['stock_quantity','stock_qty','stock','qty','quantity','inventory'], true)) {
                $stockCol = $f;
                break;
            }
        }
        $colsRes->close();
    }
    if ($stockCol) {
        $s = @$conn->prepare("SELECT {$stockCol} AS stock_qty FROM products WHERE id = ? LIMIT 1");
        if ($s) {
            $s->bind_param('i', $pid);
            $s->execute();
            $r = $s->get_result()->fetch_assoc();
            $stockQty = $r ? (int)$r['stock_qty'] : null;
            $s->close();
        }
    }

    if (!is_null($stockQty) && $qty > $stockQty) {
        $results[] = ['type' => 'add_to_cart', 'product_id' => $pid, 'status' => 'insufficient_stock', 'available' => $stockQty];
        continue;
    }

    // Build session cart key (reuse existing cart format)
    $key = $pid . ':' . ($sizeId !== null ? $sizeId : $size);
    $legacyKey = $pid . ':' . $size;

    // Migrate legacy entry if needed
    if ($sizeId !== null && $legacyKey !== $key && isset($_SESSION['cart'][$legacyKey]) && !isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key] = $_SESSION['cart'][$legacyKey];
        $_SESSION['cart'][$key]['size_id'] = $sizeId;
        unset($_SESSION['cart'][$legacyKey]);
    }

    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $qty;
        $_SESSION['cart'][$key]['size_id'] = $sizeId;
        $status = 'incremented';
    } else {
        $_SESSION['cart'][$key] = [
            'id' => $pid,
            'size' => $size,
            'size_id' => $sizeId,
            'name' => $prow['name'] ?? '',
            'brand' => $prow['brand'] ?? '',
            'image' => $prow['image'] ?? '',
            'price' => isset($prow['price']) ? (float)$prow['price'] : 0.0,
            'qty' => $qty,
        ];
        $status = 'added';
    }

    // Recompute cart summary
    $cart = array_values($_SESSION['cart']);
    $subtotal = 0.0;
    foreach ($cart as $it) {
        $subtotal += ($it['price'] ?? 0) * ($it['qty'] ?? 0);
    }

    // Log the action (append-only)
    try {
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        $logFile = $logDir . '/ai_actions.log';
        $entry = [
            'ts' => date('c'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'action' => 'apply_cart_add',
            'product_id' => $pid,
            'qty' => $qty,
            'size_id' => $sizeId,
        ];
        @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    } catch (Exception $e) {
        // ignore logging failures
    }

    $results[] = [
        'type' => 'add_to_cart',
        'product_id' => $pid,
        'status' => $status,
        'qty' => $qty,
        'cart' => $cart,
        'subtotal' => $subtotal,
    ];
}

echo json_encode(['ok' => true, 'results' => $results]);
