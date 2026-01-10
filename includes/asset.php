<?php
// Centralized asset versioning helper
// - In development (`APP_ENV=development`) this appends the current timestamp to bust caches.
// - In production (`APP_ENV=production`) it uses `ASSET_VERSION` from environment if set,
//   otherwise falls back to filemtime() when the file exists, or a fixed default.
// To bump production assets manually, set `ASSET_VERSION` in your .env (e.g. ASSET_VERSION=1.2.3).

if (!defined('ASSET_DEFAULT_VERSION')) {
    define('ASSET_DEFAULT_VERSION', getenv('ASSET_VERSION') ?: '1.0.0');
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        // normalize path to start with a single slash
        $path = '/' . ltrim($path, '/');

        $env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'development');

        if ($env === 'development') {
            $ver = time();
        } else {
            $envVer = getenv('ASSET_VERSION') ?: null;
            if ($envVer) {
                $ver = $envVer;
            } else {
                // try filemtime on the local filesystem when available
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $path;
                if (is_file($filePath)) {
                    $ver = filemtime($filePath);
                } else {
                    $ver = ASSET_DEFAULT_VERSION;
                }
            }
        }

        $sep = (strpos($path, '?') === false) ? '?' : '&';
        return $path . $sep . 'v=' . $ver;
    }
}
