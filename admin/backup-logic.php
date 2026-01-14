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

// Build mysqldump command with safer options
$mysqldump = 'mysqldump';
$opts = [
    '--quick',
    '--single-transaction',
    '--routines',
    '--triggers',
    '--skip-lock-tables'
];

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

// Try to run the command
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
    // Log error to server logs and redirect back with message
    error_log('DB backup failed: ' . implode('\n', $output));
    header('Location: settings.php?message=backup_failed');
    exit;
}
?>
