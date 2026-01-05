<?php
/**
 * SMS Webhook Receiver
 * Receives incoming SMS messages forwarded by "Incoming SMS to URL Forwarder" app
 * Processes JSON payload and stores in database
 */

session_start();
require_once 'includes/connect.php';
require_once 'includes/sms-config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}

// Get raw JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the webhook request
logWebhookRequest('WEBHOOK_RECEIVED', $data);

// Validate webhook structure
if (empty($data['phone_number']) || empty($data['message_body'])) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Missing required fields']));
}

// Extract message details
$phone_number = preg_replace('/\D+/', '', $data['phone_number']);
$message_body = trim($data['message_body']);
$sender_name = $data['sender_name'] ?? 'Unknown';
$received_at = $data['received_at'] ?? date('Y-m-d H:i:s');

try {
    // Log incoming SMS
    $log_stmt = $conn->prepare("
        INSERT INTO sms_logs (phone_number, message_type, direction, message_body, status)
        VALUES (?, 'notification', 'inbound', ?, 'received')
    ");
    $log_stmt->bind_param('ss', $phone_number, $message_body);
    $log_stmt->execute();
    $log_stmt->close();

    // Check if message contains OTP code (6 digits)
    if (preg_match('/\b(\d{6})\b/', $message_body, $matches)) {
        $otp_code = $matches[1];

        // Find pending OTP verification by phone number
        $verify_stmt = $conn->prepare("
            SELECT id, user_id, expires_at FROM otp_verifications
            WHERE phone_number = ? AND status = 'pending' AND expires_at > NOW()
            ORDER BY created_at DESC LIMIT 1
        ");
        $verify_stmt->bind_param('s', $phone_number);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result && $verify_result->num_rows > 0) {
            $otp_record = $verify_result->fetch_assoc();

            // Update OTP verification status
            $update_stmt = $conn->prepare("
                UPDATE otp_verifications SET status = 'verified', verified_at = NOW()
                WHERE id = ? AND otp_code = ?
            ");
            $update_stmt->bind_param('is', $otp_record['id'], $otp_code);
            $update_stmt->execute();

            if ($update_stmt->affected_rows > 0) {
                // Mark SMS config as verified
                $config_stmt = $conn->prepare("
                    UPDATE sms_config SET is_verified = 1, verified_at = NOW()
                    WHERE user_id = ? AND phone_number = ?
                ");
                $config_stmt->bind_param('is', $otp_record['user_id'], $phone_number);
                $config_stmt->execute();
                $config_stmt->close();

                logWebhookRequest('OTP_VERIFIED', ['user_id' => $otp_record['user_id'], 'otp' => $otp_code]);
            }

            $update_stmt->close();
        }

        $verify_stmt->close();
    }

    // Return JSON confirmation (prevents app resend)
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'SMS received and processed',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Processing failed: ' . $e->getMessage()
    ]);

    logWebhookRequest('ERROR', ['error' => $e->getMessage()]);
}
?>
