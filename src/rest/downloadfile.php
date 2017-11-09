<?php

require_once dirname(__FILE__).'/../function/ajax.php';
require_once dirname(__FILE__).'/../function/sqllink.php';
$link = sqllink();
if (!checksession($link)) {
    error('AUTHENTICATION ERROR, PLEASE RELOGIN');
}
$id = $_SESSION['userid'];
if (!isset($_POST['id']) || (int) $_POST['id'] < 1) {
    error('Parameter Error');
}
$index = (int) $_POST['id'];

$sql = 'SELECT `key`,`files` FROM `files` WHERE `userid`= ? and `index`=?';
$res = sqlexec($sql, [$id, $index], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    error('No File Can Be Downloaded');
}
$result = [];
$result['status'] = 'success';
$result['key'] = $record['key'];
$result['data'] = $record['files'];

echo json_encode($result);
