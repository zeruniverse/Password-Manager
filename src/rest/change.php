<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (checksession($link) == false) {
    error("no session found");
}
$id = $_SESSION['userid'];
$index = $_POST['index'];
if (!$link->beginTransaction()) {
    error("Database connection failed");
}
$sql = 'SELECT * FROM `password` WHERE `userid` = ? AND `index` = ? ';
$res = sqlexec($sql, [$id, (int) $index], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    $link->commit();
    error("Entry not found");
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
    error("Updating entry failed");
}
$link->commit();
echo json_encode(['status' => "success"]);
die();
