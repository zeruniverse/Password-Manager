<?php
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!checksession($link)) die('0');
$id = $_SESSION['userid'];

$ppwd=$_POST['newpwd'];
$pubkey=mt_rand(10000000,99999999);
$newpw=encrypt($ppwd,$pubkey);
$name=$_POST['name']; 
$other=$_POST['other'];

if(!$link->beginTransaction()) die('0');

$sql="SELECT max(`index`) FROM `password` WHERE `userid`= ?";
$res=sqlexec($sql,array($id),$link);
$record= $res->fetch(PDO::FETCH_NUM);
if($record==FALSE) $nid=1; else $nid=(int)$record[0]+1;

$sql="INSERT INTO `password` VALUES (?, ?, ?, ?,?,?)";
$res=sqlexec($sql,array($nid,$id,$name,$newpw,$pubkey,$other),$link);
if($record==NULL) {$link->rollBack();die(0);}
$link->commit();
echo "1";
?>
