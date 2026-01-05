<?php
/**
 * SMS Gateway Configuration
 * Integrates with SMSGate (Android) and Incoming SMS to URL Forwarder
 * Supports mock mode for testing without physical SMS gateway
 */

// ============================================================
// SMS GATEWAY MODE - Choose one:
// ============================================================
// 'dito'     - DITO carrier email-to-SMS (requires mail() configured)
// 'smsgate'  - Real Android SMSGate app (requires installation)
// 'mock'     - Simulates SMS sending (for testing/development)
// 'file'     - Writes OTP to text file instead of sending SMS
// ============================================================
define('SMS_MODE', 'mock'); // Mock mode for development (no mail server needed)

// DITO Email-to-SMS Configuration
define('DITO_SMS_GATEWAY', '@sms.dito.ph'); // DITO carrier email suffix

// SMSGate API Configuration (Outbound SMS) - Only used if SMS_MODE = 'smsgate'
define('SMSGATE_API_URL', 'http://192.168.1.100:9090/send'); // Android SMSGate API endpoint
define('SMSGATE_API_KEY', 'your-smsgate-api-key'); // Optional API key for authentication

// File-based OTP storage (for SMS_MODE = 'file')
define('OTP_FILE_PATH', __DIR__ . '/../logs/otp_codes.txt');

// Webhook Configuration (Inbound SMS)
define('WEBHOOK_LOG_FILE', __DIR__ . '/../logs/sms_webhook.log');
define('WEBHOOK_SECRET', 'your-webhook-secret-key'); // Security token for webhook verification

// OTP Configuration
define('OTP_VALIDITY_MINUTES', 10);
define('OTP_LENGTH', 6);
define('MAX_OTP_ATTEMPTS', 3);

/**
 * Send SMS via configured gateway
 * Supports multiple modes: SMSGate (real), Mock (test), File (debug)
 * 
 * @param string $phone_number Recipient phone number (format: 09XXXXXXXXX or +639XXXXXXXXX)
 * @param string $message SMS message body
 * @return bool True if request sent/logged successfully
 */
function sendSms($phone_number, $message) {
    $phone_number = preg_replace('/\D+/', '', $phone_number);
    if (strlen($phone_number) < 10) {
        return false;
    }

    switch (SMS_MODE) {
        case 'dito':
            return sendSmsViaDITO($phone_number, $message);
        case 'smsgate':
            return sendSmsViaSMSGate($phone_number, $message);
        case 'file':
            return sendSmsToFile($phone_number, $message);
        case 'mock':
        default:
            return sendSmsMock($phone_number, $message);
    }
}

/**
 * Send SMS via DITO Carrier Email-to-SMS Gateway (REAL SMS)
 * Works with DITO Philippine carrier
 * 
 * @param string $phone_number Recipient phone number (format: 09XXXXXXXXX)
 * @param string $message SMS message body
 * @return bool True if email sent successfully
 */
function sendSmsViaDITO($phone_number, $message) {
    // Convert phone number to DITO email format
    // 09667455702 becomes 9667455702@sms.dito.ph
    $clean_number = preg_replace('/\D+/', '', $phone_number);
    
    // Remove leading 0 if present
    if (substr($clean_number, 0, 1) === '0') {
        $clean_number = substr($clean_number, 1);
    }
    
    // Create DITO email address
    $dito_email = $clean_number . DITO_SMS_GATEWAY;
    
    // Message must be 160 characters or less for SMS
    $message = substr($message, 0, 160);
    
    // Use mail() to send to DITO gateway
    // Subject line becomes SMS body in some cases, message body is primary
    $headers = [
        'From: ' . get_config_email(),
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    $result = @mail($dito_email, 'SMS', $message, implode("\r\n", $headers));
    
    return $result !== false;
}

/**
 * Get configured email for sending from
 * @return string Email address
 */
function get_config_email() {
    // Use your website's admin email or any configured email
    return 'noreply@solesource.local'; // Adjust as needed
}

/**
 * Send SMS via SMSGate Android app (REAL SMS)
 * 
 * @param string $phone_number Recipient phone number
 * @param string $message SMS message body
 * @return bool True if HTTP request successful
 */
function sendSmsViaSMSGate($phone_number, $message) {
    $payload = [
        'phone' => $phone_number,
        'message' => substr($message, 0, 160),
        'timestamp' => date('Y-m-d H:i:s')
    ];

    $ch = curl_init(SMSGATE_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . SMSGATE_API_KEY
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $http_code === 200;
}

/**
 * Send SMS to text file (DEBUG MODE)
 * Useful for testing without SMS access
 * 
 * @param string $phone_number Recipient phone number
 * @param string $message SMS message body
 * @return bool True if file write successful
 */
function sendSmsToFile($phone_number, $message) {
    @mkdir(dirname(OTP_FILE_PATH), 0755, true);
    
    $entry = sprintf(
        "[%s] TO: %s | %s\n",
        date('Y-m-d H:i:s'),
        $phone_number,
        $message
    );

    return @file_put_contents(OTP_FILE_PATH, $entry, FILE_APPEND) !== false;
}

/**
 * Mock SMS send (DEVELOPMENT MODE)
 * Simulates successful SMS transmission for testing
 * 
 * @param string $phone_number Recipient phone number
 * @param string $message SMS message body
 * @return bool Always returns true (mock success)
 */
function sendSmsMock($phone_number, $message) {
    // In mock mode, we just pretend SMS was sent successfully
    // The OTP code will be displayed on the UI for testing
    return true;
}

/**
 * Generate random OTP code
 * 
 * @param int $length OTP length
 * @return string Random OTP code
 */
function generateOTP($length = OTP_LENGTH) {
    return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Log webhook request to file
 * 
 * @param string $method Request method
 * @param array $data Request data
 */
function logWebhookRequest($method, $data) {
    $log_entry = sprintf(
        "[%s] [%s] %s\n",
        date('Y-m-d H:i:s'),
        $method,
        json_encode($data)
    );

    @file_put_contents(WEBHOOK_LOG_FILE, $log_entry, FILE_APPEND);
}

/**
 * Ensure SMS-related database tables exist
 * 
 * @param mysqli $conn Database connection
 */
function ensureSMSSchema($conn) {
    // SMS Configuration table
    $conn->query("CREATE TABLE IF NOT EXISTS `sms_config` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `phone_number` varchar(50) NOT NULL,
        `is_verified` tinyint(1) DEFAULT 0,
        `verified_at` datetime DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_user_phone` (`user_id`, `phone_number`),
        CONSTRAINT `fk_sms_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // OTP verification table
    $conn->query("CREATE TABLE IF NOT EXISTS `otp_verifications` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `phone_number` varchar(50) NOT NULL,
        `otp_code` varchar(10) NOT NULL,
        `status` enum('pending','verified','expired','failed') DEFAULT 'pending',
        `attempts` int(11) DEFAULT 0,
        `expires_at` datetime NOT NULL,
        `verified_at` datetime DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_status` (`user_id`, `status`),
        CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // SMS Log table (for audit/debugging)
    $conn->query("CREATE TABLE IF NOT EXISTS `sms_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11),
        `phone_number` varchar(50),
        `message_type` enum('otp','notification','alert') DEFAULT 'notification',
        `direction` enum('outbound','inbound') DEFAULT 'outbound',
        `message_body` text,
        `status` enum('sent','received','failed','pending') DEFAULT 'pending',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_created` (`user_id`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
}
?>
