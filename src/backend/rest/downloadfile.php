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
if ($index < 0) {
  ajaxError('parameter');
}

$sql = 'SELECT `key`, `fname`, `files` FROM `files` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
$row = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if (!$row) {
  ajaxError('fileFailed');
}

ajaxSuccess([
  'key' => $row['key'],
  'fname' => $row['fname'],
  'data' => $row['files']
]);
