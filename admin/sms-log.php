<?php
session_start();
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
$logFile = __DIR__ . '/../logs/sms_log.txt';
$lines = file_exists($logFile) ? array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>SMS Log</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
    <nav class="mb-4">
        <a href="index.php" class="btn btn-outline-primary">&larr; Back to Dashboard</a>
    </nav>
    <h1 class="mb-4">SMS Log</h1>
    <div class="table-responsive">
    <table class="table table-dark table-striped table-bordered align-middle responsive-admin">
        <thead>
            <tr>
                <th>Date/Time</th>
                <th>Direction</th>
                <th>Phone</th>
                <th>Message / Status</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($lines as $line) {
            if (!preg_match('/^\[(.*?)\] (.+)$/', $line, $m)) continue;
            $dt = $m[1];
            $data = json_decode($m[2], true);
            $dir = $data['direction'] ?? '';
            $phone = '';
            $msg = '';
            $details = '';
            if ($dir === 'inbound') {
                $raw = $data['raw'] ?? [];
                $phone = $raw['from'] ?? '';
                $msg = $raw['text'] ?? '';
                $details = '';
            } elseif ($dir === 'outbound') {
                $req = $data['request'] ?? [];
                $phone = is_array($req['phoneNumbers'] ?? null) ? implode(', ', $req['phoneNumbers']) : '';
                $msg = $req['message'] ?? '';
                $details = 'Status: ' . ($data['status'] ?? '') . '<br>Resp: ' . htmlspecialchars($data['response'] ?? '');
            } elseif ($dir === 'error') {
                $msg = 'ERROR: ' . ($data['error'] ?? '');
            }
            echo '<tr>';
            echo '<td data-label="Date/Time">' . htmlspecialchars($dt) . '</td>';
            echo '<td data-label="Direction">' . htmlspecialchars($dir) . '</td>';
            echo '<td data-label="Phone">' . htmlspecialchars($phone) . '</td>';
            echo '<td data-label="Message / Status">' . htmlspecialchars($msg) . '</td>';
            echo '<td data-label="Details">' . $details . '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
    </div>
</body>
</html>
