<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';

// Security: only admins may trigger a backup
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Use DB settings from includes/connect.php
$host = isset($dbhost) ? $dbhost : (getenv('DB_HOST') ?: 'localhost');
$username = isset($dbuser) ? $dbuser : (getenv('DB_USER') ?: 'root');
$password = isset($dbpass) ? $dbpass : (getenv('DB_PASS') ?: '');
$database = isset($db) ? $db : (getenv('DB_NAME') ?: 'solesource_db');

// Filename and path
$backup_file = 'solesource_backup_' . date('Y-m-d_H-i-s') . '.sql';
$backup_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $backup_file;


// Determine mysqldump executable. Try common locations (XAMPP on Windows, /usr/bin on Linux) then fallback to system path.
$possible = [
    // Windows XAMPP default
    'C:\\xampp\\mysql\\bin\\mysqldump.exe',
    // WAMP default
    'C:\\wamp64\\bin\\mysql\\mysql5.7.31\\bin\\mysqldump.exe',
    // Linux/macOS
    '/usr/bin/mysqldump',
    '/usr/local/bin/mysqldump',
    // Fallback to PATH
    'mysqldump'
];
$mysqldump = null;
foreach ($possible as $p) {
    if ($p === 'mysqldump') {
        // Last resort: assume in PATH
        $mysqldump = $p;
        break;
    }
    if (file_exists($p) && is_executable($p)) {
        $mysqldump = $p;
        break;
    }
}

if (!$mysqldump) {
    // Should not happen because we fallback to 'mysqldump', but keep a guard
    http_response_code(500);
    echo "<h1>Backup Error</h1><p>Could not locate <strong>mysqldump</strong> on this server.</p>";
    echo "<p>Try installing MySQL client utilities or configure the correct path.</p>";
    exit;
}

// Build mysqldump command with safer options
$opts = [
    '--quick',
    '--single-transaction',
    '--routines',
    '--triggers',
    '--skip-lock-tables'
];

// Construct command. Note: password is passed via --password= so escapeshellarg is used for safety.
// Construct command. Note: password is passed via --password= so escapeshellarg is used for safety.
$command = sprintf('%s --host=%s --user=%s --password=%s %s %s > %s',
    escapeshellcmd($mysqldump),
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($password),
    implode(' ', $opts),
    escapeshellarg($database),
    escapeshellarg($backup_path)
);

// Try to run the command and capture output for diagnostics
exec($command . ' 2>&1', $output, $result);

if ($result === 0 && file_exists($backup_path)) {
    // Stream the file to the browser for download
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
    header('Content-Length: ' . filesize($backup_path));
    flush();
    readfile($backup_path);
    // Clean up
    @unlink($backup_path);
    exit;
} else {
    // Show diagnostic information to the admin to help debugging
    http_response_code(500);
    error_log('DB backup failed: ' . implode("\n", $output));
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Backup Failed</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="p-4">
        <div class="container">
            <h1 class="h4 text-danger">Database Backup Failed</h1>
            <p class="small text-muted">The server was unable to produce a database dump. Details below may help diagnose the issue.</p>
            <div class="card mb-3"><div class="card-body"><pre><?php echo htmlspecialchars($command); ?></pre></div></div>
            <div class="card"><div class="card-body"><strong>Output:</strong>
                <pre><?php echo htmlspecialchars(implode("\n", $output)); ?></pre>
            </div></div>
            <a href="settings.php" class="btn btn-secondary mt-3">Back to Settings</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
