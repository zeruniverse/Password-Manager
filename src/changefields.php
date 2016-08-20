<?php
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!isset($_POST['fields'])) die('0');
if(checksession($link)==FALSE) die('0');
$id=$_SESSION['userid'];
$sql="UPDATE `pwdusrrecord` SET `fields`= ? WHERE `id`= ? ";
$res=sqlexec($sql,array($_POST['fields'], $id),$link);
$_SESSION['fields']=$_POST['fields'];

echo "1";
?>
