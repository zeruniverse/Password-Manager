<?php
// CORS and frontend-origin checks for frontend/backend separated deployments.
// This file is loaded by function/ajax.php and function/common.php.

require_once dirname(__FILE__).'/config.php';

function pm_normalize_origin($url) {
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    $p = parse_url($url);
    if (!$p || empty($p['scheme']) || empty($p['host'])) {
        return '';
    }
    $scheme = strtolower($p['scheme']);
    $host = strtolower($p['host']);
    $port = isset($p['port']) ? (int) $p['port'] : null;

    $origin = $scheme.'://'.$host;
    if ($port !== null && !(($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
        $origin .= ':'.$port;
    }
    return $origin;
}

function pm_allowed_frontend_origins() {
    global $FRONTEND_ORIGINS, $FRONTEND_URL;

    $origins = [];
    if (isset($FRONTEND_ORIGINS) && is_array($FRONTEND_ORIGINS)) {
        foreach ($FRONTEND_ORIGINS as $origin) {
            $normalized = pm_normalize_origin($origin);
            if ($normalized !== '') {
                $origins[$normalized] = true;
            }
        }
    }

    if (isset($FRONTEND_URL)) {
        $normalized = pm_normalize_origin($FRONTEND_URL);
        if ($normalized !== '') {
            $origins[$normalized] = true;
        }
    }

    return array_keys($origins);
}

function pm_request_origin() {
    return isset($_SERVER['HTTP_ORIGIN']) ? pm_normalize_origin($_SERVER['HTTP_ORIGIN']) : '';
}

function pm_is_allowed_origin($origin) {
    if ($origin === '') {
        return false;
    }
    return in_array($origin, pm_allowed_frontend_origins(), true);
}

function pm_is_allowed_request_origin() {
    global $ALLOW_NO_ORIGIN_REQUESTS;

    $origin = pm_request_origin();
    if ($origin === '') {
        return !empty($ALLOW_NO_ORIGIN_REQUESTS);
    }
    return pm_is_allowed_origin($origin);
}

function pm_send_cors_headers() {
    global $CORS_ALLOW_CREDENTIALS;

    $origin = pm_request_origin();
    if ($origin !== '' && pm_is_allowed_origin($origin)) {
        header('Access-Control-Allow-Origin: '.$origin);
        header('Vary: Origin', false);
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        header('Access-Control-Max-Age: 600');
        if (!empty($CORS_ALLOW_CREDENTIALS)) {
            header('Access-Control-Allow-Credentials: true');
        }
    }

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (!pm_is_allowed_request_origin()) {
            http_response_code(403);
        } else {
            http_response_code(204);
        }
        exit;
    }
}
