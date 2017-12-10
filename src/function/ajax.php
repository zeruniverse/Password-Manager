<?php

header('X-Content-Type-Options: nosniff');
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
const errorMessages = array(
    'general' => 'Oops, our server run into some problems. Please refresh this page and try again.',
    'config' => 'PLEASE CONFIG function/config.php before using this system!',
    'PINunavailable' => "No PIN available",
    'PINwrong' => "Wrong PIN",
    'authentication' => 'AUTHENTICATION ERROR, PLEASE RELOGIN',
    'parameter' => 'Parameter Error',
    'session' => 'session unavailable',
    'blockIP' => 'Your IP has been blocked due to malicious activity',
    'blockAccount' => 'Your account has been protected due to continuous attack. Try again in ' + $ACCOUNT_BAN_TIME + ' seconds',
    'loginFailed' => 'Username password combination is unknown',
    'entryNotFound' => 'Entry not found',
    'updateFailed' => 'Updating entry failed',
    'signup' => 'Signup is not allowed.',
    'password' => 'password too long',
    'invalidEmail' => 'Invalid E-mail address.',
    'occupiedUser' => 'User name already occupied, please choose another user name.',
    'occupiedMail' => 'This E-mail has already been used.',
    'fileFailed' => 'No File Can Be Downloaded',
    'userWrong' => "Wrong User"
);
function error($msg)
{
    echo json_encode(['status' => 'error', 'message' => $msg]);
    die();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error('Wrong method');
}
