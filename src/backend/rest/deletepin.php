<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

if (!pm_is_allowed_request_origin()) {
  ajaxError('origin');
}

$user = isset($_POST['user']) ? (string) $_POST['user'] : '';
$device = isset($_POST['device']) ? (string) $_POST['device'] : '';
if ($user === '' || $device === '') {
  ajaxError('parameter');
}

$link = sqllink();
if (!$link) {
  ajaxError('general');
}

$sql = 'SELECT id FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$user], $link);
$record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if ($record) {
  sqlexec('DELETE FROM `pin` WHERE `userid` = ? AND `device` = ?', [(int) $record['id'], $device], $link);
}

ajaxSuccess();
