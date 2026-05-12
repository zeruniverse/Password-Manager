<?php

require_once dirname(__FILE__) . '/cors.php';

pm_send_cors_headers();

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

const ERROR_MESSAGE = [
    'general' => 'Oops, our server run into some problems. Please refresh this page and try again.',
    'config' => 'PLEASE CONFIG function/config.php before using this system!',
    'database' => 'Database is not configured.',
    'PINunavailable' => 'No PIN available',
    'PINwrong' => 'Wrong PIN',
    'authentication' => 'AUTHENTICATION ERROR, PLEASE RELOGIN',
    'parameter' => 'Parameter Error',
    'session' => 'session unavailable',
    'token' => 'Invalid session token.',
    'origin' => 'Request origin is not allowed.',
    'blockIP' => 'Your IP has been blocked due to malicious activity',
    'blockAccount' => 'Your account has been protected due to continuous attack.',
    'loginFailed' => 'Username password combination is unknown',
    'entryNotFound' => 'Entry not found',
    'updateFailed' => 'Updating entry failed',
    'signup' => 'Signup is not allowed.',
    'invalidEmail' => 'Invalid E-mail address.',
    'occupiedUser' => 'User name already occupied, please choose another user name.',
    'occupiedEmail' => 'This E-mail has already been used.',
    'fileFailed' => 'No File Can Be Downloaded',
    'userWrong' => 'Wrong User',
    'method' => 'Method not allowed',
    'TotpVerify' => 'Please enter the 6-digit code from your authenticator app (or secret).',
    'TotpWrong' => 'Wrong 2FA code',
    'input' => 'Invalid input.',
    'internal' => 'Internal error.',
];

function ajaxError($message, $payload = [])
{
    $text = isset(ERROR_MESSAGE[$message]) ? ERROR_MESSAGE[$message] : $message;

    echo json_encode(array_merge([
        'status' => 'error',
        'message' => $text,
        'error' => $message,
    ], $payload), JSON_UNESCAPED_SLASHES);

    exit();
}

function error($msg)
{
    echo json_encode([
        'status' => 'error',
        'message' => $msg,
    ], JSON_UNESCAPED_SLASHES);

    exit();
}

function ajaxSuccess($payload = [])
{
    if (!is_array($payload)) {
        $payload = ['data' => $payload];
    }

    if (function_exists('session_id') && session_id() !== '') {
        $payload['api_session_id'] = session_id();
    }

    echo json_encode(array_merge([
        'status' => 'success',
    ], $payload), JSON_UNESCAPED_SLASHES);

    exit();
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (!pm_is_allowed_request_origin()) {
        http_response_code(403);
        ajaxError('origin');
    }

    http_response_code(204);
    exit();
}

if (!pm_is_allowed_request_origin()) {
    http_response_code(403);
    ajaxError('origin');
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ajaxError('method');
}