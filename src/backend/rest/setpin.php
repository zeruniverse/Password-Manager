<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$usr = pm_auth_user();
$device = isset($_POST['device']) ? (string) $_POST['device'] : '';
$sig = isset($_POST['sig']) ? (string) $_POST['sig'] : '';

if ($device === '' || $sig === '' || strlen($device) > 20) {
  ajaxError('parameter');
}

$pinpk = random_bytes(64);
$pinsig = hash_pbkdf2('sha3-512', $sig, $pinpk, $PBKDF2_ITERATIONS);
$ua = pm_user_agent();

if (!$link->beginTransaction()) {
  ajaxError('general');
}

sqlexec('DELETE FROM `pin` WHERE `userid` = ? AND `device` = ?', [$id, $device], $link);
$sql = 'INSERT INTO `pin` (`userid`, `device`, `pinsig`, `pinpk`, `ua`, `createtime`, `errortimes`) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 0)';
$res = sqlexec($sql, [$id, $device, $pinsig, $pinpk, $ua], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}
$link->commit();

ajaxSuccess([
  'user' => $usr,
  'device' => $device,
  'pinpk' => bin2hex($pinpk)
]);
