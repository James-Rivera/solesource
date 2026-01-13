<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connect.php';

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

$clientId = getenv('PAYPAL_CLIENT_ID');
$secret = getenv('PAYPAL_CLIENT_SECRET');
$baseUrl = getenv('PAYPAL_BASE_URL') ?: 'https://api-m.sandbox.paypal.com';
$webhookId = getenv('PAYPAL_WEBHOOK_ID');

if (!$clientId || !$secret || !$webhookId) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Missing PayPal credentials']);
    exit;
}

$body = file_get_contents('php://input');
$headers = getallheaders();
$transId = $headers['Paypal-Transmission-Id'] ?? $headers['PAYPAL-TRANSMISSION-ID'] ?? '';
$transTime = $headers['Paypal-Transmission-Time'] ?? $headers['PAYPAL-TRANSMISSION-TIME'] ?? '';
$certUrl = $headers['Paypal-Cert-Url'] ?? $headers['PAYPAL-CERT-URL'] ?? '';
$authAlgo = $headers['Paypal-Auth-Algo'] ?? $headers['PAYPAL-AUTH-ALGO'] ?? '';
$transSig = $headers['Paypal-Transmission-Sig'] ?? $headers['PAYPAL-TRANSMISSION-SIG'] ?? '';

if (!$transId || !$transTime || !$certUrl || !$authAlgo || !$transSig) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing PayPal verification headers']);
    exit;
}

$accessToken = get_paypal_token($baseUrl, $clientId, $secret);
if (!$accessToken) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Failed to authenticate with PayPal']);
    exit;
}

$verificationPayload = [
    'auth_algo' => $authAlgo,
    'cert_url' => $certUrl,
    'transmission_id' => $transId,
    'transmission_sig' => $transSig,
    'transmission_time' => $transTime,
    'webhook_id' => $webhookId,
    'webhook_event' => json_decode($body, true),
];

$ch = curl_init($baseUrl . '/v1/notifications/verify-webhook-signature');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($verificationPayload),
    CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
curl_close($ch);
$verify = json_decode($response, true);

if (($verify['verification_status'] ?? '') !== 'SUCCESS') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid PayPal signature']);
    exit;
}

$event = json_decode($body, true) ?: [];
$eventType = $event['event_type'] ?? '';
$paypalOrderId = $event['resource']['id'] ?? '';
$paypalCaptureId = $event['resource']['supplementary_data']['related_ids']['capture_id'] ?? '';
if (!$paypalCaptureId && isset($event['resource']['id']) && str_starts_with($event['resource']['id'], 'CAP')) {
    $paypalCaptureId = $event['resource']['id'];
}
if (isset($event['resource']['supplementary_data']['related_ids']['order_id'])) {
    $paypalOrderId = $event['resource']['supplementary_data']['related_ids']['order_id'];
}

try {
    ensure_payment_table($conn);

    $stmtFind = $conn->prepare('SELECT order_id FROM payment_transactions WHERE provider = "PayPal" AND (provider_order_id = ? OR provider_capture_id = ?) ORDER BY id DESC LIMIT 1');
    $stmtFind->bind_param('ss', $paypalOrderId, $paypalCaptureId);
    $stmtFind->execute();
    $res = $stmtFind->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmtFind->close();

    if ($row && isset($row['order_id'])) {
        $orderId = (int) $row['order_id'];
        $stmtUpdate = $conn->prepare("UPDATE orders SET status = 'confirmed', payment_method = 'PayPal' WHERE id = ? AND status IN ('pending','confirmed')");
        $stmtUpdate->bind_param('i', $orderId);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $statusLabel = $eventType === 'PAYMENT.CAPTURE.COMPLETED' ? 'captured' : 'approved';
        $stmtTx = $conn->prepare('UPDATE payment_transactions SET status = ?, raw_payload = ? WHERE order_id = ?');
        $rawPayload = json_encode($event);
        $stmtTx->bind_param('ssi', $statusLabel, $rawPayload, $orderId);
        $stmtTx->execute();
        $stmtTx->close();
    }

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
