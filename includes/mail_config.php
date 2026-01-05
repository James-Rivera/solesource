<?php
return [
    // Update these values or set environment variables.
    'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    'port' => getenv('MAIL_PORT') ?: 587,
    'username' => getenv('MAIL_USER') ?: 'noreply.solesource@gmail.com',
    'password' => getenv('MAIL_PASS') ?: 'fxvi anfg lltp zhqh',
    'secure' => getenv('MAIL_SECURE') ?: 'tls', // 'tls' or 'ssl'
    'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'no-reply@solesource.com',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'SoleSource',
];
