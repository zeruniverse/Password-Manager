<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();header("Location: ./");die();}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();header("Location: ./");die();}
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();header("Location: ./");die();}
setcookie("pwdrecord".$usr,$pw, time()+5160000);
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="backup.json"');
$arr=array();
$sql="SELECT * FROM `password` WHERE `userid`= ?";
$res=sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $index=$i['index'];
	$name=$i['name'];
	$kss=decrypt($i['pwd'],$i['pubkey']);
	$arr[(int)$index]=array($name,$kss);
}
$ret=array('status'=>'OK','data'=>$arr);
echo json_encode($ret);
?>
	