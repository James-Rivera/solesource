<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/ai-client.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$message = isset($input['message']) ? trim((string)$input['message']) : '';
$context = isset($input['context']) && is_array($input['context']) ? $input['context'] : [];

// Seed lightweight fallback knowledge so the bot can be helpful without live data
$context['kb'] = [
    'shipping' => 'Shipping typically takes 3-7 business days nationwide.',
    'returns' => 'All sales are final. Please verify sizing and condition before purchase.',
    'orderHelp' => 'Provide your order number and email to check status or update your address before shipment.',
    'topPicks' => [
        'Nike Air Force 1',
        'Adidas Ultraboost',
        'Jordan 1 Mid',
        'Asics Gel-Kayano'
    ],
];

// Add lightweight session context when available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!empty($_SESSION['user_id'])) {
    $context['user_id'] = (int)$_SESSION['user_id'];
    if (!empty($_SESSION['user_name'])) {
        $context['user_name'] = (string)$_SESSION['user_name'];
    }
    if (!empty($_SESSION['user_email'])) {
        $context['user_email'] = (string)$_SESSION['user_email'];
    }
}

if ($message === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'empty_message']);
    exit;
}

// --- Add: lightweight product lookup so AI can reference inventory ---
$stockCol = null; // declared here so update block can reuse
$searchTerm = trim(preg_replace('/[^a-z0-9 ]/i', ' ', $message));
if ($searchTerm !== '') {
    $matches = [];
    $colCandidates = ['stock_quantity', 'stock_qty', 'stock', 'qty', 'quantity', 'inventory'];
    if (isset($conn) && $conn) {
        // detect a suitable stock column if it exists
        $colsRes = @$conn->query("SHOW COLUMNS FROM products");
        if ($colsRes) {
            while ($c = $colsRes->fetch_assoc()) {
                $f = $c['Field'] ?? null;
                if ($f && in_array($f, $colCandidates, true)) {
                    $stockCol = $f;
                    break;
                }
            }
            $colsRes->close();
        }

        // Build keyword list from message (remove short/common words)
        $rawWords = preg_split('/\s+/', trim($searchTerm));
        $stop = ['the','and','for','with','have','has','do','we','is','are','in','on','of','a','an','it','this','that','to','from','by','be','stock'];
        $keywords = [];
        foreach ($rawWords as $w) {
            $w = trim($w);
            if ($w === '') continue;
            $lw = strtolower($w);
            if (in_array($lw, $stop, true)) continue;
            if (strlen($lw) < 3) continue;
            $keywords[] = $lw;
        }
        $keywords = array_values(array_unique($keywords));

        // If no keywords found, fall back to whole phrase
        if (empty($keywords)) $keywords = [strtolower(trim($searchTerm))];

        $selectCols = 'id, sku, name, brand, status';
        if ($stockCol) {
            $selectCols .= ', ' . $stockCol . ' AS stock_qty';
        }

        // Build dynamic WHERE with per-keyword clauses (use OR between keywords)
        $whereParts = [];
        $params = [];
        foreach ($keywords as $kw) {
            $whereParts[] = '(LOWER(name) LIKE ? OR LOWER(brand) LIKE ? OR LOWER(sku) LIKE ?)';
            $like = '%' . $kw . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        // Use OR so rows matching any keyword are returned (better for natural language queries)
        $whereSql = implode(' OR ', $whereParts);
        $sql = "SELECT $selectCols FROM products WHERE $whereSql LIMIT 8";
        $stmt = @$conn->prepare($sql);
        if ($stmt) {
            // bind params dynamically (all strings)
            $types = str_repeat('s', count($params));
            $bindNames = array();
            $bindNames[] = $types;
            foreach ($params as $k => $v) {
                $bindNames[] = &$params[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindNames);
            $stmt->execute();
            $res = $stmt->get_result();
                while ($r = $res->fetch_assoc()) {
                $matches[] = [
                    'id' => (int)$r['id'],
                    'sku' => isset($r['sku']) ? $r['sku'] : null,
                    'name' => $r['name'],
                    'brand' => $r['brand'],
                    'status' => $r['status'],
                    'stock_qty' => isset($r['stock_qty']) ? (int)$r['stock_qty'] : null,
                ];
            }
            $stmt->close();
        }
        // If no matches but message looks like an exact SKU, try exact SKU
        if (empty($matches)) {
            $maybeSku = strtoupper(preg_replace('/[^A-Z0-9\-]/i','', $message));
            if ($maybeSku !== '') {
                $q = "SELECT $selectCols FROM products WHERE UPPER(sku) = ? LIMIT 1";
                $s2 = @$conn->prepare($q);
                if ($s2) {
                    $s2->bind_param('s', $maybeSku);
                    $s2->execute();
                    $r2 = $s2->get_result()->fetch_assoc();
                        if ($r2) {
                        $matches[] = [
                            'id' => (int)$r2['id'],
                            'sku' => isset($r2['sku']) ? $r2['sku'] : null,
                            'name' => $r2['name'],
                            'brand' => $r2['brand'],
                            'status' => $r2['status'],
                            'stock_qty' => isset($r2['stock_qty']) ? (int)$r2['stock_qty'] : null,
                        ];
                    }
                    $s2->close();
                }
            }
        }
        // Phrase fallback: try a simple LIKE on the whole cleaned message
        if (empty($matches)) {
            $phrase = preg_replace('/[^a-z0-9 ]/i', ' ', $message);
            $phrase = trim(preg_replace('/\s+/', ' ', $phrase));
            if ($phrase !== '') {
                $likePhrase = '%' . strtolower($phrase) . '%';
                $q2 = "SELECT $selectCols FROM products WHERE LOWER(name) LIKE ? OR LOWER(brand) LIKE ? OR LOWER(sku) LIKE ? LIMIT 6";
                $s3 = @$conn->prepare($q2);
                if ($s3) {
                    $s3->bind_param('sss', $likePhrase, $likePhrase, $likePhrase);
                    $s3->execute();
                    $res3 = $s3->get_result();
                    while ($r3 = $res3->fetch_assoc()) {
                        $matches[] = [
                            'id' => (int)$r3['id'],
                            'sku' => isset($r3['sku']) ? $r3['sku'] : null,
                            'name' => $r3['name'],
                            'brand' => $r3['brand'],
                            'status' => $r3['status'],
                            'stock_qty' => isset($r3['stock_qty']) ? (int)$r3['stock_qty'] : null,
                        ];
                    }
                    $s3->close();
                }
            }
        }
    }
    if (!empty($matches)) {
        $context['inventory_matches'] = $matches;
        // also expose a human-friendly summary text to encourage the model to use it
        $summary = [];
        foreach ($matches as $m) {
            $line = trim(($m['name'] ?? 'Unnamed'));
            if (!empty($m['sku'])) $line .= ' (SKU: ' . $m['sku'] . ')';
            $line .= ' — Brand: ' . ($m['brand'] ?? 'Unknown');
            $line .= ' — Status: ' . ($m['status'] ?? 'unknown');
            $line .= ' — Stock: ' . (is_null($m['stock_qty']) ? 'unknown' : (string)$m['stock_qty']);
            $summary[] = $line;
        }
        $context['inventory_summary'] = $summary;
        $context['inventory_summary_text'] = implode("\n", $summary);
    }
}

$result = ai_complete($message, $context);

if (!$result['ok']) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $result['error'] ?? 'unknown_error',
        'raw' => $result['raw'] ?? null,
    ]);
    exit;
}
// If AI suggested actions, optionally validate/apply them server-side (safe, whitelisted)
$applied = [];
$applyRequested = (!empty($input['apply_actions']) && $input['apply_actions']);
$allowWrite = getenv('ALLOW_AI_DB_WRITE') === '1';
// Allow cart-only writes if configured separately
$allowCartWrite = (getenv('ALLOW_AI_CART_WRITE') === '1') || $allowWrite;
if (!empty($result['data']['actions']) && is_array($result['data']['actions'])) {
    // audit log AI suggested actions (append-only)
    try {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        $logFile = $logDir . '/ai_actions.log';
        $entry = [
            'ts' => date('c'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'actions' => $result['data']['actions'],
            'inventory_summary' => $context['inventory_summary'] ?? null,
        ];
        @file_put_contents($logFile, json_encode($entry, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    } catch (Exception $e) {
        // ignore logging failures
    }

    foreach ($result['data']['actions'] as $act) {
        if (!is_array($act) || empty($act['type'])) continue;
        $type = $act['type'];
        if ($type === 'update_inventory') {
            $pid = isset($act['product_id']) ? (int)$act['product_id'] : 0;
            $setQty = isset($act['set_qty']) ? $act['set_qty'] : null;
            $change = isset($act['change']) ? $act['change'] : null;
            if ($pid <= 0) {
                $applied[] = ['type' => 'update_inventory', 'product_id' => $pid, 'status' => 'invalid_product_id'];
                continue;
            }
            if ($setQty === null && $change === null) {
                $applied[] = ['type' => 'update_inventory', 'product_id' => $pid, 'status' => 'missing_qty_or_change'];
                continue;
            }

            // Determine new quantity
            $current = null;
            if ($change !== null || $setQty === null) {
                if (!empty($stockCol)) {
                    $q = "SELECT " . $stockCol . " AS stock_qty FROM products WHERE id = ? LIMIT 1";
                    $stmt = @$conn->prepare($q);
                    if ($stmt) {
                        $stmt->bind_param('i', $pid);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $r = $res->fetch_assoc();
                        $current = $r ? (int)$r['stock_qty'] : null;
                        $stmt->close();
                    }
                } else {
                    // no stock column detected; cannot compute change
                    $current = null;
                }
            }

            if ($setQty !== null) {
                $newQty = (int)$setQty;
            } else {
                if ($current === null) {
                    $applied[] = ['type' => 'update_inventory', 'product_id' => $pid, 'status' => 'product_not_found'];
                    continue;
                }
                $newQty = $current + (int)$change;
                if ($newQty < 0) $newQty = 0;
            }

            if ($applyRequested && $allowWrite) {
                if (empty($stockCol)) {
                    $applied[] = ['type' => 'update_inventory', 'product_id' => $pid, 'status' => 'no_stock_column', 'new_qty' => $newQty];
                } else {
                    // use the detected stock column name for updates
                    $col = $stockCol;
                    $u = $conn->prepare("UPDATE products SET {$col} = ? WHERE id = ?");
                    if ($u) {
                        $u->bind_param('ii', $newQty, $pid);
                        $ok = $u->execute();
                        $u->close();
                        $applied[] = ['type' => 'update_inventory', 'product_id' => $pid, 'status' => $ok ? 'applied' : 'failed', 'new_qty' => $newQty];
                    } else {
                        $applied[] = ['type' => 'update_inventory', 'product_id' => $pid, 'status' => 'prepare_failed', 'new_qty' => $newQty];
                    }
                }
            } else {
                $applied[] = ['type' => 'update_inventory', 'product_id' => $pid, 'status' => $allowWrite ? 'preview' : 'write_disabled', 'new_qty' => $newQty];
            }
        } elseif ($type === 'add_to_cart') {
            $pid = isset($act['product_id']) ? (int)$act['product_id'] : 0;
            $qty = isset($act['qty']) ? max(1, (int)$act['qty']) : 1;
            $sizeId = isset($act['size_id']) && $act['size_id'] !== '' ? (int)$act['size_id'] : null;
            $size = isset($act['size']) ? (string)$act['size'] : '';

            if ($pid <= 0) {
                $applied[] = ['type' => 'add_to_cart', 'product_id' => $pid, 'status' => 'invalid_product_id'];
                continue;
            }

            // If apply requested and cart-writes (or full writes) allowed, call the trusted endpoint
            $actionSecret = getenv('AI_ACTIONS_SECRET') ?: '';
            if ($applyRequested && $allowCartWrite && $actionSecret !== '') {
                $endpoint = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://localhost/solesource/includes/ai-actions/apply-cart-add.php';
                $payload = json_encode(['actions' => [
                    [
                        'type' => 'add_to_cart',
                        'product_id' => $pid,
                        'qty' => $qty,
                        'size_id' => $sizeId,
                        'size' => $size,
                    ]
                ]]);

                $ch = curl_init($endpoint);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'X-AI-ACTIONS-SECRET: ' . $actionSecret,
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $resp = curl_exec($ch);
                $err = curl_error($ch);
                $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($resp !== false) {
                    $decoded = @json_decode($resp, true);
                    $applied[] = ['type' => 'add_to_cart', 'product_id' => $pid, 'status' => ($decoded['ok'] ? 'applied' : 'failed'), 'response' => $decoded ?? $resp, 'http_code' => $http];
                } else {
                    $applied[] = ['type' => 'add_to_cart', 'product_id' => $pid, 'status' => 'curl_error', 'error' => $err];
                }
            } else {
                $applied[] = ['type' => 'add_to_cart', 'product_id' => $pid, 'status' => $allowCartWrite ? 'preview' : 'write_disabled'];
            }
        } else {
            // other whitelisted action types can be added here (e.g., setValue is UI-only)
            $applied[] = ['type' => $type, 'status' => 'ignored_not_actionable_server_side'];
        }
    }
}

echo json_encode([
    'ok' => true,
    'data' => $result['data'],
    'applied_actions' => $applied,
    'sent_context' => (!empty($input['debug_context']) ? $context : null),
]);
