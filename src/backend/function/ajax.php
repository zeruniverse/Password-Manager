<?php

require_once dirname(__FILE__) . '/cors.php';

pm_send_cors_headers();
header('Content-Type: application/json; charset=UTF-8');

$AJAX_MESSAGES = [
  'general' => 'General error.',
  'config' => 'Server is not configured.',
  'database' => 'Database is not configured.',
  'PINunavailable' => 'No PIN available',
  'PINwrong' => 'PIN is wrong',
  'authentication' => 'AUTHENTICATION REQUIRED',
  'parameter' => 'PARAMETER ERROR',
  'origin' => 'Request origin is not allowed.',
  'blockIP' => 'Too many failed attempts from this IP. Please try later.',
  'blockAccount' => 'Too many failed attempts for this account. Please try later.',
  'loginFailed' => 'Username or password is wrong.',
  'entryNotFound' => 'Entry not found.',
  'updateFailed' => 'Failed to update data.',
  'signup' => 'Sign up is not allowed.',
  'occupiedUser' => 'Username is already occupied.',
  'occupiedEmail' => 'Email is already occupied.',
  'fileFailed' => 'File not found.',
  'invalidEmail' => 'Invalid email address.',
  'TotpVerify' => 'Please enter the 2FA authenticator code.',
  'TotpWrong' => 'The 2FA authenticator code is wrong.'
];

function ajaxError($message = 'general', $payload = [])
{
  global $AJAX_MESSAGES;

  $text = isset($AJAX_MESSAGES[$message]) ? $AJAX_MESSAGES[$message] : $message;
  $data = is_array($payload) ? $payload : [];

  echo json_encode(array_merge([
    'status' => 'error',
    'message' => $text,
    'code' => $message
  ], $data));
  exit;
}

function ajaxSuccess($payload = [])
{
  if (!is_array($payload)) {
    $payload = ['data' => $payload];
  }

  echo json_encode(array_merge([
    'status' => 'success'
  ], $payload));
  exit;
}
