<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('general');
}
$id = $_SESSION['userid'];

$newpw = $_POST['kss'];
$name = $_POST['name'];
$other = $_POST['other'];

if (!$link->beginTransaction()) {
    ajaxError('general');
}

$sql = 'SELECT max(`index`) FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$record = $res->fetch(PDO::FETCH_NUM);
if (!$record) {
    $nid = 1;
} else {
    $nid = (int) $record[0] + 1;
}

$sql = 'INSERT INTO `password` VALUES (?, ?, ?, ?, ?)';
$res = sqlexec($sql, [$nid, $id, $name, $newpw, $other], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}
$link->commit();
ajaxSuccess(['nid' => $nid]);
