<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("../../functionzzeyu/sqllink.php");
require_once("../../functionzzeyu/basic.php");
$link=sqllink();
if(!$link) die();
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
if($usr==""||$pw=="")  {session_destroy();header("Location: ./");die();}
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr' AND `pwd`='$pw'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {session_destroy();header("Location: ./");die();}
setcookie("pwdrecord".$usr,$pw, time()+5160000);
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="backup.json"');
?>
<?php
function decrypt($data, $key)
{
	$key = md5($key);
    $x = 0;
	$char='';
	$str='';
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

$arr=array();
$sql="SELECT * FROM `password` WHERE `usr`='$usr'";
$rett=mysql_query($sql, $link);
while ($i = mysql_fetch_array($rett)){ //返回查询结果
	$index=$i['index'];
	$name=$i['name'];
	$kss=decrypt($i['pwd'],$i['pubkey']);
	$arr[(int)$index]=array($name,$kss);
}
$ret=array('status'=>'OK','data'=>$arr);
echo json_encode($ret);
?>
	