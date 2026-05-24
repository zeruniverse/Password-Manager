<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$result = [];
$result['user'] = pm_auth_user();
$result['fields'] = pm_auth_fields();
$result['fields_allow_change'] = $CUSTOMIZE_FIELDS ? 1 : 0;
$result['server_timeout'] = $SERVER_TIMEOUT;
$result['default_timeout'] = $SERVER_TIMEOUT;
$result['default_length'] = 13;
$result['file_enabled'] = $FILE_ENABLED ? 1 : 0;

$sql = 'SELECT `totp_sec` FROM `pwdusrrecord` WHERE `id` = ?';
$res = sqlexec($sql, [$id], $link);
$userRecord = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
$result['totp_enabled'] = ($userRecord && trim((string) $userRecord['totp_sec']) !== '') ? 1 : 0;

$sql = 'SELECT * FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$accounts = [];
if ($res) {
  while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $accounts[] = [
      'index' => $row['index'],
      'name' => $row['name'],
      'additional' => $row['other'],
      'kss' => $row['pwd']
    ];
  }
}
$result['accounts'] = $accounts;

$sql = 'SELECT `index`, `fname`, `key` FROM `files` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$fdata = [];
if ($res) {
  while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $fdata[] = [
      'index' => $row['index'],
      'fname' => $row['fname'],
      'fkey' => $row['key']
    ];
  }
}
$result['fdata'] = $fdata;

$sql = 'SELECT `id`, UNIX_TIMESTAMP(`time`) AS `time` FROM `history` WHERE `userid` = ? AND `outcome` = 1 ORDER BY `id` DESC LIMIT 1 OFFSET 1';
$res = sqlexec($sql, [$id], $link);
$data = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if ($data) {
  $loginID = (int) $data['id'];
  $result['loginInformation'] = [
    'lastLogin' => (int) $data['time']
  ];
} else {
  $loginID = 0;
  $result['loginInformation'] = [
    'lastLogin' => 0
  ];
}

$sql = 'SELECT COUNT(*) AS `failedLogins` FROM `history` WHERE `userid` = ? AND `outcome` = 0 AND `id` > ?';
$res = sqlexec($sql, [$id, $loginID], $link);
$data = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
$result['loginInformation']['failedCount'] = $data ? (int) $data['failedLogins'] : 0;

ajaxSuccess($result);
