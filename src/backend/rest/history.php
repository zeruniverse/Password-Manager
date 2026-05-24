<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$result = [];
$result['usr'] = pm_auth_user();

$pins = [];
$sql = 'SELECT `device`, `ua`, UNIX_TIMESTAMP(`createtime`) AS `createtime` FROM `pin` WHERE `userid` = ? ORDER BY `createtime` DESC';
$res = sqlexec($sql, [$id], $link);
if ($res) {
  while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $pins[] = [
      'ua' => $row['ua'],
      'ctime' => (int) $row['createtime'],
      'did' => $row['device']
    ];
  }
}
$result['pins'] = $pins;

$ips = [];
$sql = 'SELECT `ip`, `ua`, `outcome`, UNIX_TIMESTAMP(`time`) AS `time` FROM `history` WHERE `userid` = ? ORDER BY `id` DESC LIMIT 60';
$res = sqlexec($sql, [$id], $link);
if ($res) {
  while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $ips[] = [
      'ip' => $row['ip'],
      'ua' => $row['ua'],
      'ctime' => (int) $row['time'],
      'outcome' => ((int) $row['outcome']) == 0
    ];
  }
}
$result['ips'] = $ips;
$result['server_timeout'] = $SERVER_TIMEOUT;
$result['default_timeout'] = $SERVER_TIMEOUT;

ajaxSuccess($result);
