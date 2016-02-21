<?php
require_once("function/sqllink.php");
$link=sqllink();
if(!$link) die(0);
$user=$_POST['user'];
$device=$_POST['device'];
if($user==""||$device=="")  {die("0");}
$sql="SELECT id FROM `pwdusrrecord` WHERE `username`= ?";
$res=sqlexec($sql,array($user),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {die("0");}
$id = $record['id'];
$sql="DELETE FROM `pin` WHERE `userid`= ? AND `device`= ?";
$res=sqlexec($sql,array($id,$device),$link);
echo '1';
?>
