<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('session');
}
$id = $_SESSION['userid'];
$usr = $_SESSION['user'];
$username = $_POST['user'];
$device = $_POST['device'];
$sig = $_POST['sig'];

if ($username != $usr) {
    ajaxError('userWrong');
}
$sql = 'DELETE FROM `pin` WHERE `userid` = ? AND `device` = ?';
$res = sqlexec($sql, [$id, $device], $link);

$pinpk = random_bytes(64);

$sig = hash_pbkdf2('sha3-512', (string) $sig, $pinpk, $PBKDF2_ITERATIONS);

$sql = 'INSERT INTO `pin` (`userid`,`device`,`pinsig`,`pinpk`,`ua`) VALUES (?,?,?,?,?)';

$res = sqlexec($sql, [$id, $device, $sig, $pinpk, $_SERVER['HTTP_USER_AGENT']], $link);

ajaxSuccess(['pinpk' => bin2hex($pinpk)]);
