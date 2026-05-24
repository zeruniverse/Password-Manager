<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

if (!pm_is_allowed_request_origin()) {
  ajaxError('origin');
}

$user = isset($_POST['user']) ? (string) $_POST['user'] : '';
$device = isset($_POST['device']) ? (string) $_POST['device'] : '';
$rawSig = isset($_POST['sig']) ? (string) $_POST['sig'] : '';

if ($user === '' || $device === '' || $rawSig === '') {
  ajaxError('PINunavailable');
}

$link = sqllink();
if (!$link) {
  ajaxError('general');
}

$sql = 'SELECT id FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$user], $link);
$record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if (!$record) {
  ajaxError('PINunavailable');
}
$id = (int) $record['id'];

$sql = 'DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?';
sqlexec($sql, [$PIN_EXPIRE_TIME], $link);

$sql = 'SELECT `pinsig`, `pinpk` FROM `pin` WHERE `userid` = ? AND `device` = ?';
$res = sqlexec($sql, [$id, $device], $link);
$pin = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if (!$pin) {
  ajaxError('PINunavailable');
}

$postedSig = hash_pbkdf2('sha3-512', $rawSig, $pin['pinpk'], $PBKDF2_ITERATIONS);
if (hash_equals((string) $pin['pinsig'], $postedSig)) {
  sqlexec('UPDATE `pin` SET `errortimes` = 0 WHERE `userid` = ? AND `device` = ?', [$id, $device], $link);
  ajaxSuccess(['pinpk' => bin2hex($pin['pinpk'])]);
}

sqlexec('UPDATE `pin` SET `errortimes` = `errortimes` + 1 WHERE `userid` = ? AND `device` = ?', [$id, $device], $link);
ajaxError('PINwrong');
