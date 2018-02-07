<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('general');
}
$id = $_SESSION['userid'];

$index = (int) $_POST['id'];
$fkey = $_POST['fkey'];
$fname = $_POST['fname'];
$data = $_POST['data'];

if ($fname == '' || $fkey == '' || $data == '' || $index < 1) {
    ajaxError('parameter');
}
if (strlen($fkey) > 100 || strlen($fname) > 100 || strlen($data) > 1024 * 1024 * 15) {
    ajaxError('parameter');
}

if (!$link->beginTransaction()) {
    ajaxError('general');
}

$sql = 'DELETE FROM `files` WHERE `userid`= ? and `index`=?';
$res = sqlexec($sql, [$id, $index], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}

$sql = 'SELECT * FROM `password` WHERE `userid`= ? AND `index`= ?';
$res = sqlexec($sql, [$id, $index], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record == false) {
    $link->rollBack();
    ajaxError('general');
}

$sql = 'INSERT INTO `files` VALUES (?, ?, ?, ?,?)';
$stmt = $link->prepare($sql);
$stmt->bindParam(1, $id);
$stmt->bindParam(2, $index);
$stmt->bindParam(3, $fkey);
$stmt->bindParam(4, $fname);
$stmt->bindParam(5, $data, PDO::PARAM_LOB);
$exeres = $stmt->execute();
if ($exeres == false) {
    $link->rollBack();
    ajaxError('general');
}
$link->commit();
ajaxSuccess();
