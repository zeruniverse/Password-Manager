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

$index=(int)$_POST['index'];
if(!$link->beginTransaction()) die('0');

$sql="SELECT * FROM `password` WHERE `userid`= ? AND `index`= ?";
$res=sqlexec($sql,array($id,$index),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {$link->commit();die("0");}

$sql="SELECT max(`index`) FROM `password` WHERE `userid`= ?";
$res=sqlexec($sql,array($id),$link);
$record= $res->fetch(PDO::FETCH_NUM);
if($record==FALSE) {$link->commit();die("0");}
$nid=(int)$record[0];

$sql="DELETE FROM `password` WHERE `userid`= ? AND `index`= ?";
$res=sqlexec($sql,array($id,$index),$link);
if($res==NULL) {$link->rollBack();die("0");}

$sql="UPDATE `password` SET `index` = ?  WHERE `userid`=? AND `index`= ?";
$res=sqlexec($sql,array($index,$id,$nid),$link);
if($res==NULL) {$link->rollBack();die("0");}

$link->commit();
echo "1";
?>