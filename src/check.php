<?php
require_once("function/encryption.php");
require_once("function/sqllink.php");
session_start(); 
if(!isset($_SESSION['random_login_stamp']) || $_SESSION['random_login_stamp']=='') {die("4");}
$usr=$_POST['user'];
$pw=$_POST['pwd'];
if($pw==""||$usr=="") die("0");
$link=sqllink();
if(!$link) die('4');
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ?";
$res=sqlexec($sql,array($usr),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) die('0');
if(strcmp(hash('sha512',(string)decrypt($record["password"],$GLOBAL_SALT_3).(string)$_SESSION['random_login_stamp']),$pw)!=0) die("1");
$_SESSION['loginok']=1;
$_SESSION['user']=$usr;
$_SESSION['userid']=$record['id'];
$_SESSION['pwd']=$record['password'];
echo "9";
?>
