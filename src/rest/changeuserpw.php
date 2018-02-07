<?php

require_once dirname(__FILE__).'/../function/sqllink.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('general');
}
$id = $_SESSION['userid'];
$newpass = $_POST['newpass'];
// check length of password hash for pbkdf2
if (strlen($newpass) > 130) {
    ajaxError('general');
}
$accarray = json_decode($_POST['accarray']);
$salt = openssl_random_pseudo_bytes(32);

if (!$link->beginTransaction()) {
    ajaxError('general');
}
$sql = 'UPDATE `pwdusrrecord` SET `password` = ?, `salt` = ? WHERE `id` = ? ';
$newpass = hash_pbkdf2('sha256', $newpass, $salt, $PBKDF2_ITERATIONS);
$res = sqlexec($sql, [$newpass, $salt, $id], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}

$sql = 'SELECT `index` FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $sql = 'UPDATE `password` SET `name` = ?, `pwd` = ?, `other` = ? WHERE `userid` = ? AND `index` = ?';
    $resss = sqlexec($sql, [$accarray[(int) $i['index']]->name, $accarray[(int) $i['index']]->newpwd, $accarray[(int) $i['index']]->other, $id, (int) $i['index']], $link);
    if ($resss == null) {
        $link->rollBack();
        ajaxError('general');
    }
}

$sql = 'SELECT `index` FROM `files` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $sql = 'UPDATE `files` SET `key` = ?, `fname` = ? WHERE `userid` = ? AND `index` = ?';
    $resss = sqlexec($sql, [$accarray[(int) $i['index']]->fk, $accarray[(int) $i['index']]->fname, $id, (int) $i['index']], $link);
    if ($resss == null) {
        $link->rollBack();
        ajaxError('general');
    }
}

$sql = 'DELETE FROM `pin` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
$link->commit();
ajaxSuccess();
