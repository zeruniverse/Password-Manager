<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}
if (!$FILE_ENABLED) {
  ajaxError('parameter');
}

$id = pm_auth_userid();
$index = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_POST['index']) ? (int) $_POST['index'] : -1);
$fkey = isset($_POST['fkey']) ? (string) $_POST['fkey'] : '';
$fname = isset($_POST['fname']) ? (string) $_POST['fname'] : '';
$data = isset($_POST['data']) ? (string) $_POST['data'] : '';

if ($index < 0 || $fkey === '' || $fname === '' || $data === '') {
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

sqlexec('DELETE FROM `files` WHERE `userid` = ? AND `index` = ?', [$id, $index], $link);
$sql = 'INSERT INTO `files` VALUES (?, ?, ?, ?, ?)';
$res = sqlexec($sql, [$id, $index, $fkey, $fname, $data], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}

$link->commit();
ajaxSuccess();
