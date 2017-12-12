<?php

// only even lengths are allowed
function random_str($length)
{
    return bin2hex(openssl_random_pseudo_bytes($length / 2));
}
require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError("session");
}
$id = $_SESSION['userid'];
$usr = $_SESSION['user'];
$username = $_POST['user'];
$device = $_POST['device'];
$sig = $_POST['sig'];

if ($username != $usr) {
    ajaxError("userWrong");
}
$sql = 'DELETE FROM `pin` WHERE `userid` = ? AND `device` = ?';
$res = sqlexec($sql, [$id, $device], $link);

$pinpk = random_str(30);

$sql = 'INSERT INTO `pin` (`userid`,`device`,`pinsig`,`pinpk`,`ua`) VALUES (?,?,?,?,?)';
$res = sqlexec($sql, [$id, $device, $sig, $pinpk, $_SERVER['HTTP_USER_AGENT']], $link);

ajaxSuccess(["pinpk" => $pinpk]);
?>
