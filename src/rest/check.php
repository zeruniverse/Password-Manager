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
    $remote = $_SERVER['REMOTE_ADDR'];

    return $remote;
}
function loghistory($link, $userid, $ip, $ua, $outcome)
{
    $sql = 'SELECT max(`id`) AS `m` FROM `history`';
    $res = sqlquery($sql, $link);
    $r = $res->fetch(PDO::FETCH_ASSOC);
    $i = ($r == false) ? 0 : ((int) $r['m']) + 1;
    $sql = 'INSERT INTO `history` VALUES (?,?,?,?,?,CURRENT_TIMESTAMP)';
    $res = sqlexec($sql, [$i, $userid, $ip, $ua, $outcome], $link);
}
if (!isset($_SESSION['random_login_stamp']) || $_SESSION['random_login_stamp'] == '') {
    error('Oops, our server run into some problems. Please refresh this page and try again.');
}
$usr = $_POST['user'];
$pw = $_POST['pwd'];
// check length of password hash for pbkdf2
if (strlen($pw) > 130) {
    error('Oops, our server run into some problems. Please refresh this page and try again.');
}
if ($pw == '' || $usr == '' || $_POST['session_token'] == '') {
    error('Oops, our server run into some problems. Please refresh this page and try again.');
}
$link = sqllink();
if (!$link) {
    error('Oops, our server run into some problems. Please refresh this page and try again.');
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
if ($record != false) {
    error('Your IP has been blocked due to malicious activity');
}

$sql = 'SELECT * FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$usr], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    error('Username password combination is unknown');
}

$sql = 'SELECT count(*) as `m` FROM `history` WHERE `userid` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
$res = sqlexec($sql, [(int) $record['id'], $ACCOUNT_BAN_TIME], $link);
$count = $res->fetch(PDO::FETCH_ASSOC);
if ((int) $count['m'] >= $BLOCK_ACCOUNT_TRY) {
    error('Your account has been protected due to continuous attack. Try again in ' + $ACCOUNT_BAN_TIME + ' seconds');
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
    error('Username password combination is unknown');
}
$_SESSION['loginok'] = 1;
$_SESSION['user'] = $usr;
$_SESSION['userid'] = $record['id'];
$_SESSION['pwd'] = $record['password'];
$_SESSION['fields'] = $record['fields'];
$_SESSION['create_time'] = time();
setcookie('ServerRenew', '1', 0, '/');
loghistory($link, (int) $record['id'], getUserIP(), $_SERVER['HTTP_USER_AGENT'], 1);
echo json_encode(['status' => "success"]);
