<?php

require_once dirname(__FILE__) . '/config.php';

function pm_normalize_origin($url)
{
  $url = trim((string) $url);
  if ($url === '') {
    return '';
  }

  $parts = parse_url($url);
  if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
    return '';
  }

  $scheme = strtolower($parts['scheme']);
  $host = strtolower($parts['host']);
  $port = isset($parts['port']) ? (int) $parts['port'] : null;

  $origin = $scheme . '://' . $host;
  if ($port !== null && !(($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80))) {
    $origin .= ':' . $port;
  }

  return $origin;
}

function pm_frontend_origin()
{
  global $FRONTEND_URL;
  return pm_normalize_origin($FRONTEND_URL);
}

function pm_request_origin()
{
  return isset($_SERVER['HTTP_ORIGIN']) ? pm_normalize_origin($_SERVER['HTTP_ORIGIN']) : '';
}

function pm_is_allowed_request_origin()
{
  global $ALLOW_NO_ORIGIN_REQUESTS;

  $origin = pm_request_origin();
  if ($origin === '') {
    return !empty($ALLOW_NO_ORIGIN_REQUESTS) || PHP_SAPI === 'cli';
  }

  $frontend = pm_frontend_origin();
  return $frontend !== '' && hash_equals($frontend, $origin);
}

function pm_send_cors_headers()
{
  $origin = pm_request_origin();
  $frontend = pm_frontend_origin();

  if ($origin !== '' && $frontend !== '' && hash_equals($frontend, $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
  }

  header('Access-Control-Allow-Methods: POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
  header('Access-Control-Max-Age: 86400');

  if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
  }
}
