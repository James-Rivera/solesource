<?php

declare(strict_types=1);

/**
 * Remove expired password reset links proactively so only valid tokens remain.
 */
function prune_password_reset_tokens(mysqli $conn): void
{
    $conn->query('DELETE FROM password_resets WHERE expires_at < UTC_TIMESTAMP()');
}

function create_password_reset_token(mysqli $conn, string $email, int $ttlMinutes = 60): ?string
{
    $email = trim($email);
    if ($email === '') {
        return null;
    }

    prune_password_reset_tokens($conn);

    if ($delete = $conn->prepare('DELETE FROM password_resets WHERE email = ?')) {
        $delete->bind_param('s', $email);
        $delete->execute();
        $delete->close();
    }

    try {
        $token = bin2hex(random_bytes(32));
    } catch (\Throwable $e) {
        return null;
    }

    $expiresAt = gmdate('Y-m-d H:i:s', time() + ($ttlMinutes * 60));
    $tokenHash = hash('sha256', $token);

    $stmt = $conn->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('sss', $email, $tokenHash, $expiresAt);
    $saved = $stmt->execute();
    $stmt->close();

    return $saved ? $token : null;
}

function validate_password_reset_token(mysqli $conn, string $token): ?array
{
    $token = trim($token);
    if ($token === '') {
        return null;
    }

    prune_password_reset_tokens($conn);

    $tokenHash = hash('sha256', $token);
    $stmt = $conn->prepare('SELECT email FROM password_resets WHERE token = ? AND expires_at >= UTC_TIMESTAMP() LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $tokenHash);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        return null;
    }

    return $row;
}

function consume_password_reset_token(mysqli $conn, string $email): void
{
    $email = trim($email);
    if ($email === '') {
        return;
    }

    $stmt = $conn->prepare('DELETE FROM password_resets WHERE email = ?');
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
    }
}
