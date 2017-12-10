<?php

require_once dirname(__FILE__).'/../function/ajax.php';
require_once dirname(__FILE__).'/../function/sqllink.php';
$link = sqllink();
if (!checksession($link)) {
}
$id = $_SESSION['userid'];

$index = (int) $_POST['index'];
if (!$link->beginTransaction()) {
    error('error occured');
}

$sql = 'SELECT * FROM `password` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    $link->commit();
    error('error occured');
}

$sql = 'SELECT max(`index`) FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$record = $res->fetch(PDO::FETCH_NUM);
if ($record == false) {
    $link->commit();
    error('error occured');
}
$nid = (int) $record[0];

$sql = 'DELETE FROM `password` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
if ($res == null) {
    $link->rollBack();
    error('error occured');
}

$sql = 'DELETE FROM `files` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
if ($res == null) {
    $link->rollBack();
    error('error occured');
}

$sql = 'UPDATE `password` SET `index` = ?  WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$index, $id, $nid], $link);
if ($res == null) {
    $link->rollBack();
    error('error occured');
}

$sql = 'UPDATE `files` SET `index` = ?  WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$index, $id, $nid], $link);
if ($res == null) {
    $link->rollBack();
    error('error occured');
}

$link->commit();

echo json_encode(['status' => "success"]);
