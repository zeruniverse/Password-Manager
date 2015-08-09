<?php
session_start();
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();die("0");}
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();die("0");}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();die("0");}
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();die("0");}

$newpass=$_POST['newpass'];
$accarray=json_decode($_POST['accarray']);
$passarray=json_decode($_POST['passarray']);
$newpass=encrypt($newpass,$GLOBAL_SALT_3);

if(!$link->beginTransaction()) die('0');
$sql="UPDATE `pwdusrrecord` SET `password`= ? WHERE `id`= ? ";
$res=sqlexec($sql,array($newpass, $id),$link);
if($res==NULL) {$link->rollBack();die("0");}

$sql="SELECT `index` FROM `password` WHERE `userid`= ?";
$res=sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC))
{
    $pubkey=mt_rand(10000000,99999999);
    $storepw=encrypt($passarray[(int)$i["index"]],$pubkey);
    $sql="UPDATE `password` SET `name`= ?, `pwd`=?, `key`=? WHERE `userid`=? AND `index`= ?";
    $resss=sqlexec($sql,array($accarray[(int)$i["index"]], $storepw,$pubkey,$id,(int)$i['index']),$link);
    if($resss==NULL) {$link->rollBack();die("0");}
}
$link->commit();
echo "1";
?>