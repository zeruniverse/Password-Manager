<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>ZerUniverse</title>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
</head>
<body>
<!-- Begin Wrapper -->
<div style="padding:50px;">
	<!-- Begin Sidebar -->
	
	<!-- Begin Content -->
	<div id="content">
	<h1 class="title">Register</h1>
	<div class="line"></div>
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
$pw=safe($_POST['pw']);
$usr=safe($_POST['user']);
$email=addslashes($_POST['email']);
require_once("sqllink.php");
	$link=sqllink()

if(!$link) {
    die("error");
}

if($pw==''||$usr==''||$email=="")die("input error");
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num!=0) die("username already exists");
$kp=encrypt($pw,'zzeyucom');
$sql="INSERT INTO `pwdusrrecord` VALUES ('$usr','$kp',0,'$email')";
$rett=mysql_query($sql,$link);
echo($usr."<br />successful<br /><br />");
?>
	


    <!-- Begin Footer -->
    <div id="footer">
  	&copy;Jeffery Zhao; 2014. Alpha<br /><br />
    </div>
    <!-- End Footer -->
    
    
	</div>
	<!-- End Content -->

</div>
<!-- End Wrapper -->
<div class="clear"></div>


</body>
</html>