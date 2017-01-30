<?php
require_once(dirname(__FILE__).'/../function/ajax.php');
require_once(dirname(__FILE__).'/../function/sqllink.php');
require_once(dirname(__FILE__).'/../function/encryption.php');
$link=sqllink();
if(!checksession($link)) {
    error("AUTHENTICATION ERROR, PLEASE RELOGIN");
}
$id=$_SESSION['userid'];
$result=array();
$result["status"] = "success";
$result["default_timeout"] = $BROWSER_TIMEOUT;
$result["default_letter_used"] = $DEFAULT_LETTER_USED;
$result["default_length"] = $DEFAULT_LENGTH; 
$result["global_salt_1"] = $GLOBAL_SALT_1; 
$result["global_salt_2"] = $GLOBAL_SALT_2;
$result["user"] = $_SESSION['user'];
$result["fields"] = $_SESSION['fields'];
$result["fields_allow_change"] = $CUSTOMIZE_FIELDS;
$result["server_timeout"]=$SERVER_TIMEOUT;
$result["file_enabled"]=$FILE_ENABLED?1:0;

$sql = "SELECT * FROM `password` WHERE `userid`= ?";
$res = sqlexec($sql,array($id),$link);
$accounts = array();
while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
    $accounts[]=array( "index" => $i['index'], "name" => $i['name'], "additional" => $i['other'], "kss" => decrypt($i['pwd'],$i['key']));
}
$result["accounts"] = $accounts;

$sql = "SELECT `index`,`fname`,`key` FROM `files` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
$fdata = array();
while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
    $fdata[]=array( "index" => $i['index'], "fname" => $i['fname'], "fkey" => $i['key']);
}
$result["fdata"] = $fdata;
echo json_encode($result);
?>
