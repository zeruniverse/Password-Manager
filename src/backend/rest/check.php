<?php
require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';
require_once dirname(__FILE__) . '/../function/totp.php';

start_session();

$token = (isset($_SESSION['session_token']) ? $_SESSION['session_token'] : null);
session_regenerate_id(true);
$_SESSION['session_token'] = $token;
$sidvalue = session_id();

function getUserIP()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }

    return $_SERVER['REMOTE_ADDR'];
}

function loghistory($link, $userid, $ip, $ua, $outcome)
{
    $sql = 'SELECT max(`id`) AS `m` FROM `history`';
    $res = sqlquery($sql, $link);
    $r = $res->fetch(PDO::FETCH_ASSOC);
    $i = (!$r) ? 0 : ((int) $r['m']) + 1;

    $sql = 'INSERT INTO `history` VALUES (?,?,?,?,?,CURRENT_TIMESTAMP)';
    sqlexec($sql, [$i, $userid, $ip, $ua, $outcome], $link);
}

$usr = isset($_POST['user']) ? $_POST['user'] : '';
$pw = isset($_POST['pwd']) ? $_POST['pwd'] : '';
$totpcode = isset($_POST['totpcode']) ? trim((string) $_POST['totpcode']) : '';
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

if (strlen($pw) > 130) {
    ajaxError('general');
}

if ($pw == '' || $usr == '' || !isset($_POST['session_token']) || $_POST['session_token'] == '') {
    ajaxError('general');
}

$link = sqllink();

if (!$link) {
    ajaxError('general');
}

// Clear up expired records.
$sql = 'DELETE FROM `blockip` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?';
sqlexec($sql, [$BLOCK_IP_TIME], $link);

$sql = 'DELETE FROM `history` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?';
sqlexec($sql, [$LOG_EXPIRE_TIME], $link);

// Check if IP is blocked.
$sql = 'SELECT * FROM `blockip` WHERE `ip` = ?';
$res = sqlexec($sql, [getUserIP()], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);

if ($record) {
    ajaxError('blockIP');
}

// Check username.
$sql = 'SELECT * FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$usr], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    ajaxError('loginFailed');
}

// Check if account is blocked.
$sql = 'SELECT count(*) as `m` FROM `history`
        WHERE `userid` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
$res = sqlexec($sql, [(int) $record['id'], $ACCOUNT_BAN_TIME], $link);
$count = $res->fetch(PDO::FETCH_ASSOC);

if ((int) $count['m'] >= $BLOCK_ACCOUNT_TRY) {
    ajaxError('blockAccount');
}

// Check if password is correct.
$password = $record['password'];
$hash_pbkdf2 = hash_pbkdf2('sha3-512', $pw, (string) $record['salt'], $PBKDF2_ITERATIONS);

if (strcmp((string) $password, (string) $hash_pbkdf2) != 0) {
    loghistory($link, (int) $record['id'], getUserIP(), $userAgent, 0);

    $sql = 'SELECT count(*) as `m` FROM `history`
            WHERE `ip` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
    $res = sqlexec($sql, [getUserIP(), $BLOCK_IP_TIME], $link);
    $count = $res->fetch(PDO::FETCH_ASSOC);

    if ((int) $count['m'] >= $BLOCK_IP_TRY) {
        $sql = 'INSERT INTO `blockip` VALUES (?,CURRENT_TIMESTAMP)';
        sqlexec($sql, [getUserIP()], $link);
    }

    ajaxError('loginFailed');
}

// TOTP 2FA.
// Same timing as old email verification: ask only when this browser/device is not trusted
// or the trusted-device cookie has expired.
$totpSecret = isset($record['totp_sec']) ? trim((string) $record['totp_sec']) : '';

if ($totpSecret !== '') {
    if (!totp_is_trusted_device($usr, $hash_pbkdf2, $totpSecret)) {
        if ($totpcode === '') {
            ajaxError('TotpVerify');
        }

        // Recovery path: entering the correct TOTP secret disables 2FA and continues login.
        if (totp_secret_equals($totpSecret, $totpcode)) {
            $sql = 'UPDATE `pwdusrrecord` SET `totp_sec` = ? WHERE `id` = ?';
            $update = sqlexec($sql, ['', (int) $record['id']], $link);
            if (!$update) {
                ajaxError('general');
            }

            totp_clear_trust_cookie($usr);
            $record['totp_sec'] = '';
        } elseif (totp_verify_code($totpSecret, $totpcode)) {
            totp_set_trust_cookie($usr, $hash_pbkdf2, $totpSecret);
        } else {
            loghistory($link, (int) $record['id'], getUserIP(), $userAgent, 0);
            ajaxError('TotpWrong');
        }
    }
}

$_SESSION['loginok'] = 1;
$_SESSION['user'] = $usr;
$_SESSION['userid'] = $record['id'];
$_SESSION['pwd'] = $record['password'];
$_SESSION['fields'] = $record['fields'];
$_SESSION['create_time'] = time();
$_SESSION['refresh_time'] = time();

loghistory($link, (int) $record['id'], getUserIP(), $userAgent, 1);

$payload = ['api_session_id' => session_id(), 'session_token' => $_SESSION['session_token']];
if (isset($GLOBALS['PM_TOTP_TRUST_VALUE'])) {
    $payload['totp_trust'] = $GLOBALS['PM_TOTP_TRUST_VALUE'];
}
if (!empty($GLOBALS['PM_TOTP_CLEAR_TRUST'])) {
    $payload['totp_clear'] = 1;
}
ajaxSuccess($payload);
