<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('general');
}
$id = $_SESSION['userid'];

$index = (int) $_POST['index'];
if (!$link->beginTransaction()) {
    ajaxError('general');
}

$sql = 'SELECT * FROM `password` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if (!$record) {
    $link->commit();
    ajaxError('general');
}

$sql = 'SELECT max(`index`) FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$record = $res->fetch(PDO::FETCH_NUM);
if (!$record) {
    $link->commit();
    ajaxError('general');
}
$nid = (int) $record[0];

$sql = 'DELETE FROM `password` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}

$sql = 'DELETE FROM `files` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}

$sql = 'UPDATE `password` SET `index` = ?  WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$index, $id, $nid], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}

$sql = 'UPDATE `files` SET `index` = ?  WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$index, $id, $nid], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}

$link->commit();

ajaxSuccess();
