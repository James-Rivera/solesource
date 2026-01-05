<?php
require __DIR__ . '/includes/mailer.php';

$to = getenv('MAIL_TEST_TO') ?: getenv('MAIL_FROM_EMAIL') ?: 'test@example.com';
$subject = 'SoleSource mail test';
$body = '<p>Hello from SoleSource mail test.</p>';
$result = sendEmail($to, $subject, $body);

if ($result === true) {
    echo "Mail sent to {$to}\n";
} else {
    echo "Mail failed: {$result}\n";
}
