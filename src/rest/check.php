<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
session_start();
$token = $_SESSION['session_token'];
session_regenerate_id(true);
$_SESSION['session_token'] = $token;
$sidvalue = session_id();
function getUserIP()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
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

$sql = 'SELECT * FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$usr], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    ajaxError('loginFailed');
}

$sql = 'SELECT count(*) as `m` FROM `history` WHERE `userid` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
$res = sqlexec($sql, [(int) $record['id'], $ACCOUNT_BAN_TIME], $link);
$count = $res->fetch(PDO::FETCH_ASSOC);
if ((int) $count['m'] >= $BLOCK_ACCOUNT_TRY) {
    ajaxError('blockAccount');
}

if (strcmp((string) $record['password'], (string) hash_pbkdf2('sha256', $pw, (string) $record['salt'], $PBKDF2_ITERATIONS)) != 0) {
    loghistory($link, (int) $record['id'], getUserIP(), $_SERVER['HTTP_USER_AGENT'], 0);
    $sql = 'SELECT count(*) as `m` FROM `history` WHERE `ip` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
    $res = sqlexec($sql, [getUserIP(), $BLOCK_IP_TIME], $link);
    $count = $res->fetch(PDO::FETCH_ASSOC);
    if ((int) $count['m'] >= $BLOCK_IP_TRY) {
        $sql = 'INSERT INTO `blockip` VALUES (?,CURRENT_TIMESTAMP)';
        $res = sqlexec($sql, [getUserIP()], $link);
    }
    ajaxError('loginFailed');
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
