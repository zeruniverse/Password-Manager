<?php
function random_str($length)
{
    $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
 
    $str = '';
    $arr_len = count($arr);
    for ($i = 0; $i < $length; $i++)
    {
        $rand = mt_rand(0, $arr_len-1);
        $str.=$arr[$rand];
    }
 
    return $str;
}
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();die("0");}
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) die(0);
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();die("0");}
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();die("0");}

$username=$_POST['user'];
$device=$_POST['device'];
$sig=$_POST['sig'];

if($username!=$usr) die('0');
$sql="DELETE FROM `pin` WHERE `userid`= ? AND `device`= ?";
$res=sqlexec($sql,array($id,$device),$link);

$pinpk=random_str(29);

$sql="INSERT INTO `pin` (`userid`,`device`,`pinsig`,`pinpk`,`ua`) VALUES (?,?,?,?,?)";
$res=sqlexec($sql,array($id,$device,$sig,$pinpk,$_SERVER['HTTP_USER_AGENT']),$link);

echo $pinpk;
?>