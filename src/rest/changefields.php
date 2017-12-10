<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
if (!$CUSTOMIZE_FIELDS) {
    http_response_code(405);
    ajaxError('method');
}
$link = sqllink();
if (!isset($_POST['fields'])) {
    ajaxError('parameter');
}
if (checksession($link) == false) {
    ajaxError('session');
}
$id = $_SESSION['userid'];
$sql = 'UPDATE `pwdusrrecord` SET `fields` = ? WHERE `id` = ? ';
$res = sqlexec($sql, [$_POST['fields'], $id], $link);
$_SESSION['fields'] = $_POST['fields'];
ajaxSuccess();
