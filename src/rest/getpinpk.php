<?php

//return:
//0 - FORCE TO USE USERNAME/PASSWORD
//1 - PIN error
//otherwise - pk
session_start();
require_once dirname(__FILE__).'/../function/sqllink.php';
$link = sqllink();
if (!$link) {
    error("Database connection failed");
}
$user = $_POST['user'];
$device = $_POST['device'];
if ($user == '' || $device == '') {
    error("No PIN available");
}
$sql = 'SELECT `id` FROM `pwdusrrecord` WHERE `username`= ?';
$res = sqlexec($sql, [$user], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    error("No PIN available");
}
$id = $record['id'];
$sql = 'DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?';
$res = sqlexec($sql, [$PIN_EXPIRE_TIME], $link);
$sql = 'SELECT `pinsig`,`pinpk` FROM `pin` WHERE `userid`= ? AND `device`=?';
$res = sqlexec($sql, [$id, $device], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    error("No PIN available");
}
$sig = $record['pinsig'];
if (strcmp(hash('sha512', (string) $sig.(string) $_SESSION['random_login_stamp']), (string) $_POST['sig']) == 0) {
    $sql = 'UPDATE `pin` SET `errortimes`=0 WHERE `userid`= ? AND `device`=?';
    $res = sqlexec($sql, [$id, $device], $link);

    echo json_encode(['status' => "success", "pinpk" => $record['pinpk']]);
    die();
}
$sql = 'UPDATE `pin` SET `errortimes`=`errortimes`+1 WHERE `userid`= ? AND `device`=?';
$res = sqlexec($sql, [$id, $device], $link);
error("To many tries. PIN deleted.");
