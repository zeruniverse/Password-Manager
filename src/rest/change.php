<?php
require_once(dirname(__FILE__)."/../function/sqllink.php");
require_once(dirname(__FILE__)."/../function/encryption.php");
$link=sqllink();
if(checksession($link)==FALSE) {die("0");}
$id=$_SESSION['userid'];
$index=$_POST['index']; 
if(!$link->beginTransaction()) die('0');
$sql="SELECT * FROM `password` WHERE `userid`= ? AND `index`= ? ";
$res=sqlexec($sql,array($id,(int)$index),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {$link->commit();die("0");}
$ppwd=$_POST['newpwd'];
$pubkey=mt_rand(10000000,99999999);
$newpw=encrypt($ppwd,$pubkey);
$changedCols="`key` = ? ,`pwd` = ?";
$values=array($pubkey,$newpw);
if (isset($_POST["name"])){
    $changedCols .= " ,`name` = ?";
    array_push($values, $_POST["name"]);
}
if (isset($_POST["other"])){
    $changedCols .= " ,`other` = ?";
    array_push($values, $_POST["other"]);
}
array_push($values, $id, (int)$index);
$sql="UPDATE `password` SET ".$changedCols." WHERE `userid` = ? AND `index`= ?";
$res=sqlexec($sql,$values,$link);
if($res==NULL) {$link->rollBack();die("0");}
$link->commit();
echo "1";
?>
