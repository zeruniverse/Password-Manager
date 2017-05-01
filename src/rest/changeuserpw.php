<?php
require_once(dirname(__FILE__)."/../function/sqllink.php");
$link = sqllink();
if(!checksession($link)) {
    die("0");
}
$id = $_SESSION['userid'];
$newpass = $_POST['newpass'];
// check length of password hash for pbkdf2
if (strlen($newpass) > 130) {
    die('0');
}
$accarray = json_decode($_POST['accarray']);
$salt = openssl_random_pseudo_bytes(32);

if(!$link->beginTransaction()) {
    die('0');
}
$sql = "UPDATE `pwdusrrecord` SET `password` = ?, `salt` = ? WHERE `id` = ? ";
$newpass = hash_pbkdf2('sha256',$newpass,$salt,$PBKDF2_ITERATIONS);
$res = sqlexec($sql,array($newpass, $salt, $id),$link);
if($res == NULL) {
    $link->rollBack();
    die("0");
}

$sql = "SELECT `index` FROM `password` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $sql = "UPDATE `password` SET `name` = ?, `pwd` = ?, `other` = ? WHERE `userid` = ? AND `index` = ?";
    $resss = sqlexec($sql,array($accarray[(int)$i["index"]]->name, $accarray[(int)$i["index"]]->newpwd, $accarray[(int)$i["index"]]->other,$id,(int)$i['index']),$link);
    if($resss == NULL) {
        $link->rollBack();
        die("0");
    }
}

$sql = "SELECT `index` FROM `files` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
    $sql = "UPDATE `files` SET `key` = ?, `fname` = ? WHERE `userid` = ? AND `index` = ?";
    $resss = sqlexec($sql,array($accarray[(int)$i["index"]]->fk, $accarray[(int)$i["index"]]->fname, $id, (int)$i['index']),$link);
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
