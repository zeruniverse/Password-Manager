<?php
require_once(dirname(__FILE__)."/../function/sqllink.php");
$link = sqllink();
if(!checksession($link)) {
    die("0");
}
$id = $_SESSION['userid'];
$newpass = $_POST['newpass'];
$accarray = json_decode($_POST['accarray']);
$passarray = json_decode($_POST['passarray']);

if(!$link->beginTransaction()) {
    die('0');
}
$sql = "UPDATE `pwdusrrecord` SET `password` = ? WHERE `id` = ? ";
$res = sqlexec($sql,array($newpass, $id),$link);
if($res == NULL) {
    $link->rollBack();
    die("0");
}

$sql = "SELECT `index` FROM `password` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $storepw = $passarray[(int)$i["index"]]->pw;
    $sql = "UPDATE `password` SET `name` = ?, `pwd` = ?, `other` = ? WHERE `userid` = ? AND `index` = ?";
    $resss = sqlexec($sql,array($accarray[(int)$i["index"]]->name, $storepw,$accarray[(int)$i["index"]]->other,$id,(int)$i['index']),$link);
    if($resss == NULL) {
        $link->rollBack();
        die("0");
    }
}

$sql = "SELECT `index` FROM `files` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $sql = "UPDATE `files` SET `key` = ?, `fname` = ? WHERE `userid` = ? AND `index` = ?";
    $resss = sqlexec($sql,array($passarray[(int)$i["index"]]->fk, $accarray[(int)$i["index"]]->fname, $id, (int)$i['index']),$link);
    if($resss == NULL) {
        $link->rollBack();
        die("0");
    }
}

$sql = "DELETE FROM `pin` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
$link->commit();
echo "1";
?>
