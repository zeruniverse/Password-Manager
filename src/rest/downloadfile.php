<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('authentication');
}
$id = $_SESSION['userid'];
if (!isset($_POST['id']) || (int) $_POST['id'] < 1) {
    ajaxError('parameter');
}
$index = (int) $_POST['id'];

$sql = 'SELECT `key`,`files` FROM `files` WHERE `userid`= ? and `index`=?';
$res = sqlexec($sql, [$id, $index], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    ajaxError('fileFailed');
}
$result = [];
$result['key'] = $record['key'];
$result['data'] = $record['files'];

ajaxSuccess($result);
