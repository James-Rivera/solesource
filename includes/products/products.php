<?php
// Centralized products provider (DB-backed) for legacy consumers.
// NOTE: do not close the shared $conn here; it may be reused by callers.

require_once __DIR__ . '/../connect.php';

$all_products = [];

$sql = "SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format price for frontend expectations (₱ + thousands + 2 decimals)
        $row['price'] = '₱' . number_format((float)$row['price'], 2, '.', ',');
        $all_products[] = $row;
    }
}
?>