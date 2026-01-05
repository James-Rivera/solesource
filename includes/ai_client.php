<?php
require_once __DIR__ . '/env.php';

/**
 * Unified AI completion client for hosted APIs and Ollama.
 * Returns an array: ['ok' => bool, 'data' => array|null, 'error' => string|null, 'raw' => string|null]
 */
function ai_complete(string $userMessage, array $context = []): array
{
    $message = trim($userMessage);
    if ($message === '') {
        return ['ok' => false, 'error' => 'empty_message', 'data' => null, 'raw' => null];
    }

    $provider = getenv('AI_PROVIDER') ?: 'api';
    $apiKey = getenv('AI_API_KEY') ?: '';
    $model = getenv('AI_MODEL') ?: 'gpt-4o-mini';
    $baseUrl = getenv('OLLAMA_BASE_URL') ?: 'http://localhost:11434';
    $systemPrompt = getenv('AI_SYSTEM_PROMPT') ?: 'You are a SoleSource support assistant. Answer only about SoleSource site usage, orders, shipping, returns, or products. If asked anything else, politely refuse.';

    if ($provider !== 'ollama' && $apiKey === '') {
        return ['ok' => false, 'error' => 'missing_api_key', 'data' => null, 'raw' => null];
    }

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $message],
    ];

    // Optional contextual hints appended as a tool-free message
    if (!empty($context)) {
        $messages[] = [
            'role' => 'user',
            'content' => 'Context: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];
    }

    if ($provider === 'ollama') {
        $url = rtrim($baseUrl, '/') . '/api/chat';
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
        ];
        $headers = ['Content-Type: application/json'];
    } else {
        $url = 'https://api.openai.com/v1/chat/completions';
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'response_format' => ['type' => 'json_object'],
        ];
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        return ['ok' => false, 'error' => 'transport_failed', 'data' => null, 'raw' => null];
    }

    $res = json_decode($raw, true);
    if (!is_array($res)) {
        return ['ok' => false, 'error' => 'invalid_json', 'data' => null, 'raw' => $raw];
    }

    $content = null;
    if ($provider === 'ollama') {
        $content = $res['message']['content'] ?? null;
    } else {
        $content = $res['choices'][0]['message']['content'] ?? null;
    }

    if (!is_string($content) || $content === '') {
        return ['ok' => false, 'error' => 'missing_content', 'data' => null, 'raw' => $raw];
    }

    $parsed = json_decode($content, true);
    if (!is_array($parsed)) {
        // Allow non-JSON content to still be returned raw for UI fallback
        return ['ok' => false, 'error' => 'invalid_response', 'data' => null, 'raw' => $content, 'status' => $httpCode];
    }

    return ['ok' => true, 'data' => $parsed, 'error' => null, 'raw' => $content];
}

?>
