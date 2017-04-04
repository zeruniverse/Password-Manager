<?php
require_once(dirname(__FILE__).'/../function/ajax.php');
require_once(dirname(__FILE__).'/../function/sqllink.php');
session_start();
if(!isset($_SESSION['random_login_stamp'])) 
    $_SESSION['random_login_stamp']=date("Ymdhis").mt_rand(10000,99999);
if(!isset($_SESSION['session_token']))
    $_SESSION['session_token']=bin2hex(openssl_random_pseudo_bytes(32));
function usepin()
{
    global $PIN_EXPIRE_TIME;
    if(!isset($_COOKIE["username"]) || !isset($_COOKIE["device"])) return False;
    $user=$_COOKIE["username"];
    $device=$_COOKIE["device"];
    if($user==""||$device=="")  return False;
    $link=sqllink();
    if(!$link) return False;
    $sql="SELECT id FROM `pwdusrrecord` WHERE `username`= ?";
    $res=sqlexec($sql,array($user),$link);
    $record= $res->fetch(PDO::FETCH_ASSOC);
    if($record==FALSE) return False;
    $id = $record['id'];
    $sql="DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?";
    $res=sqlexec($sql,array($PIN_EXPIRE_TIME),$link);
    $sql="SELECT * FROM `pin` WHERE `userid`= ? AND `device`= ?";
    $res=sqlexec($sql,array($id,$device),$link);
    $record= $res->fetch(PDO::FETCH_ASSOC);
    if($record==FALSE) return False;
    return True;
}
if($DB_NAME == '') {
    error("PLEASE CONFIG function/config.php before using this system!");
}
$result = array();
$result["status"] = "success";
$result["loggedIn"] = (isset($_SESSION["loginok"])&& $_SESSION['loginok']==1);
$result["default_timeout"] = $BROWSER_TIMEOUT;
$result["default_letter_used"] = $DEFAULT_LETTER_USED;
$result["default_length"] = $DEFAULT_LENGTH; 
$result["global_salt_1"] = $GLOBAL_SALT_1; 
$result["global_salt_2"] = $GLOBAL_SALT_2;
$result["session_token"] = $_SESSION['session_token'];
$result["random_login_stamp"] = $_SESSION['random_login_stamp'];
$result["use_pin"] = usepin() ? 1 : 0;
$result["version"] = $VERSION;
$result["banTime"] = $ACCOUNT_BAN_TIME;
$result["allowSignup"] = $ALLOW_SIGN_UP;
echo json_encode($result);
?>
