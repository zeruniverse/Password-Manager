<?php
function encrypt($data, $key)
{
	$key	=	md5($key);
    $x		=	0;
    $len	=	strlen($data);
    $l		=	strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}
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
if($usr==""||$pw==""){session_destroy();die("0");}
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr' AND `pwd`='$pw'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {session_destroy();die("0");}
$i = mysql_fetch_array($rett);
$recordnum=$i['num'];
$recordnum=$recordnum+1;
$ppwd=$_POST['newpwd'];
$pubkey=mt_rand(10000000,99999999);
$newpw=encrypt($ppwd,$pubkey);
$name=addslashes($_POST['name']); 
$sql="INSERT INTO `password` VALUES ($recordnum, '$usr', '$name', '$newpw','','$pubkey')";
$rett=mysql_query($sql,$link);
$sql="UPDATE `pwdusrrecord` SET `num` = $recordnum  WHERE `usr`='$usr' AND `pwd`='$pw'";

$rett=mysql_query($sql,$link);
echo "1";
?>