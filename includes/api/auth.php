<?php
// Checks the authorization header for a valid API key for course partners
function require_course_partner_auth(): void
{
    header('Content-Type: application/json');
    $expected = getenv('COURSE_API_KEY');
    if (!$expected) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'api-key-not-configured']);
        exit;
    }

    $auth = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? (function () {
            if (function_exists('getallheaders')) {
                $headers = array_change_key_case(getallheaders(), CASE_LOWER);
                return $headers['authorization'] ?? '';
            }
            return '';
        })();
    if (stripos($auth, 'Bearer ') !== 0) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'missing-bearer-token']);
        exit;
    }

    $provided = trim(substr($auth, 7));
    if (!hash_equals($expected, $provided)) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'invalid-api-key']);
        exit;
    }
}
