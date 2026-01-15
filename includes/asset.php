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

        // Determine application base path. Prefer explicit APP_URL path, otherwise try to infer from SCRIPT_NAME.
        $base = '';
        $envAppUrl = getenv('APP_URL') ?: ($_SERVER['APP_URL'] ?? '');
        if ($envAppUrl) {
            $parts = parse_url($envAppUrl);
            if (!empty($parts['path'])) {
                $base = rtrim($parts['path'], '/');
            }
        }
        if ($base === '') {
            $script = $_SERVER['SCRIPT_NAME'] ?? '';
            $segments = explode('/', trim($script, '/'));
            if (!empty($segments) && $segments[0] !== '') {
                // assume first segment is the base folder when app is in subfolder
                $base = '/' . $segments[0];
                // if it's index.php or a known file, ignore
                if (preg_match('/\.php$/', $base)) {
                    $base = '';
                }
            }
        }
        // Avoid duplicating base if path already starts with it
        // But do not prepend the inferred base for known root-absolute resource prefixes
        $skipPrefixes = ['/assets/', '/vendor/', '/static/', '/node_modules/'];
        $hasSkip = false;
        foreach ($skipPrefixes as $p) {
            if (strpos($path, $p) === 0) { $hasSkip = true; break; }
        }
        if ($base !== '' && !$hasSkip && strpos($path, $base . '/') !== 0) {
            $path = $base . $path;
        }

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
