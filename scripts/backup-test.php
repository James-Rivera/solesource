<?php
require_once __DIR__ . '/../includes/connect.php';

$host = isset($dbhost) ? $dbhost : (getenv('DB_HOST') ?: 'localhost');
$username = isset($dbuser) ? $dbuser : (getenv('DB_USER') ?: 'root');
$password = isset($dbpass) ? $dbpass : (getenv('DB_PASS') ?: '');
$database = isset($db) ? $db : (getenv('DB_NAME') ?: 'solesource_db');

$backup_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_backup.sql';
$mysqldump = 'mysqldump';
$opts = [
    '--quick',
    '--single-transaction',
    '--routines',
    '--triggers',
    '--skip-lock-tables'
];

$command = sprintf('%s --host=%s --user=%s --password=%s %s %s > %s',
    escapeshellcmd($mysqldump),
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($password),
    implode(' ', $opts),
    escapeshellarg($database),
    escapeshellarg($backup_path)
);

echo $command . PHP_EOL;
