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

function sendEmail(string $to, string $subject, string $htmlBody, string $altBody = '')
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

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody ?: strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
