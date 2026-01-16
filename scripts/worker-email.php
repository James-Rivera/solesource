<?php
// Simple email queue worker. Run via cron: php scripts/worker-email.php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "CLI only";
    exit;
}

require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/mailer.php';

// Log helper
function worker_log(string $msg): void
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/worker-email.log';
    @file_put_contents($logFile, '[' . date('c') . '] ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Quick SMTP reachability check using configured mailer host and port.
 * Returns true when a TCP connection can be opened.
 */
function smtp_is_reachable(int $timeout = 5): bool
{
    try {
        $cfg = mailer_config();
    } catch (Throwable $e) {
        worker_log('mailer_config error: ' . $e->getMessage());
        return false;
    }

    $host = $cfg['host'] ?? '';
    $port = isset($cfg['port']) ? (int) $cfg['port'] : 25;
    // strip scheme if present
    $host = preg_replace('#^[a-z0-9+\-\.]+://#i', '', $host);
    if ($host === '') {
        return false;
    }

    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    worker_log(sprintf('SMTP unreachable (%s:%d) errno=%s err=%s', $host, $port, $errno ?? 'N/A', $errstr ?? ''));
    return false;
}

$batchSize = getenv('EMAIL_QUEUE_BATCH') ? (int) getenv('EMAIL_QUEUE_BATCH') : 10;
$maxAttempts = getenv('EMAIL_QUEUE_MAX_ATTEMPTS') ? (int) getenv('EMAIL_QUEUE_MAX_ATTEMPTS') : 3;
$batchSize = $batchSize > 0 ? $batchSize : 10;
$maxAttempts = $maxAttempts > 0 ? $maxAttempts : 3;

ensure_email_queue_table($conn);

// If SMTP is temporarily unreachable (e.g. mobile data not yet ready), retry a few times with short backoff,
// otherwise bail cleanly so Task Scheduler can try again later.
$smtpRetries = getenv('WORKER_SMTP_RETRIES') ? (int) getenv('WORKER_SMTP_RETRIES') : 3;
$smtpDelay = getenv('WORKER_SMTP_DELAY') ? (int) getenv('WORKER_SMTP_DELAY') : 5; // seconds
$ok = false;
for ($i = 0; $i < $smtpRetries; $i++) {
    if (smtp_is_reachable(5)) {
        $ok = true;
        break;
    }
    // short sleep before retry
    sleep($smtpDelay);
}
if (!$ok) {
    worker_log('SMTP unreachable after retries, exiting worker early');
    echo "SMTP unreachable, exiting\n";
    exit(0);
}

$stmt = $conn->prepare("SELECT id, recipient, subject, body_html, body_text, embedded_json, attempts FROM email_queue WHERE status = 'queued' ORDER BY id ASC LIMIT ?");
$stmt->bind_param('i', $batchSize);
$stmt->execute();
$result = $stmt->get_result();
$jobs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();

if (empty($jobs)) {
    echo "No queued emails" . PHP_EOL;
    exit;
}

foreach ($jobs as $job) {
    $id = (int) $job['id'];

    // Try to mark as sending to avoid double work
    $mark = $conn->prepare("UPDATE email_queue SET status = 'sending', attempts = attempts + 1 WHERE id = ? AND status = 'queued'");
    $mark->bind_param('i', $id);
    $mark->execute();
    $affected = $mark->affected_rows;
    $mark->close();

    if ($affected === 0) {
        continue; // Already taken by another worker
    }

    $embedded = [];
    if (!empty($job['embedded_json'])) {
        $decoded = json_decode($job['embedded_json'], true);
        if (is_array($decoded)) {
            $embedded = $decoded;
        }
    }

    $result = sendEmail(
        $job['recipient'],
        $job['subject'],
        $job['body_html'],
        $job['body_text'] ?? '',
        $embedded
    );

    if ($result === true) {
        $done = $conn->prepare("UPDATE email_queue SET status = 'sent', sent_at = NOW(), last_error = NULL WHERE id = ?");
        $done->bind_param('i', $id);
        $done->execute();
        $done->close();
        echo "[sent] #{$id} to {$job['recipient']}" . PHP_EOL;
    } else {
        $error = is_string($result) ? $result : 'Unknown error';
        $attemptsNow = (int) $job['attempts'] + 1;
        $failStatus = ($attemptsNow >= $maxAttempts) ? 'failed' : 'queued';
        $fail = $conn->prepare("UPDATE email_queue SET status = ?, last_error = ? WHERE id = ?");
        $fail->bind_param('ssi', $failStatus, $error, $id);
        $fail->execute();
        $fail->close();

        echo "[error] #{$id} to {$job['recipient']}: {$error}" . PHP_EOL;
    }
}
