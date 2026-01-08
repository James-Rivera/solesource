<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../connect.php';

function build_cart_summary(mysqli $conn): array {
    $sessionCart = $_SESSION['cart'] ?? [];
    if (empty($sessionCart)) {
        return [[], 0.0];
    }

    $productIds = array_unique(array_filter(array_map(static function ($item) {
        return isset($item['id']) ? (int) $item['id'] : 0;
    }, $sessionCart)));
    $productIds = array_values(array_filter($productIds, static fn($id) => $id > 0));

    $sizeIds = array_unique(array_filter(array_map(static function ($item) {
        return isset($item['size_id']) ? (int) $item['size_id'] : 0;
    }, $sessionCart)));
    $sizeIds = array_values(array_filter($sizeIds, static fn($id) => $id > 0));

    $sizeMap = [];
    if ($sizeIds) {
        $placeholders = implode(',', array_fill(0, count($sizeIds), '?'));
        $types = str_repeat('i', count($sizeIds));
        $stmt = $conn->prepare("SELECT id, product_id, size_label, size_system, gender, stock_quantity FROM product_sizes WHERE id IN ($placeholders) AND is_active = 1");
        $bind = [$types];
        foreach ($sizeIds as $idx => $sid) {
            $bind[] = &$sizeIds[$idx];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $sizeMap[(int) $row['id']] = $row;
        }
        $stmt->close();
    }

    $products = [];
    if ($productIds) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $types = str_repeat('i', count($productIds));
        $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $bind = [$types];
        foreach ($productIds as $idx => $pid) {
            $bind[] = &$productIds[$idx];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $products[$row['id']] = $row;
        }
        $stmt->close();
    }

    $cartItems = [];
    $subtotal = 0.0;
    foreach ($sessionCart as $item) {
        $pid = isset($item['id']) ? (int) $item['id'] : 0;
        if (!$pid || !isset($products[$pid])) {
            continue;
        }
        $qty = max(1, (int) ($item['qty'] ?? 1));
        $price = (float) $products[$pid]['price'];
        $sizeId = isset($item['size_id']) ? (int) $item['size_id'] : null;
        $sizeRow = $sizeId && isset($sizeMap[$sizeId]) ? $sizeMap[$sizeId] : null;
        $displaySize = $sizeRow['size_label'] ?? ($item['size'] ?? '');
        $lineTotal = $price * $qty;
        $subtotal += $lineTotal;

        $cartItems[] = [
            'id' => $pid,
            'name' => $products[$pid]['name'],
            'brand' => $products[$pid]['brand'],
            'image' => $products[$pid]['image'],
            'size' => $displaySize,
            'size_id' => $sizeId,
            'qty' => $qty,
            'price' => $price,
            'line_total' => $lineTotal,
        ];
    }

    return [$cartItems, $subtotal];
}

function get_paypal_token(string $baseUrl, string $clientId, string $secret): string {
    $ch = curl_init($baseUrl . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_USERPWD => $clientId . ':' . $secret,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($response, true);
    return $json['access_token'] ?? '';
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

[$cartItems, $subtotal] = build_cart_summary($conn);
if (empty($cartItems)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Cart is empty']);
    exit;
}

$clientId = getenv('PAYPAL_CLIENT_ID');
$secret = getenv('PAYPAL_CLIENT_SECRET');
$baseUrl = getenv('PAYPAL_BASE_URL') ?: 'https://api-m.sandbox.paypal.com';

if (!$clientId || !$secret) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Missing PayPal credentials']);
    exit;
}

$accessToken = get_paypal_token($baseUrl, $clientId, $secret);
if (!$accessToken) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Failed to authenticate with PayPal']);
    exit;
}

$payload = [
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'amount' => [
            'currency_code' => 'PHP',
            'value' => number_format($subtotal, 2, '.', ''),
        ],
    ]],
    'application_context' => [
        'shipping_preference' => 'NO_SHIPPING',
    ],
];

$ch = curl_init($baseUrl . '/v2/checkout/orders');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
curl_close($ch);
$order = json_decode($response, true);

if (empty($order['id'])) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Failed to create PayPal order', 'payload' => $order]);
    exit;
}

echo json_encode(['ok' => true, 'id' => $order['id']]);
