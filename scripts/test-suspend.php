<?php
require_once __DIR__ . '/../includes/connect.php';

$email = 'test_suspend@example.com';
$passwordPlain = 'Password1';

// Ensure user exists
$stmt = $conn->prepare('SELECT id, password, is_active FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    $hashed = password_hash($passwordPlain, PASSWORD_DEFAULT);
    $role = 'customer';
    $insert = $conn->prepare('INSERT INTO users (full_name, email, phone, password, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())');
    $full = 'Suspend Test User';
    $phone = '+639171234567';
    $insert->bind_param('sssss', $full, $email, $phone, $hashed, $role);
    if ($insert->execute()) {
        $userId = $insert->insert_id;
        echo "Created test user (#$userId)\n";
    } else {
        echo "Failed to create test user: " . $conn->error . "\n";
        exit(1);
    }
    $insert->close();
} else {
    $userId = (int)$user['id'];
    echo "Test user exists (#$userId)\n";
}

function attempt_login($conn, $email, $password) {
    $stmt = $conn->prepare('SELECT id, password, is_active FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $u = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$u) return ['ok'=>false,'msg'=>'no_user'];
    if (!password_verify($password, $u['password'])) return ['ok'=>false,'msg'=>'bad_password'];
    if (isset($u['is_active']) && (int)$u['is_active'] === 0) return ['ok'=>false,'msg'=>'suspended'];
    return ['ok'=>true,'msg'=>'logged_in'];
}

echo "Attempting login before suspension...\n";
$r1 = attempt_login($conn, $email, $passwordPlain);
var_export($r1);
echo "\n";

// Suspend user
$upd = $conn->prepare('UPDATE users SET is_active = 0 WHERE email = ?');
$upd->bind_param('s', $email);
$upd->execute();
$upd->close();

echo "User suspended.\n";

echo "Attempting login after suspension...\n";
$r2 = attempt_login($conn, $email, $passwordPlain);
var_export($r2);
echo "\n";

// Re-activate for cleanliness
$upd2 = $conn->prepare('UPDATE users SET is_active = 1 WHERE email = ?');
$upd2->bind_param('s', $email);
$upd2->execute();
$upd2->close();

echo "User reactivated. Test complete.\n";
