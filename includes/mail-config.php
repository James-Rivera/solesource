<?php

// Fail fast if any mail env var is missing to avoid falling back to hardcoded secrets.
$env = static function (string $key): string {
    $value = getenv($key);
    if ($value === false || $value === '') {
        throw new RuntimeException("Missing required environment variable: {$key}");
    }
    return $value;
};

return [
    'host' => $env('MAIL_HOST'),
    'port' => (int) $env('MAIL_PORT'),
    'username' => $env('MAIL_USER'),
    'password' => $env('MAIL_PASS'),
    'secure' => $env('MAIL_SECURE'), // Expected values: tls or ssl
    'from_email' => $env('MAIL_FROM_EMAIL'),
    'from_name' => $env('MAIL_FROM_NAME'),
];
