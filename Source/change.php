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
require_once("sqllink.php");	
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) { session_destroy();die("0");}
if($_POST['csfds']!='sdf') die("0");
$link=sqllink();
if(!$link) 
   {session_destroy();die("0");}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
if($pw==""||$usr==""){session_destroy();die("0");}
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr' AND `pwd`='$pw'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {session_destroy();die("0");}
$index=safe($_POST['index']);
$name=addslashes($_POST['name']); 
$sql="SELECT * FROM `password` WHERE `usr`='$usr' AND `index`=$index AND `name`='$name'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
$ppwd=$_POST['newpwd'];
if($num==0) {die("0");}
$pubkey=mt_rand(10000000,99999999);
$newpw=encrypt($ppwd,$pubkey);
$oldpw=addslashes($_POST['oldpw']);
$sql="UPDATE `password` SET `pubkey` = '$pubkey' ,`pwd` = '$newpw' ,`lastpwd` = '$oldpw'  WHERE `usr` = '$usr' AND `index`=$index AND `name`='$name'";
$rett=mysql_query($sql,$link);
echo "1";
?>