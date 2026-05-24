<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';
require_once dirname(__FILE__) . '/../function/totp.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$username = pm_auth_user();
$action = isset($_POST['action']) ? (string) $_POST['action'] : 'status';

$sql = 'SELECT `password`, `totp_sec` FROM `pwdusrrecord` WHERE `id` = ?';
$res = sqlexec($sql, [$id], $link);
$record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if (!$record) {
  ajaxError('authentication');
}

if ($action === 'status') {
  ajaxSuccess(['enabled' => trim((string) $record['totp_sec']) !== '' ? 1 : 0]);
}

if ($action === 'off') {
  $res = sqlexec('UPDATE `pwdusrrecord` SET `totp_sec` = ? WHERE `id` = ?', ['', $id], $link);
  if (!$res) {
    ajaxError('general');
  }
  totp_clear_trust_cookie($username);
  ajaxSuccess(['enabled' => 0, 'totp_clear' => 1]);
}

if ($action === 'on') {
  $secret = isset($_POST['secret']) ? totp_normalize_secret($_POST['secret']) : '';
  $code = isset($_POST['code']) ? (string) $_POST['code'] : '';
  if ($secret === '' || !totp_verify_code($secret, $code)) {
    ajaxError('TotpWrong');
  }

  $res = sqlexec('UPDATE `pwdusrrecord` SET `totp_sec` = ? WHERE `id` = ?', [$secret, $id], $link);
  if (!$res) {
    ajaxError('general');
  }

  totp_set_trust_cookie($username, (string) $record['password'], $secret);
  $payload = ['enabled' => 1];
  if (isset($GLOBALS['PM_TOTP_TRUST_VALUE'])) {
    $payload['totp_trust'] = $GLOBALS['PM_TOTP_TRUST_VALUE'];
  }
  ajaxSuccess($payload);
}

ajaxError('parameter');
