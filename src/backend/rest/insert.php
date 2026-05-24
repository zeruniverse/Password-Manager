<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$name = isset($_POST['name']) ? (string) $_POST['name'] : '';
$kss = isset($_POST['kss']) ? (string) $_POST['kss'] : '';
$other = isset($_POST['other']) ? (string) $_POST['other'] : '';

if ($name === '' || $kss === '') {
  ajaxError('parameter');
}

if (!$link->beginTransaction()) {
  ajaxError('general');
}

$sql = 'SELECT max(`index`) AS `m` FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$row = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
$nid = (!$row || $row['m'] === null) ? 0 : ((int) $row['m']) + 1;

$sql = 'INSERT INTO `password` VALUES (?, ?, ?, ?, ?)';
$res = sqlexec($sql, [$nid, $id, $name, $kss, $other], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}

$link->commit();
ajaxSuccess(['nid' => $nid]);
