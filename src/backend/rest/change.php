<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$index = isset($_POST['index']) ? (int) $_POST['index'] : -1;
if ($index < 0) {
  ajaxError('parameter');
}

if (!$link->beginTransaction()) {
  ajaxError('general');
}

$sql = 'SELECT * FROM `password` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
if (!$res || !$res->fetch(PDO::FETCH_ASSOC)) {
  $link->rollBack();
  ajaxError('entryNotFound');
}

$parts = [];
$params = [];
if (isset($_POST['kss'])) {
  $parts[] = '`pwd` = ?';
  $params[] = (string) $_POST['kss'];
}
if (isset($_POST['name'])) {
  $parts[] = '`name` = ?';
  $params[] = (string) $_POST['name'];
}
if (isset($_POST['other'])) {
  $parts[] = '`other` = ?';
  $params[] = (string) $_POST['other'];
}
if (count($parts) === 0) {
  $link->rollBack();
  ajaxError('parameter');
}

$params[] = $id;
$params[] = $index;
$sql = 'UPDATE `password` SET ' . implode(', ', $parts) . ' WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, $params, $link);
if (!$res) {
  $link->rollBack();
  ajaxError('updateFailed');
}

$link->commit();
ajaxSuccess();
