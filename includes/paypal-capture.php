<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/connect.php';

function ensure_payment_table(mysqli $conn): void {
    $sql = "CREATE TABLE IF NOT EXISTS payment_transactions (
        id INT(11) NOT NULL AUTO_INCREMENT,
        order_id INT(11) NOT NULL,
        provider VARCHAR(50) NOT NULL,
        provider_order_id VARCHAR(100) DEFAULT NULL,
        provider_capture_id VARCHAR(100) DEFAULT NULL,
        status VARCHAR(50) DEFAULT NULL,
        raw_payload LONGTEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
        PRIMARY KEY (id),
        KEY idx_order (order_id),
        KEY idx_provider_order (provider_order_id),
        KEY idx_provider_capture (provider_capture_id),
        CONSTRAINT fk_payment_transactions_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql);
}

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

function sanitize(string $value): string {
    return trim($value);
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$paypalOrderId = sanitize($_POST['order_id'] ?? '');
if ($paypalOrderId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'order_id is required']);
    exit;
}

$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$fullName = sanitize($_POST['full_name'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$region = sanitize($_POST['region'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$province = sanitize($_POST['province'] ?? '');
$postal = sanitize($_POST['zip_code'] ?? '');
$barangay = sanitize($_POST['barangay'] ?? '');
$country = sanitize($_POST['country'] ?? 'Philippines');

$required = [
    'Email' => $email,
    'Phone' => $phone,
    'Full Name' => $fullName,
    'Address' => $address,
    'Region' => $region,
    'Province' => $province,
    'City' => $city,
    'Barangay' => $barangay,
    'Postal Code' => $postal,
    'Country' => $country,
];

foreach ($required as $label => $value) {
    if ($value === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => $label . ' is required']);
        exit;
    }
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

$ch = curl_init($baseUrl . '/v2/checkout/orders/' . urlencode($paypalOrderId) . '/capture');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
curl_close($ch);
$capture = json_decode($response, true);

$status = $capture['status'] ?? '';
if ($status !== 'COMPLETED') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'PayPal capture failed', 'payload' => $capture]);
    exit;
}

$paypalCaptureId = '';
if (!empty($capture['purchase_units'][0]['payments']['captures'][0]['id'])) {
    $paypalCaptureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'];
}

$userId = (int) $_SESSION['user_id'];
$orderNumber = 'SO-' . date('YmdHis') . '-' . rand(1000, 9999);
$shippingAddress = implode(', ', array_filter([$address, $barangay, $city, $province, $region, $postal, $country]));

try {
    $conn->begin_transaction();

    // Stock validation with locks
    foreach ($cartItems as $ci) {
        $pid = (int) $ci['id'];
        $qty = (int) $ci['qty'];
        $sizeId = $ci['size_id'] ?? null;

        if ($sizeId) {
            $stmtCheck = $conn->prepare('SELECT stock_quantity FROM product_sizes WHERE id = ? AND product_id = ? AND is_active = 1 FOR UPDATE');
            $stmtCheck->bind_param('ii', $sizeId, $pid);
            $stmtCheck->execute();
            $res = $stmtCheck->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmtCheck->close();
            if (!$row || $qty > (int) $row['stock_quantity']) {
                throw new RuntimeException('Insufficient stock for product #' . $pid . ' size ' . $ci['size']);
            }
        } else {
            $stmtCheck = $conn->prepare('SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE');
            $stmtCheck->bind_param('i', $pid);
            $stmtCheck->execute();
            $res = $stmtCheck->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmtCheck->close();
            if (!$row || $qty > (int) $row['stock_quantity']) {
                throw new RuntimeException('Insufficient stock for product #' . $pid);
            }
        }
    }

    $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, payment_method, status, phone, full_name, address, city, province, region, barangay, zip_code, country, shipping_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $paymentMethod = 'PayPal';
    $orderStatus = 'confirmed';
    $stmtOrder->bind_param(
        'isdssssssssssss',
        $userId,
        $orderNumber,
        $subtotal,
        $paymentMethod,
        $orderStatus,
        $phone,
        $fullName,
        $address,
        $city,
        $province,
        $region,
        $barangay,
        $postal,
        $country,
        $shippingAddress
    );
    $stmtOrder->execute();
    $orderId = $stmtOrder->insert_id;
    $stmtOrder->close();

    $stmtItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, product_size_id, size, quantity, price_at_purchase) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($cartItems as $ci) {
        $sizeId = $ci['size_id'] ?? null;
        $stmtItem->bind_param('iiisid', $orderId, $ci['id'], $sizeId, $ci['size'], $ci['qty'], $ci['price']);
        $stmtItem->execute();

        if ($sizeId) {
            $stmtDec = $conn->prepare('UPDATE product_sizes SET stock_quantity = stock_quantity - ? WHERE id = ?');
            $stmtDec->bind_param('ii', $ci['qty'], $sizeId);
            $stmtDec->execute();
            $stmtDec->close();

            $stmtDecProd = $conn->prepare('UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE id = ?');
            $stmtDecProd->bind_param('ii', $ci['qty'], $ci['id']);
            $stmtDecProd->execute();
            $stmtDecProd->close();
        } else {
            $stmtDec = $conn->prepare('UPDATE products SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE id = ?');
            $stmtDec->bind_param('ii', $ci['qty'], $ci['id']);
            $stmtDec->execute();
            $stmtDec->close();
        }
    }
    $stmtItem->close();

    ensure_payment_table($conn);
    $stmtPay = $conn->prepare('INSERT INTO payment_transactions (order_id, provider, provider_order_id, provider_capture_id, status, raw_payload) VALUES (?, ?, ?, ?, ?, ?)');
    $provider = 'PayPal';
    $statusLabel = 'captured';
    $rawPayload = json_encode($capture);
    $stmtPay->bind_param('isssss', $orderId, $provider, $paypalOrderId, $paypalCaptureId, $statusLabel, $rawPayload);
    $stmtPay->execute();
    $stmtPay->close();

    $conn->commit();
    unset($_SESSION['cart']);

    echo json_encode(['ok' => true, 'status' => $status, 'order_id' => $orderId, 'paypal_capture_id' => $paypalCaptureId]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
