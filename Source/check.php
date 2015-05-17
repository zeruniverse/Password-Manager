<?php
require_once("function/encryption.php");
require_once("function/sqllink.php");
require_once("function/sendMail.php");
function sendrandemail($email,$user)
{	
	$k=mt_rand(100000,999999);
sendmail($email,"zzeyu.com Password Manager",'You ('.$user.') are using a new device to login zzeyu.com password manager.<br /><h2>Your E-mail verification code is：'.$k.'</h2><br /><span style="color:red">Please don\'t tell this code to anyone else. Please make sure you are visit domain zzeyu.com<br /><br /><strong>IF YOU DON\'T KNOW WHY YOU RECEIVE THIS E-MAIL, MODIFY YOUR PASSWORD IMMEDIATELY!!!</strong></span><br /><br />');
	return $k;
}
session_start(); 
if(isset($_SESSION['checktime']))
  {	  if($_SESSION['checktime']<3)
	  $_SESSION['checktime']=$_SESSION['checktime']+1;
	  else
	  {
		  if(!isset($_SESSION["helloweba_num"])) die("3");
		  if(safe($_POST['vericode'])!=$_SESSION["helloweba_num"]) die("3");
	  }
  }
else
     {$_SESSION['checktime']=1;}
	if($_POST['csfds']!='sdf') die("4");
	$_SESSION["helloweba_num"]="igahworgnodufvh9h";
	$usr=$_POST['user'];
	$pw=$_POST['pwd'];
	if($pw==""||$usr=="") die("0");
	
$link=sqllink();
if(!$link) die(4);
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ?";
$res=sqlexec($sql,array($usr),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) die('0');
if(strcmp(decrypt($record["password"],'zzeyucom'),$pw)!=0)die("1");
if((!isset($_COOKIE["pwdrecord".$usr])||$_COOKIE["pwdrecord".$usr]!=$record['password']) && (!isset($_SESSION["veriemail"]) || $_SESSION["veriemail"]!=$_POST['emailcode'])) {
	$_SESSION['veriemail']=sendrandemail($record['email'],$usr);	
	die("2");}
 $_SESSION['loginok']=1;
 $_SESSION['user']=$usr;
 $_SESSION['userid']=$record['id'];
 $_SESSION['pwd']=$record['password'];
	echo "9";
?>
