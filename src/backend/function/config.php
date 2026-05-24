<?php

//****************************************
// PLEASE SPECIFY THE VARIABLES BELOW
//****************************************

// Database host.
$DB_HOST = getenv('DB_HOST')?:'';

// Database name for Password Manager.
$DB_NAME = getenv('DB_NAME')?:'';

// Database username.
$DB_USER = getenv('DB_USER')?:'';

// Database password.
$DB_PASSWORD = getenv('DB_PASSWORD')?:'';

/*
 * Trusted static frontend URL.
 *
 * This may contain a path. CORS origin will be derived from this URL.
 *
 * Example:
 *   https://abc.github.io/passwordmanager/
 *
 * Derived origin:
 *   https://abc.github.io
 */
$FRONTEND_URL = getenv('FRONTEND_URL')?:'https://abc.github.io/passwordmanager/';

/*
 * Keep false in production.
 *
 * Set true only for temporary CLI testing without an Origin header.
 */
$ALLOW_NO_ORIGIN_REQUESTS = filter_var(getenv('ALLOW_NO_ORIGIN_REQUESTS'), FILTER_VALIDATE_BOOLEAN);

// Default timezone.
date_default_timezone_set(getenv('TZ')?:'America/Los_Angeles');

/*
 * Server-side salt only.
 *
 * Do not change after you start using Password Manager.
 */
$GLOBAL_SALT_3 = getenv('GLOBAL_SALT_3')?:'*&Kjnskjnaucibiqb9298hv9sHIUWNiukJNIusfbic897*(^)';

//********************************************************************
// ADVANCED SETTINGS
//********************************************************************

// Default fields.
// Do not start key with `_` because those are system fields.
$DEFAULT_FIELDS = getenv('DEFAULT_FIELDS')?:json_encode([
    'url' => [
        'colname' => 'URL',
        'hint' => '',
        'cls' => 'hidden',
    ],
    'user' => [
        'colname' => 'Username',
        'hint' => '',
        'cls' => 'hidden-xs',
        'position' => 1,
    ],
    'comment' => [
        'colname' => 'Comment',
        'hint' => '',
        'cls' => 'hidden',
        'type' => 'textarea',
    ],
    'tags' => [
        'colname' => 'Tags',
        'hint' => 'Comma separated values',
        'cls' => 'hidden-xs',
    ],
]);

// File upload feature.
$FILE_ENABLED = filter_var(getenv('FILE_ENABLED'), FILTER_VALIDATE_BOOLEAN);

// Allow new user signup.
$ALLOW_SIGN_UP = filter_var(getenv('ALLOW_SIGN_UP'), FILTER_VALIDATE_BOOLEAN);

// Allow users to customize fields.
$CUSTOMIZE_FIELDS = filter_var(getenv('CUSTOMIZE_FIELDS'), FILTER_VALIDATE_BOOLEAN);

// PIN expire time.
$PIN_EXPIRE_TIME = getenv('PIN_EXPIRE_TIME')?(int)getenv('PIN_EXPIRE_TIME'):7776000;

// Login history expire time.
$LOG_EXPIRE_TIME = getenv('LOG_EXPIRE_TIME')?(int)getenv('LOG_EXPIRE_TIME'):7776000;

// Block IP after this many failed tries in BLOCK_IP_TIME.
$BLOCK_IP_TRY = getenv('BLOCK_IP_TRY')?(int)getenv('BLOCK_IP_TRY'):10;

// Block IP time.
$BLOCK_IP_TIME = getenv('BLOCK_IP_TIME')?(int)getenv('BLOCK_IP_TIME'):3600;

// Block account after this many failed tries in ACCOUNT_BAN_TIME.
$BLOCK_ACCOUNT_TRY = getenv('BLOCK_ACCOUNT_TRY')?(int)getenv('BLOCK_ACCOUNT_TRY'):5;

// Account ban time.
$ACCOUNT_BAN_TIME = getenv('ACCOUNT_BAN_TIME')?(int)getenv('ACCOUNT_BAN_TIME'):180;

// Server-side session timeout.
$SERVER_TIMEOUT = getenv('SERVER_TIMEOUT')?(int)getenv('SERVER_TIMEOUT'):1200;

// Number of iterations for server-side password hashing.
$PBKDF2_ITERATIONS = getenv('PBKDF2_ITERATIONS')?(int)getenv('PBKDF2_ITERATIONS'):64000;