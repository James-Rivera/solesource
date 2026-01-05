<?php
// Auto-advance order statuses for demo/testing (cron-safe)
// Usage: php scripts/auto-advance-orders.php

require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: application/json');

// Move pending -> confirmed after 2 minutes
$confirmed = $conn->query("UPDATE orders SET status = 'confirmed' WHERE status = 'pending' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 2");
$confirmedCount = $confirmed ? $conn->affected_rows : 0;

// Move confirmed -> shipped after 5 minutes; set mock tracking/courier if missing
$shipped = $conn->query("UPDATE orders SET status = 'shipped', tracking_number = COALESCE(tracking_number, CONCAT('MOCK-', LPAD(FLOOR(RAND()*999999), 6, '0'))), courier = COALESCE(courier, 'MockExpress') WHERE status = 'confirmed' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 5");
$shippedCount = $shipped ? $conn->affected_rows : 0;

// Move shipped -> delivered after 10 minutes
$delivered = $conn->query("UPDATE orders SET status = 'delivered' WHERE status = 'shipped' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 10");
$deliveredCount = $delivered ? $conn->affected_rows : 0;

echo json_encode([
    'ok' => true,
    'updated' => [
        'confirmed' => $confirmedCount,
        'shipped' => $shippedCount,
        'delivered' => $deliveredCount,
    ],
]);
