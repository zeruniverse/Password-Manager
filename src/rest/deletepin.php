<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!$link) {
    ajaxError('general');
}
$user = $_POST['user'];
$device = $_POST['device'];
if ($user == '' || $device == '') {
    ajaxError('parameter');
}
$sql = 'SELECT id FROM `pwdusrrecord` WHERE `username`= ?';
$res = sqlexec($sql, [$user], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    ajaxSuccess(); //Respond with success to prevent the enumeration of usernames
}
$id = $record['id'];
$sql = 'DELETE FROM `pin` WHERE `userid`= ? AND `device`= ?';
$res = sqlexec($sql, [$id, $device], $link);
ajaxSuccess();
