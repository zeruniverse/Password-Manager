<?php
require_once dirname(__FILE__).'/../function/common.php';
require_once dirname(__FILE__).'/../function/ajax.php';

start_session();

if (!isset($_SESSION['session_token'])) {
    $_SESSION['session_token'] = bin2hex(random_bytes(64));
}

function pm_post_or_cookie($postName, $cookieName) {
    if (isset($_POST[$postName])) {
        return (string) $_POST[$postName];
    }
    if (isset($_COOKIE[$cookieName])) {
        return (string) $_COOKIE[$cookieName];
    }
    return '';
}

function usepin() {
    global $PIN_EXPIRE_TIME;

    // In the split deployment, username/device cookies live on the trusted frontend domain,
    // not on the backend domain. The frontend sends them as POST fields for this check.
    $user = pm_post_or_cookie('frontend_username', 'username');
    $device = pm_post_or_cookie('frontend_device', 'device');

    if ($user === '' || $device === '') {
        return false;
    }

    $link = sqllink();
    if (!$link) {
        return false;
    }

    $sql = 'SELECT id FROM `pwdusrrecord` WHERE `username`= ?';
    $res = sqlexec($sql, [$user], $link);
    $record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
    if (!$record) {
        return false;
    }

    $id = $record['id'];

    $sql = 'DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?';
    sqlexec($sql, [$PIN_EXPIRE_TIME], $link);

    $sql = 'SELECT * FROM `pin` WHERE `userid`= ? AND `device`= ?';
    $res = sqlexec($sql, [$id, $device], $link);
    $record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;

    return (bool) $record;
}

if (!isset($DB_NAME) || $DB_NAME === '') {
    ajaxError('database');
}

$result = [];
$result['loggedIn'] = (isset($_SESSION['loginok']) && $_SESSION['loginok'] == 1);
$result['session_token'] = $_SESSION['session_token'];
$result['api_session_id'] = session_id();
$result['use_pin'] = usepin() ? 1 : 0;
$result['version'] = $VERSION;
$result['banTime'] = $ACCOUNT_BAN_TIME;
$result['allowSignup'] = $ALLOW_SIGN_UP;
$result['minPasswordLength'] = $MINIMAL_PASSWORD_LENGTH;
$result['minNameLength'] = $MINIMAL_NAME_LENGTH;

ajaxSuccess($result);
