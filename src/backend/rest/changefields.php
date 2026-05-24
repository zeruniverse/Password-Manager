<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}
if (!$CUSTOMIZE_FIELDS) {
  ajaxError('parameter');
}
if (!isset($_POST['fields'])) {
  ajaxError('parameter');
}

$fields = (string) $_POST['fields'];
json_decode($fields);
if (json_last_error() !== JSON_ERROR_NONE) {
  ajaxError('parameter');
}

$sql = 'UPDATE `pwdusrrecord` SET `fields` = ? WHERE `id` = ?';
$res = sqlexec($sql, [$fields, pm_auth_userid()], $link);
if (!$res) {
  ajaxError('general');
}

ajaxSuccess();
