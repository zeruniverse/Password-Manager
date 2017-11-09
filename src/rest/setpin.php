<?php

function random_str($length)
{
    $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

    $str = '';
    $arr_len = count($arr);
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $arr_len - 1);
        $str .= $arr[$rand];
    }

    return $str;
}
require_once dirname(__FILE__).'/../function/sqllink.php';
$link = sqllink();
if (!checksession($link)) {
    die('0');
}
$id = $_SESSION['userid'];
$usr = $_SESSION['user'];
$username = $_POST['user'];
$device = $_POST['device'];
$sig = $_POST['sig'];

if ($username != $usr) {
    die('0');
}
$sql = 'DELETE FROM `pin` WHERE `userid` = ? AND `device` = ?';
$res = sqlexec($sql, [$id, $device], $link);

$pinpk = random_str(29);

$sql = 'INSERT INTO `pin` (`userid`,`device`,`pinsig`,`pinpk`,`ua`) VALUES (?,?,?,?,?)';
$res = sqlexec($sql, [$id, $device, $sig, $pinpk, $_SERVER['HTTP_USER_AGENT']], $link);

echo $pinpk;
