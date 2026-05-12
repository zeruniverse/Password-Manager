<?php

//****************************************
// PLEASE SPECIFY THE VARIABLES BELOW
//****************************************

// Database host.
$DB_HOST = '';

// Database name for Password Manager.
$DB_NAME = '';

// Database username.
$DB_USER = '';

// Database password.
$DB_PASSWORD = '';

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
$FRONTEND_URL = 'https://abc.github.io/passwordmanager/';

/*
 * Keep false in production.
 *
 * Set true only for temporary CLI testing without an Origin header.
 */
$ALLOW_NO_ORIGIN_REQUESTS = false;

// Default timezone.
date_default_timezone_set('America/Los_Angeles');

/*
 * Server-side salt only.
 *
 * Do not change after you start using Password Manager.
 */
$GLOBAL_SALT_3 = '*&Kjnskjnaucibiqb9298hv9sHIUWNiukJNIusfbic897*(^)';

//********************************************************************
// ADVANCED SETTINGS
//********************************************************************

// Default fields.
// Do not start key with `_` because those are system fields.
$DEFAULT_FIELDS = json_encode([
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
$FILE_ENABLED = true;

// Allow new user signup.
$ALLOW_SIGN_UP = true;

// Allow users to customize fields.
$CUSTOMIZE_FIELDS = true;

// PIN expire time.
$PIN_EXPIRE_TIME = 7776000;

// Login history expire time.
$LOG_EXPIRE_TIME = 7776000;

// Block IP after this many failed tries in BLOCK_IP_TIME.
$BLOCK_IP_TRY = 10;

// Block IP time.
$BLOCK_IP_TIME = 3600;

// Block account after this many failed tries in ACCOUNT_BAN_TIME.
$BLOCK_ACCOUNT_TRY = 5;

// Account ban time.
$ACCOUNT_BAN_TIME = 180;

// Server-side session timeout.
$SERVER_TIMEOUT = 1200;

// Number of iterations for server-side password hashing.
$PBKDF2_ITERATIONS = 64000;