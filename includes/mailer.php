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

    $defaults = [
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USER') ?: '',
        'password' => getenv('MAIL_PASS') ?: '',
        'secure' => getenv('MAIL_SECURE') ?: 'tls',
        'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'no-reply@example.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'SoleSource',
    ];

    $fileConfig = [];
    $path = __DIR__ . '/mail_config.php';
    if (file_exists($path)) {
        $loaded = include $path;
        if (is_array($loaded)) {
            $fileConfig = $loaded;
        }
    }

    $config = array_merge($defaults, $fileConfig);
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
