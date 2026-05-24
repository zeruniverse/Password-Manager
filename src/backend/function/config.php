<?php

$DB_HOST = getenv('DB_HOST') ?: '';
$DB_NAME = getenv('DB_NAME') ?: '';
$DB_USER = getenv('DB_USER') ?: '';
$DB_PASSWORD = getenv('DB_PASSWORD') ?: '';

$FRONTEND_URL = getenv('FRONTEND_URL') ?: 'https://abc.github.io/passwordmanager/';
$ALLOW_NO_ORIGIN_REQUESTS = filter_var(getenv('ALLOW_NO_ORIGIN_REQUESTS'), FILTER_VALIDATE_BOOLEAN);

date_default_timezone_set(getenv('TZ') ?: 'America/Los_Angeles');

$GLOBAL_SALT_3 = getenv('GLOBAL_SALT_3') ?: '*&Kjnskjnaucibiqb9298hv9sHIUWNiukJNIusfbic897*(^)';

$DEFAULT_FIELDS = getenv('DEFAULT_FIELDS') ?: json_encode([
  'url' => [
    'colname' => 'URL',
    'hint' => '',
    'cls' => 'hidden'
  ],
  'user' => [
    'colname' => 'Username',
    'hint' => '',
    'cls' => 'hidden-xs',
    'position' => 1
  ],
  'comment' => [
    'colname' => 'Comment',
    'hint' => '',
    'cls' => 'hidden',
    'type' => 'textarea'
  ],
  'tags' => [
    'colname' => 'Tags',
    'hint' => 'Comma separated values',
    'cls' => 'hidden-xs'
  ]
]);

$FILE_ENABLED = filter_var(getenv('FILE_ENABLED'), FILTER_VALIDATE_BOOLEAN);
$ALLOW_SIGN_UP = filter_var(getenv('ALLOW_SIGN_UP'), FILTER_VALIDATE_BOOLEAN);
$CUSTOMIZE_FIELDS = filter_var(getenv('CUSTOMIZE_FIELDS'), FILTER_VALIDATE_BOOLEAN);

$PIN_EXPIRE_TIME = getenv('PIN_EXPIRE_TIME') ? (int) getenv('PIN_EXPIRE_TIME') : 7776000;
$LOG_EXPIRE_TIME = getenv('LOG_EXPIRE_TIME') ? (int) getenv('LOG_EXPIRE_TIME') : 7776000;

$BLOCK_IP_TRY = getenv('BLOCK_IP_TRY') ? (int) getenv('BLOCK_IP_TRY') : 10;
$BLOCK_IP_TIME = getenv('BLOCK_IP_TIME') ? (int) getenv('BLOCK_IP_TIME') : 3600;
$BLOCK_ACCOUNT_TRY = getenv('BLOCK_ACCOUNT_TRY') ? (int) getenv('BLOCK_ACCOUNT_TRY') : 5;
$ACCOUNT_BAN_TIME = getenv('ACCOUNT_BAN_TIME') ? (int) getenv('ACCOUNT_BAN_TIME') : 180;

/* Kept for frontend compatibility. Backend auth is stateless and does not use this for session lifetime. */
$SERVER_TIMEOUT = getenv('SERVER_TIMEOUT') ? (int) getenv('SERVER_TIMEOUT') : 1200;
$PBKDF2_ITERATIONS = getenv('PBKDF2_ITERATIONS') ? (int) getenv('PBKDF2_ITERATIONS') : 64000;
