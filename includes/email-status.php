<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/connect.php';

$jobId = isset($_GET['job_id']) ? (int) $_GET['job_id'] : 0;
if ($jobId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'job_id required']);
    exit;
}

// Ensure table exists to avoid missing-table errors on early calls.
ensure_email_queue_table($conn);

$stmt = $conn->prepare('SELECT status, last_error, sent_at FROM email_queue WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $jobId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'job not found']);
    exit;
}

echo json_encode([
    'success' => true,
    'status' => $row['status'],
    'last_error' => $row['last_error'],
    'sent_at' => $row['sent_at'],
]);
