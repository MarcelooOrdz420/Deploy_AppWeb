<?php

$body = file_get_contents('php://input') ?: '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST' || trim($body) === '') {
    http_response_code(200);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'OK';
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$hasIzipayPayload = false;

if (stripos($contentType, 'application/json') !== false) {
    $payload = json_decode($body, true);
    if (is_array($payload)) {
        $hasIzipayPayload = isset($payload['orderId'])
            || isset($payload['orderDetails']['orderId'])
            || isset($payload['answer']['orderId'])
            || isset($payload['answer']['orderDetails']['orderId'])
            || isset($payload['metadata']['tracking_code'])
            || isset($payload['answer']['metadata']['tracking_code']);
    }
} else {
    parse_str($body, $payload);
    $answer = $payload['kr-answer'] ?? null;
    if (is_string($answer) && trim($answer) !== '') {
        $decoded = json_decode($answer, true);
        $hasIzipayPayload = is_array($decoded) && (
            isset($decoded['orderId'])
            || isset($decoded['orderDetails']['orderId'])
            || isset($decoded['answer']['orderId'])
            || isset($decoded['answer']['orderDetails']['orderId'])
            || isset($decoded['metadata']['tracking_code'])
            || isset($decoded['answer']['metadata']['tracking_code'])
        );
    }
}

if (! $hasIzipayPayload) {
    http_response_code(200);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'OK';
    exit;
}

$_SERVER['REQUEST_URI'] = '/api/v1/payments/izipay/webhook';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/index.php';
