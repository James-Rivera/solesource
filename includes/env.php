<?php
// Simple .env loader: populates getenv/$_ENV/$_SERVER
if (!function_exists('load_env')) {
    function load_env(string $path): void
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        if (!is_file($path) || !is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $quote = $value[0];
                $len = strlen($value);
                if ($len >= 2 && $value[$len - 1] === $quote) {
                    $value = substr($value, 1, $len - 2);
                }
            }
            if ($name === '') {
                continue;
            }
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
        $loaded = true;
    }
}

load_env(__DIR__ . '/../.env');
