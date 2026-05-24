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

$sql = 'SELECT max(`index`) AS `m` FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$row = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
$max = (!$row || $row['m'] === null) ? $index : (int) $row['m'];

$res = sqlexec('DELETE FROM `password` WHERE `userid` = ? AND `index` = ?', [$id, $index], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}
$res = sqlexec('DELETE FROM `files` WHERE `userid` = ? AND `index` = ?', [$id, $index], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}

if ($max !== $index) {
  $res = sqlexec('UPDATE `password` SET `index` = ? WHERE `userid` = ? AND `index` = ?', [$index, $id, $max], $link);
  if (!$res) {
    $link->rollBack();
    ajaxError('general');
  }
  $res = sqlexec('UPDATE `files` SET `index` = ? WHERE `userid` = ? AND `index` = ?', [$index, $id, $max], $link);
  if (!$res) {
    $link->rollBack();
    ajaxError('general');
  }
}

$link->commit();
ajaxSuccess();
