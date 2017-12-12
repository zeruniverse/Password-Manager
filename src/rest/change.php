<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (checksession($link) == false) {
    ajaxError("session");
}
$id = $_SESSION['userid'];
$index = $_POST['index'];
if (!$link->beginTransaction()) {
    ajaxError("general");
}
$sql = 'SELECT * FROM `password` WHERE `userid` = ? AND `index` = ? ';
$res = sqlexec($sql, [$id, (int) $index], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    $link->commit();
    ajaxError("entryNotFound");
}
$newpw = $_POST['newpwd'];
$changedCols = '`pwd` = ?';
$values = [$newpw];
if (isset($_POST['name'])) {
    $changedCols .= ' ,`name` = ?';
    array_push($values, $_POST['name']);
}
if (isset($_POST['other'])) {
    $changedCols .= ' ,`other` = ?';
    array_push($values, $_POST['other']);
}
array_push($values, $id, (int) $index);
$sql = 'UPDATE `password` SET '.$changedCols.' WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, $values, $link);
if ($res == null) {
    $link->rollBack();
    ajaxError("updateFailed");
}
$link->commit();
ajaxSuccess();
