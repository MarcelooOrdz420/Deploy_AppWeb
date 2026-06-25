<?php

$body = file_get_contents('php://input') ?: '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || trim($body) === '') {
    http_response_code(200);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'OK';
    exit;
}

$_SERVER['REQUEST_URI'] = '/api/v1/payments/izipay/webhook';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/index.php';
