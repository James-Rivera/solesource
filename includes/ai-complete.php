<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/ai_client.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$message = isset($input['message']) ? trim((string)$input['message']) : '';
$context = isset($input['context']) && is_array($input['context']) ? $input['context'] : [];

// Seed lightweight fallback knowledge so the bot can be helpful without live data
$context['kb'] = [
    'shipping' => 'Shipping typically takes 3-7 business days nationwide.',
    'returns' => 'All sales are final. Please verify sizing and condition before purchase.',
    'orderHelp' => 'Provide your order number and email to check status or update your address before shipment.',
    'topPicks' => [
        'Nike Air Force 1',
        'Adidas Ultraboost',
        'Jordan 1 Mid',
        'Asics Gel-Kayano'
    ],
];

// Add lightweight session context when available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['user_id'])) {
    $context['user_id'] = (int)$_SESSION['user_id'];
    if (!empty($_SESSION['user_name'])) {
        $context['user_name'] = (string)$_SESSION['user_name'];
    }
    if (!empty($_SESSION['user_email'])) {
        $context['user_email'] = (string)$_SESSION['user_email'];
    }
}

if ($message === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'empty_message']);
    exit;
}

$result = ai_complete($message, $context);

if (!$result['ok']) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $result['error'] ?? 'unknown_error',
        'raw' => $result['raw'] ?? null,
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'data' => $result['data'],
]);
