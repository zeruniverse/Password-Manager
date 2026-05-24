<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$index = isset($_POST['index']) ? (int) $_POST['index'] : (isset($_POST['id']) ? (int) $_POST['id'] : -1);
if ($index < 0) {
  ajaxError('parameter');
}

$res = sqlexec('DELETE FROM `files` WHERE `userid` = ? AND `index` = ?', [$id, $index], $link);
if (!$res) {
  ajaxError('general');
}
ajaxSuccess();
