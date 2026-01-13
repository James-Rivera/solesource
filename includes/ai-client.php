<?php
require_once __DIR__ . '/env.php';

/**
 * Unified AI completion client for hosted APIs (OpenAI/Gemini) and Ollama.
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
    $geminiKey = getenv('GEMINI_API_KEY') ?: ($provider === 'gemini' ? $apiKey : '');
    $model = getenv('AI_MODEL') ?: 'gpt-4o-mini';
    $baseUrl = getenv('OLLAMA_BASE_URL') ?: 'http://localhost:11434';
    $systemPrompt = getenv('AI_SYSTEM_PROMPT') ?: 'You are a SoleSource support assistant. Answer only about SoleSource site usage, orders, shipping, returns, or products. If asked anything else, politely refuse. If Context.inventory_summary_text is present, you MUST use it and include its contents (or a concise summary) in your reply; do NOT claim you have no inventory data. Use the provided context (Context.inventory_matches) for facts when relevant. Respond as strict JSON with this shape: {"reply": string, "actions": [{"type": "setValue" | "update_inventory", "selector"?: string, "value"?: string, "product_id"?: int, "set_qty"?: int, "change"?: int}], "faqs": [string]}. Allowed selectors: input[name="email"], input[name="phone"], input[name="full_name"], textarea[name="message"], input[name="order_number"]. For inventory changes, use action.type="update_inventory" and include product_id and either set_qty or change. Never attempt to modify orders or financial data. Always include reply. actions is optional.';

    if ($provider === 'api' && $apiKey === '') {
        return ['ok' => false, 'error' => 'missing_api_key', 'data' => null, 'raw' => null];
    }
    if ($provider === 'gemini' && $geminiKey === '') {
        return ['ok' => false, 'error' => 'missing_api_key', 'data' => null, 'raw' => null];
    }

    // Build messages: system prompt, then inventory summary (if present) so model MUST use it, then user message
    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
    ];

    if (!empty($context['inventory_summary_text'])) {
        // prioritize inventory summary so the model sees it before the user's query
        $messages[] = [
            'role' => 'user',
            'content' => "Inventory data:\n" . $context['inventory_summary_text'],
        ];
    }

    $messages[] = ['role' => 'user', 'content' => $message];

    // Optional contextual hints appended as a tool-free message (kept after user message)
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
    } elseif ($provider === 'gemini') {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . urlencode($geminiKey);
        $userText = $message . (!empty($context) ? "\nContext: " . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '');
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [ ['text' => $userText] ],
                ],
            ],
            'systemInstruction' => [
                'parts' => [ ['text' => $systemPrompt] ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
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

    // Transport with simple retry/backoff (2 attempts)
    $attempts = 0;
    $raw = null;
    $err = null;
    $httpCode = 0;
    $maxAttempts = 2;
    while ($attempts < $maxAttempts) {
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

        if (empty($err) && $httpCode >= 200 && $httpCode < 500) {
            break; // success or application-level error; stop retrying
        }

        $attempts++;
        if ($attempts < $maxAttempts) {
            // small backoff
            sleep(1);
        }
    }

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
    } elseif ($provider === 'gemini') {
        $content = $res['candidates'][0]['content']['parts'][0]['text'] ?? null;
    } else {
        $content = $res['choices'][0]['message']['content'] ?? null;
    }

    if (!is_string($content) || $content === '') {
        return ['ok' => false, 'error' => 'missing_content', 'data' => null, 'raw' => $raw];
    }

    $parsed = json_decode($content, true);
    if (!is_array($parsed)) {
        // Allow plain text responses as a fallback
        return [
            'ok' => true,
            'data' => ['reply' => $content],
            'error' => null,
            'raw' => $content,
        ];
    }

    // Basic sanitation of actions (structure only). Defer DB writes to caller.
    if (!empty($parsed['actions']) && is_array($parsed['actions'])) {
        $sanitized = [];
        foreach ($parsed['actions'] as $act) {
            if (!is_array($act) || empty($act['type'])) continue;
            $type = (string)$act['type'];
            if ($type === 'setValue') {
                $sanitized[] = [
                    'type' => 'setValue',
                    'selector' => isset($act['selector']) ? (string)$act['selector'] : null,
                    'value' => isset($act['value']) ? (string)$act['value'] : null,
                ];
            } elseif ($type === 'update_inventory') {
                $sanitized[] = [
                    'type' => 'update_inventory',
                    'product_id' => isset($act['product_id']) ? (int)$act['product_id'] : null,
                    'set_qty' => isset($act['set_qty']) ? (int)$act['set_qty'] : null,
                    'change' => isset($act['change']) ? (int)$act['change'] : null,
                ];
            } elseif ($type === 'add_to_cart') {
                $sanitized[] = [
                    'type' => 'add_to_cart',
                    'product_id' => isset($act['product_id']) ? (int)$act['product_id'] : null,
                    'qty' => isset($act['qty']) ? max(1, (int)$act['qty']) : 1,
                    'size_id' => isset($act['size_id']) ? (int)$act['size_id'] : null,
                    'size' => isset($act['size']) ? (string)$act['size'] : '',
                ];
            }
        }
        $parsed['actions'] = $sanitized;
    }

    return ['ok' => true, 'data' => $parsed, 'error' => null, 'raw' => $content];
}

?>
