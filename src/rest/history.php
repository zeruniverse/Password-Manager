<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('session');
}
$id = $_SESSION['userid'];
$result = [];
$result['id'] = $id;
$result['usr'] = $_SESSION['user'];
$sql = 'SELECT `device`,UNIX_TIMESTAMP(`createtime`) AS `createtime`,`ua` FROM `pin` WHERE `userid`= ?';
$res = sqlexec($sql, [$id], $link);
$pins = [];
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $did = $i['device'];
    $ctime = (int) $i['createtime'];
    $ua = $i['ua'];
    $pins[] = ['ua' => $ua, 'ctime' => $ctime, 'did' => $did];
}
$result['pins'] = $pins;

$sql = 'SELECT `ip`,`ua`,`outcome`,UNIX_TIMESTAMP(`time`) AS `time` FROM `history` WHERE `userid`= ?
    ORDER BY `id` DESC LIMIT 60';
$res = sqlexec($sql, [$id], $link);
$ips = [];
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $ip = $i['ip'];
    $ua = $i['ua'];
    $ctime = (int) $i['time'];
    $ips[] = ['ip' => $ip, 'ua' => $ua, 'ctime' => $ctime, 'outcome' => $i['outcome'] == 0];
}
$result['ips'] = $ips;
$result['default_timeout'] = $BROWSER_TIMEOUT;
$result['server_timeout'] = $SERVER_TIMEOUT;
ajaxSuccess($result);
