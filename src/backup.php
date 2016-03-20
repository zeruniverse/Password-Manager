<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {die('0');}
if(!isset($_POST['a']) || $_POST['a']!='a') die('Forbidden'); //disallow direct visit;
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {die('0');}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {die('0');}
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {die('0');}
$arr=array();
$sql="SELECT * FROM `password` WHERE `userid`= ?";
$res=sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $index=$i['index'];
	$name=$i['name'];
    $other=$i['other'];
	$kss=decrypt($i['pwd'],$i['key']);
	$arr[(int)$index]=array($name,$kss,$other);
}
$ret=array('status'=>'OK', 'VERSION'=>$VERSION, 'JSsalt'=>$GLOBAL_SALT_1, 'PWsalt'=>$GLOBAL_SALT_2, 'ALPHABET'=>$DEFAULT_LETTER_USED, 'data'=>$arr);
echo json_encode($ret);
?>