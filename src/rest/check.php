<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
start_session();
$token = (isset($_SESSION['session_token']) ? $_SESSION['session_token'] : null);
session_regenerate_id(true);
$_SESSION['session_token'] = $token;
$sidvalue = session_id();
function getUserIP()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
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
    $res = sqlexec($sql, [$i, $userid, $ip, $ua, $outcome], $link);
}
if (!isset($_SESSION['random_login_stamp']) || $_SESSION['random_login_stamp'] == '') {
    ajaxError('general');
}
$usr = $_POST['user'];
$pw = $_POST['pwd'];
$emailcode = $_POST['emailcode'];
// check length of password hash for pbkdf2
if (strlen($pw) > 130) {
    ajaxError('general');
}
if ($pw == '' || $usr == '' || $_POST['session_token'] == '') {
    ajaxError('general');
}
$link = sqllink();
if (!$link) {
    ajaxError('general');
}

//Clear Up.
$sql = 'DELETE FROM `blockip` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?';
$res = sqlexec($sql, [$BLOCK_IP_TIME], $link);
$sql = 'DELETE FROM `history` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?';
$res = sqlexec($sql, [$LOG_EXPIRE_TIME], $link);

//check if IP is blocked
$sql = 'SELECT * FROM `blockip` WHERE `ip` = ?';
$res = sqlexec($sql, [getUserIP()], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record) {
    ajaxError('blockIP');
}

//check username
$sql = 'SELECT * FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$usr], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    ajaxError('loginFailed');
}

// check if account is blocked
$sql = 'SELECT count(*) as `m` FROM `history`
    WHERE `userid` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
$res = sqlexec($sql, [(int) $record['id'], $ACCOUNT_BAN_TIME], $link);
$count = $res->fetch(PDO::FETCH_ASSOC);
if ((int) $count['m'] >= $BLOCK_ACCOUNT_TRY) {
    ajaxError('blockAccount');
}

// check if password is correct
$password = $record['password'];
$hash_pbkdf2 = hash_pbkdf2('sha3-512', $pw, (string) $record['salt'], $PBKDF2_ITERATIONS);
if (strcmp((string) $password, (string) $hash_pbkdf2) != 0) {
    loghistory($link, (int) $record['id'], getUserIP(), $_SERVER['HTTP_USER_AGENT'], 0);
    $sql = 'SELECT count(*) as `m` FROM `history`
        WHERE `ip` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
    $res = sqlexec($sql, [getUserIP(), $BLOCK_IP_TIME], $link);
    $count = $res->fetch(PDO::FETCH_ASSOC);
    if ((int) $count['m'] >= $BLOCK_IP_TRY) {
        $sql = 'INSERT INTO `blockip` VALUES (?,CURRENT_TIMESTAMP)';
        $res = sqlexec($sql, [getUserIP()], $link);
    }
    ajaxError('loginFailed');
}
if($EMAIL_VERIFICATION_ENABLED)
{
    $pwdrecord_check = hash_pbkdf2('sha3-512', (string) $hash_pbkdf2, $GLOBAL_SALT_3, $PBKDF2_ITERATIONS);

    // To avoid spam, only do email verification if password is correct

    // Use urlencode as backend has no restriction for username.
    $encoded_usr = urlencode($usr);
    if ((!isset($_COOKIE["pwdrecord_".$encoded_usr]) ||
         $_COOKIE["pwdrecord_".$encoded_usr]!=$pwdrecord_check) &&
        (!isset($_SESSION["emailcode"]) || $_SESSION["emailcode"]!=$emailcode))
    {
        // We need to generate a random email verification number

        require_once dirname(__FILE__).'/../function/send_email.php';
        // 8 digits verification code
        $k = sprintf("%08d", mt_rand(0, 99999999));
        $_SESSION["emailcode"] = $k;
        if(send_email($record["email"], $k))
        {
            ajaxError('EmailVerify');
        } else {
            // Fail to send out emails
            ajaxError('general');
        }

    }

    // on successful login, update user pwdrecord cookie. The cookie will expire in 60 days. i.e.
    //    if you haven't logoned for 30 days, you will have to verify emails again!
    setcookie("pwdrecord_".$encoded_usr, $pwdrecord_check, time()+86400*60,
              '/; samesite=strict', null, true, true);
}

$_SESSION['loginok'] = 1;
$_SESSION['user'] = $usr;
$_SESSION['userid'] = $record['id'];
$_SESSION['pwd'] = $record['password'];
$_SESSION['fields'] = $record['fields'];
$_SESSION['create_time'] = time();
$_SESSION['refresh_time'] = time();
loghistory($link, (int) $record['id'], getUserIP(), $_SERVER['HTTP_USER_AGENT'], 1);
ajaxSuccess();
