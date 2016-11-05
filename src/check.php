<?php
require_once("function/encryption.php");
require_once("function/sqllink.php");
session_start();
$currentCookieParams = session_get_cookie_params();  
session_regenerate_id(true);
$sidvalue = session_id();  
setcookie(  
    session_name(),//name  
    $sidvalue,//value  
    0,//expires at end of session  
    $currentCookieParams['path'],//path  
    $currentCookieParams['domain'],//domain  
    $currentCookieParams['secure'], //secure 
    true 
);
function getUserIP()
{
    $remote  = $_SERVER['REMOTE_ADDR'];
    return $remote;
}
function loghistory($link,$userid,$ip,$ua,$outcome)
{
	$sql="SELECT max(`id`) AS `m` FROM `history`";
	$res=sqlquery($sql,$link);
	$r= $res->fetch(PDO::FETCH_ASSOC);
	$i=($r==FALSE)?0:((int)$r['m'])+1;
	$sql="INSERT INTO `history` VALUES (?,?,?,?,?,CURRENT_TIMESTAMP)";
	$res=sqlexec($sql,array($i,$userid,$ip,$ua,$outcome),$link);
}
if(!isset($_SESSION['random_login_stamp']) || $_SESSION['random_login_stamp']=='') {die("4");}
$usr=$_POST['user'];
$pw=$_POST['pwd'];
if($pw==""||$usr==""||$_POST['session_token']=='') die("0");
$link=sqllink();
if(!$link) die('4');

//Clear Up.
$sql="DELETE FROM `blockip` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?";
$res=sqlexec($sql,array($BLOCK_IP_TIME),$link);
$sql="DELETE FROM `history` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?";
$res=sqlexec($sql,array($LOG_EXPIRE_TIME),$link);

//check if IP is blocked
$sql="SELECT * FROM `blockip` WHERE `ip`= ?";
$res=sqlexec($sql,array(getUserIP()),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record!=FALSE) die('7');

$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ?";
$res=sqlexec($sql,array($usr),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) die('0');

$sql="SELECT count(*) as `m` FROM `history` WHERE `userid`= ? AND outcome=0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?";
$res=sqlexec($sql,array((int)$record["id"],$ACCOUNT_BAN_TIME),$link);
$count= $res->fetch(PDO::FETCH_ASSOC);
if((int)$count['m']>=$BLOCK_ACCOUNT_TRY) die('8');

if(strcmp(hash('sha512',(string)decrypt($record["password"],$GLOBAL_SALT_3).(string)$_SESSION['random_login_stamp']),$pw)!=0) {
    loghistory($link,(int)$record["id"],getUserIP(),$_SERVER['HTTP_USER_AGENT'],0);
    $sql="SELECT count(*) as `m` FROM `history` WHERE `ip`= ? AND outcome=0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?";
    $res=sqlexec($sql,array(getUserIP(),$BLOCK_IP_TIME),$link);
    $count= $res->fetch(PDO::FETCH_ASSOC);
    if((int)$count['m']>=$BLOCK_IP_TRY){
        $sql="INSERT INTO `blockip` VALUES (?,CURRENT_TIMESTAMP)";
        $res=sqlexec($sql,array(getUserIP()),$link);
    }
    die("1");
}
$_SESSION['loginok']=1;
$_SESSION['user']=$usr;
$_SESSION['userid']=$record['id'];
$_SESSION['pwd']=$record['password'];
$_SESSION['fields']=$record['fields'];
$_SESSION['create_time']=time();
$_SESSION['session_token']=$_POST['session_token'];
loghistory($link,(int)$record["id"],getUserIP(),$_SERVER['HTTP_USER_AGENT'],1);
echo "9";
?>
