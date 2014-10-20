<?php
function safe($str)
{
	$len=strlen($str);
	$re='';
	for($i=0;$i<$len;$i++){
	$c=substr($str,$i,1);
	if(ord('0')<=ord($c) && ord('9')>=ord($c))$re=$re.$c;
	if(ord('a')<=ord($c) && ord('z')>=ord($c))$re=$re.$c;
	if(ord('A')<=ord($c) && ord('Z')>=ord($c))$re=$re.$c;
	}
	return $re;
}
	
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();die("0");}
if($_POST['csfds']!='sdf') die("0");
require_once("sqllink.php");
	$link=sqllink()

if(!$link) {
    die("0");
}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
if($pw==""||$usr==""){session_destroy();die("0");}
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr' AND `pwd`='$pw'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {session_destroy();die("0");}
$i = mysql_fetch_array($rett);
$recordnum=$i['num'];
$index=safe($_POST['index']);
$name=addslashes($_POST['name']); 
$sql="SELECT * FROM `password` WHERE `usr`='$usr' AND `index`=$index AND `name`='$name'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {die("0");}
$sql="SELECT * FROM `password` WHERE `usr`='$usr' AND `index`=$recordnum ";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {die("0");}
$i = mysql_fetch_array($rett);
$oldname=$i['name'];
$oldpwd=$i['pwd'];
$oldpubkey=$i['pubkey'];
$oldlastpwd=$i['lastpwd'];
$sql="UPDATE `password` SET `pubkey` = '$oldpubkey' ,`pwd` = '$oldpwd' ,`lastpwd` = '$oldlastpwd' ,`name` = '$oldname' WHERE `usr` = '$usr' AND `index`=$index AND `name`='$name'";
$rett=mysql_query($sql,$link);
$sql="DELETE FROM `password` WHERE `usr`='$usr' AND `index`='$recordnum'";
$rett=mysql_query($sql,$link);
$sql="UPDATE `pwdusrrecord` SET `num` = $recordnum-1  WHERE `usr`='$usr' AND `pwd`='$pw'";
$rett=mysql_query($sql,$link);
echo "1";
?>