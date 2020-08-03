<?php

require_once dirname(__FILE__).'/../function/common.php';
require_once dirname(__FILE__).'/../function/ajax.php';
start_session();
if (!isset($_SESSION['session_token'])) {
    $_SESSION['session_token'] = bin2hex(random_bytes(64));
}
function usepin()
{
    global $PIN_EXPIRE_TIME;
    if (!isset($_COOKIE['username']) || !isset($_COOKIE['device'])) {
        return false;
    }
    $user = $_COOKIE['username'];
    $device = $_COOKIE['device'];
    if ($user == '' || $device == '') {
        return false;
    }
    $link = sqllink();
    if (!$link) {
        return false;
    }
    $sql = 'SELECT id FROM `pwdusrrecord` WHERE `username`= ?';
    $res = sqlexec($sql, [$user], $link);
    $record = $res->fetch(PDO::FETCH_ASSOC);
    if (!$record) {
        return false;
    }
    $id = $record['id'];
    $sql = 'DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?';
    $res = sqlexec($sql, [$PIN_EXPIRE_TIME], $link);
    $sql = 'SELECT * FROM `pin` WHERE `userid`= ? AND `device`= ?';
    $res = sqlexec($sql, [$id, $device], $link);
    $record = $res->fetch(PDO::FETCH_ASSOC);
    if (!$record) {
        return false;
    }

    return true;
}
if ($DB_NAME == '') {
    ajaxError('config');
}
$result = [];
$result['hostdomain'] = $HOSTDOMAIN;
$result['loggedIn'] = (isset($_SESSION['loginok']) && $_SESSION['loginok'] == 1);
$result['default_timeout'] = $BROWSER_TIMEOUT;
$result['default_letter_used'] = $DEFAULT_LETTER_USED;
$result['default_length'] = $DEFAULT_LENGTH;
$result['global_salt_1'] = $GLOBAL_SALT_1;
$result['global_salt_2'] = $GLOBAL_SALT_2;
$result['session_token'] = $_SESSION['session_token'];
$result['use_pin'] = usepin() ? 1 : 0;
$result['version'] = $VERSION;
$result['banTime'] = $ACCOUNT_BAN_TIME;
$result['allowSignup'] = $ALLOW_SIGN_UP;
$result['minPasswordLength'] = $MINIMAL_PASSWORD_LENGTH;
$result['minNameLength'] = $MINIMAL_NAME_LENGTH;
ajaxSuccess($result);
