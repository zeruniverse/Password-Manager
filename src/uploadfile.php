<?php
require_once("function/sqllink.php");
$link=sqllink();
if(!checksession($link)) die('0');
$id = $_SESSION['userid'];

$index=(int)$_POST['id'];
$fkey=$_POST['fkey'];
$fname=$_POST['fname'];
$data=$_POST['data'];

if($fname=='' || $fkey=='' || $data=='' || $index<1) die('0');
if(strlen($fkey)>100 || strlen($fname)>100||strlen($data)>1024*1024*15) die('0');

if(!$link->beginTransaction()) die('0');

$sql="DELETE FROM `files` WHERE `userid`= ? and `index`=?";
$res=sqlexec($sql,array($id,$index),$link);
if($res==NULL) {$link->rollBack();die(0);}

$sql="SELECT * FROM `password` WHERE `userid`= ? AND `index`= ?";
$res=sqlexec($sql,array($id,$index),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {$link->rollBack();die("0");}

$sql="INSERT INTO `files` VALUES (?, ?, ?, ?,?)";
$res=sqlexec($sql,array($id,$index,$fkey,$fname,$data),$link);
if($res==NULL) {$link->rollBack();die(0);}
$link->commit();
echo "1";
?>
