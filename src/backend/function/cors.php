<?php

require_once dirname(__FILE__) . '/config.php';

function pm_normalize_origin($url)
{
    $url = trim((string) $url);

    if ($url === '') {
        return '';
    }

    $parts = parse_url($url);

    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
        return '';
    }

    $scheme = strtolower($parts['scheme']);
    $host = strtolower($parts['host']);

    if ($scheme !== 'https' && $scheme !== 'http') {
        return '';
    }

    $origin = $scheme . '://' . $host;

    if (isset($parts['port'])) {
        $port = (int) $parts['port'];
        $isDefaultHttp = ($scheme === 'http' && $port === 80);
        $isDefaultHttps = ($scheme === 'https' && $port === 443);

        if (!$isDefaultHttp && !$isDefaultHttps) {
            $origin .= ':' . $port;
        }
    }

    return $origin;
}

function pm_frontend_origin()
{
    global $FRONTEND_URL;

    return pm_normalize_origin(isset($FRONTEND_URL) ? $FRONTEND_URL : '');
}

function pm_request_origin()
{
    return isset($_SERVER['HTTP_ORIGIN'])
        ? pm_normalize_origin($_SERVER['HTTP_ORIGIN'])
        : '';
}

function pm_is_allowed_request_origin()
{
    global $ALLOW_NO_ORIGIN_REQUESTS;

    $rawOrigin = isset($_SERVER['HTTP_ORIGIN'])
        ? trim((string) $_SERVER['HTTP_ORIGIN'])
        : '';

    if ($rawOrigin === '') {
        return !empty($ALLOW_NO_ORIGIN_REQUESTS);
    }

    $requestOrigin = pm_normalize_origin($rawOrigin);
    $frontendOrigin = pm_frontend_origin();

    return $requestOrigin !== '' &&
        $frontendOrigin !== '' &&
        hash_equals($frontendOrigin, $requestOrigin);
}

function pm_send_cors_headers()
{
    $rawOrigin = isset($_SERVER['HTTP_ORIGIN'])
        ? trim((string) $_SERVER['HTTP_ORIGIN'])
        : '';

    if ($rawOrigin === '') {
        return;
    }

    $requestOrigin = pm_normalize_origin($rawOrigin);
    $frontendOrigin = pm_frontend_origin();

    if (
        $requestOrigin !== '' &&
        $frontendOrigin !== '' &&
        hash_equals($frontendOrigin, $requestOrigin)
    ) {
        header('Access-Control-Allow-Origin: ' . $requestOrigin);
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Access-Control-Max-Age: 600');
    }
}