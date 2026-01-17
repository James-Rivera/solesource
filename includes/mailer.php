<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function mailer_config(): array
{
    static $config;
    if ($config !== null) {
        return $config;
    }

    $path = __DIR__ . '/mail-config.php';
    if (!file_exists($path)) {
        throw new RuntimeException('Mail config missing. Ensure includes/mail_config.php exists and uses env vars.');
    }

    $loaded = include $path;
    if (!is_array($loaded)) {
        throw new RuntimeException('Mail config invalid.');
    }

    $required = ['host', 'port', 'username', 'password', 'secure', 'from_email', 'from_name'];
    foreach ($required as $key) {
        if (!array_key_exists($key, $loaded) || $loaded[$key] === '' || $loaded[$key] === null) {
            throw new RuntimeException('Missing mail config value: ' . $key);
        }
    }

    $loaded['port'] = (int) $loaded['port'];
    $config = $loaded;
    return $config;
}

function sendEmail(string $to, string $subject, string $htmlBody, string $altBody = '', array $embedded = [])
{
    $cfg = mailer_config();
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $cfg['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $cfg['username'];
        $mail->Password = $cfg['password'];
        $mail->SMTPSecure = $cfg['secure'];
        $mail->Port = (int) $cfg['port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($cfg['from_email'], $cfg['from_name']);
        $mail->clearReplyTos();
        $mail->addCustomHeader('Auto-Submitted', 'auto-generated');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'All');
        $mail->addAddress($to);

        // Attach inline images when provided
        foreach ($embedded as $embed) {
            if (empty($embed['path']) || empty($embed['cid']) || !is_readable($embed['path'])) {
                continue;
            }
            $name = $embed['name'] ?? basename($embed['path']);
            $type = $embed['type'] ?? (@mime_content_type($embed['path']) ?: 'application/octet-stream');
            $mail->addEmbeddedImage($embed['path'], $embed['cid'], $name, 'base64', $type);
        }

        $mail->isHTML(true);
        $mail->ContentType = 'text/html';
        $mail->Encoding = 'base64';
        $mail->Subject = $subject;
        // Force HTML as the primary part; leave AltBody empty unless explicitly provided
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody ?: '';

        // Optional debug: set MAIL_DEBUG_DUMP=/tmp/mail.eml to capture the MIME we send
        $dumpPath = getenv('MAIL_DEBUG_DUMP');
        if ($dumpPath) {
            // Defer generation until after we set bodies and headers
            $mime = $mail->preSend() ? $mail->getSentMIMEMessage() : '';
            if ($mime) {
                file_put_contents($dumpPath, $mime);
            }
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function ensure_email_queue_table(mysqli $conn): void
{
    $sql = "CREATE TABLE IF NOT EXISTS email_queue (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recipient VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body_html LONGTEXT NOT NULL,
        body_text LONGTEXT NULL,
        embedded_json LONGTEXT NULL,
        status ENUM('queued','sending','sent','failed') NOT NULL DEFAULT 'queued',
        attempts INT NOT NULL DEFAULT 0,
        last_error TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        sent_at DATETIME NULL,
        KEY idx_status (status),
        KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql);
}

function queueEmail(mysqli $conn, string $to, string $subject, string $htmlBody, string $altBody = '', array $embedded = []): int
{
    ensure_email_queue_table($conn);
    // Try immediate send when enabled. If it succeeds, return 0 to indicate not queued.
    if (getenv('EMAIL_SEND_IMMEDIATE') === '1') {
        try {
            $res = sendEmail($to, $subject, $htmlBody, $altBody, $embedded);
            if ($res === true) {
                return 0; // sent immediately, no queue id
            }
            // on failure, fall back to queueing
        } catch (Throwable $e) {
            // fall through to queue the message
        }
    }

    $embeddedJson = $embedded ? json_encode($embedded) : null;
    $stmt = $conn->prepare('INSERT INTO email_queue (recipient, subject, body_html, body_text, embedded_json) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('sssss', $to, $subject, $htmlBody, $altBody, $embeddedJson);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return (int) $id;
}
