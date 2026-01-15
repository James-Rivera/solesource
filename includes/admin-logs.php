<?php
/**
 * Simple admin audit log helper.
 * Creates `admin_logs` table when needed and writes entries.
 */
function ensure_admin_logs_table($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NULL,
        action VARCHAR(100) NOT NULL,
        meta JSON NULL,
        ip VARCHAR(45) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    // Try to create table; ignore errors
    @$conn->query($sql);
}

function log_admin_action($conn, $adminId, $action, $meta = [])
{
    ensure_admin_logs_table($conn);
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $metaJson = null;
    if (!empty($meta)) {
        $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE);
    }

    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, meta, ip) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('isss', $adminId, $action, $metaJson, $ip);
        $stmt->execute();
        $stmt->close();
    } else {
        // fallback: log to PHP error log if DB insert not available
        error_log('Admin log failed to prepare statement: ' . $conn->error . ' | action=' . $action);
        error_log('Admin log meta: ' . print_r($meta, true));
    }
}
