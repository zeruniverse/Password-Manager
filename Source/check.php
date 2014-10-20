<?php
function sendrandemail($email,$user)
{	
	$k=mt_rand(100000,999999);
	sendmail($email,"Email Verify",'You('.$user.')are trying to login to the password manager<br /><h2>Your varify code is '.$k.'</h2><br /><span style="color:red">Do not tell anyone this code.<br /><strong>If this mail is sent without your request, modify your password immediately.</strong></span>');
	return $k;
}
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
function decrypt($data, $key)
{
	$key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}
?>
<?php
require_once("mail/sendMail.php");
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
	$usr=safe($_POST['user']);
	$pw=safe($_POST['pwd']);
	require_once("sqllink.php");
	if($pw==""||$usr=="") die("0");
	$link=sqllink()

if(!$link) {
    die("4");
}
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) die("0");
$record=mysql_fetch_array($rett);
if(strcmp(decrypt($record["pwd"],'zzeyucom'),$pw)!=0)die("1");
if((!isset($_COOKIE["pwdrecord".$usr])||$_COOKIE["pwdrecord".$usr]!=$record['pwd']) && (!isset($_SESSION["veriemail"]) || $_SESSION["veriemail"]!=$_POST['emailcode'])) {
	$_SESSION['veriemail']=sendrandemail($record['email'],$usr);
	
	die("2");}
 $_SESSION['loginok']=1;
 $_SESSION['user']=$usr;
 $_SESSION['pwd']=$record['pwd'];
	echo "9";
?>