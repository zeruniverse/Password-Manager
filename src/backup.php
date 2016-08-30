<?php
if(!isset($_POST['a']) || $_POST['a']!='a') die('Forbidden'); //disallow direct visit;
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();

if(checksession($link)==FALSE) {die('0');}
$id=$_SESSION['userid'];
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