<?php
require_once("function/encryption.php");
require_once("function/sqllink.php");
session_start(); 
$usr=$_POST['user'];
$pw=$_POST['pwd'];
if($pw==""||$usr=="") die("0");
$link=sqllink();
if(!$link) die('4');
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ?";
$res=sqlexec($sql,array($usr),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) die('0');
if(strcmp(decrypt($record["password"],$GLOBAL_SALT_3),$pw)!=0)die("1");
$_SESSION['loginok']=1;
$_SESSION['user']=$usr;
$_SESSION['userid']=$record['id'];
$_SESSION['pwd']=$record['password'];
echo "9";
?>
