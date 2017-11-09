<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
$link = sqllink();
if (!$link) {
    die(0);
}
$user = $_POST['user'];
$device = $_POST['device'];
if ($user == '' || $device == '') {
    die('0');
}
$sql = 'SELECT id FROM `pwdusrrecord` WHERE `username`= ?';
$res = sqlexec($sql, [$user], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    die('0');
}
$id = $record['id'];
$sql = 'DELETE FROM `pin` WHERE `userid`= ? AND `device`= ?';
$res = sqlexec($sql, [$id, $device], $link);
echo '1';
