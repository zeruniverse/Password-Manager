<?php
//return:
//0 - FORCE TO USE USERNAME/PASSWORD
//1 - PIN error
//otherwise - pk
session_start(); 
require_once("function/sqllink.php");
$link=sqllink();
if(!$link) die(0);
$user=$_POST['user'];
$device=$_POST['device'];
if($user==""||$device=="")  {die("0");}
$sql="SELECT `id` FROM `pwdusrrecord` WHERE `username`= ?";
$res=sqlexec($sql,array($user),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {die("0");}
$id = $record['id'];
$sql="DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?";
$res=sqlexec($sql,array($PIN_EXPIRE_TIME),$link);
$sql="SELECT `pinsig`,`pinpk` FROM `pin` WHERE `userid`= ? AND `device`=?";
$res=sqlexec($sql,array($id,$device),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {die("0");}
$sig=$record['pinsig'];
if(strcmp(hash('sha512',(string)$sig.(string)$_SESSION['random_login_stamp']),(string)$_POST['sig'])==0) {
    $sql="UPDATE `pin` SET `errortimes`=0 WHERE `userid`= ? AND `device`=?";
    $res=sqlexec($sql,array($id,$device),$link);
    die($record['pinpk']);
}
$sql="UPDATE `pin` SET `errortimes`=`errortimes`+1 WHERE `userid`= ? AND `device`=?";
$res=sqlexec($sql,array($id,$device),$link);
die('1');
?>