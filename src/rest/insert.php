<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
$link = sqllink();
if (!checksession($link)) {
    die('0');
}
$id = $_SESSION['userid'];

$newpw = $_POST['newpwd'];
$name = $_POST['name'];
$other = $_POST['other'];

if (!$link->beginTransaction()) {
    die('0');
}

$sql = 'SELECT max(`index`) FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$record = $res->fetch(PDO::FETCH_NUM);
if ($record == false) {
    $nid = 1;
} else {
    $nid = (int) $record[0] + 1;
}

$sql = 'INSERT INTO `password` VALUES (?, ?, ?, ?, ?)';
$res = sqlexec($sql, [$nid, $id, $name, $newpw, $other], $link);
if ($res == null) {
    $link->rollBack();
    die(0);
}
$link->commit();
echo $nid;
