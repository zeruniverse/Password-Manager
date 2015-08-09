<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();die("0");}
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) die(0);
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();die("0");}
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();die("0");}

$ppwd=$_POST['newpwd'];
$pubkey=mt_rand(10000000,99999999);
$newpw=encrypt($ppwd,$pubkey);
$name=$_POST['name']; 

if(!$link->beginTransaction()) die('0');

$sql="SELECT max(`index`) FROM `password` WHERE `userid`= ?";
$res=sqlexec($sql,array($id),$link);
$record= $res->fetch(PDO::FETCH_NUM);
if($record==FALSE) $nid=1; else $nid=(int)$record[0]+1;

$sql="INSERT INTO `password` VALUES (?, ?, ?, ?,?)";
$res=sqlexec($sql,array($nid,$id,$name,$newpw,$pubkey),$link);
if($record==NULL) {$link->rollBack();die(0);}
$link->commit();
echo "1";
?>