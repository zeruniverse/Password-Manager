<?php

//return:
//"PIN error" - FORCE TO USE USERNAME/PASSWORD, DELETE PIN
//"Wrong PIN" - PIN just retry
//otherwise - pk
require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
start_session();
$link = sqllink();
if (!$link) {
    ajaxError('general');
}
$user = $_POST['user'];
$device = $_POST['device'];
if ($user == '' || $device == '') {
    ajaxError('PINunavailable');
}
$sql = 'SELECT `id` FROM `pwdusrrecord` WHERE `username`= ?';
$res = sqlexec($sql, [$user], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    ajaxError('PINunavailable');
}
//Delete PIN in case of too many tries
$id = $record['id'];
$sql = 'DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?';
$res = sqlexec($sql, [$PIN_EXPIRE_TIME], $link);

//Find matching PIN record
$sql = 'SELECT `pinsig`, `pinpk` FROM `pin` WHERE `userid`= ? AND `device`=?';
$res = sqlexec($sql, [$id, $device], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    ajaxError('PINunavailable');
}
$sig = $record['pinsig'];
$pinpk = $record['pinpk'];

$post_sig = hash_pbkdf2('sha3-512', (string) $_POST['sig'], $pinpk, $PBKDF2_ITERATIONS);

if (strcmp($sig, $post_sig) == 0)
{
    $sql = 'UPDATE `pin` SET `errortimes`=0 WHERE `userid`= ? AND `device`=?';
    $res = sqlexec($sql, [$id, $device], $link);

    ajaxSuccess(['pinpk' => bin2hex($record['pinpk'])]);
}
//Wrong PIN
$sql = 'UPDATE `pin` SET `errortimes`=`errortimes`+1 WHERE `userid`= ? AND `device`=?';
$res = sqlexec($sql, [$id, $device], $link);
ajaxError('PINwrong');
