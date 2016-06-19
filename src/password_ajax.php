<?php
function error($msg) {
    echo json_encode(array("status" => "error", "message" => $msg));
    die();
}
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();error("session not ok");}
require_once("function/sqllink.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();error("can't connect to database");}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();error("something is wrong with the user");}

//CHECK AGAIN, TO AVOID PASSWORD CHANGE IN ANOTHER BROWSER
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();error("this user doesn't exist");}

$result=array();
$result["status"] = "success";
$result["default_timeout"] = $BROWSER_TIMEOUT;
$result["default_letter_used"] = $DEFAULT_LETTER_USED;
$result["default_length"] = $DEFAULT_LENGTH; 
$result["global_salt_1"] = $GLOBAL_SALT_1; 
$result["global_salt_2"] = $GLOBAL_SALT_2;
$result["user"] = $_SESSION['user'];
$result["fields"] = $_SESSION['fields'];

$sql = "SELECT * FROM `password` WHERE `userid`= ?";
$res = sqlexec($sql,array($id),$link);
$accounts = array();
while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
    $accounts[]=array( "index" => $i['index'], "name" => $i['name'], "additional" => $i['other'], "kss" => decrypt($i['pwd'],$i['key']));
}
$result["accounts"] = $accounts;
echo json_encode($result);
?>
