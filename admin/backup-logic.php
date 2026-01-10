<?php
session_start();

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sis';

// Generate backup filename with timestamp
$backup_file = 'solesource_backup_' . date('Y-m-d_H-i-s') . '.sql';
$backup_path = sys_get_temp_dir() . '/' . $backup_file;

try {
    // Create mysqldump command
    $command = sprintf(
        'mysqldump --user=%s --password=%s --host=%s %s > %s',
        escapeshellarg($username),
        escapeshellarg($password),
        escapeshellarg($host),
        escapeshellarg($database),
        escapeshellarg($backup_path)
    );

    // Execute backup
    exec($command, $output, $result);

    if ($result === 0 && file_exists($backup_path)) {
        // Force download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup_file . '"');
        header('Content-Length: ' . filesize($backup_path));
        readfile($backup_path);
        
        // Clean up temp file
        unlink($backup_path);
        exit;
    } else {
        // Backup failed
        header('Location: settings.php?message=backup_failed');
        exit;
    }
} catch (Exception $e) {
    // Error handling
    header('Location: settings.php?message=backup_error');
    exit;
}
?>
