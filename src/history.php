<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("function/sqllink.php");
require_once("function/basic.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();header("Location: ./");die();}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();header("Location: ./");die();}

//CHECK AGAIN, TO AVOID PASSWORD CHANGE IN ANOTHER BROWSER
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();header("Location: ./");die();}
echoheader();
?>
<div class="container theme-showcase" style="margin-top:-30px;">
    <div class="page-header">
        <h1>Trusted Devices</h1>
    </div>
    <table class="table">
    <tr><th>Device ID</th><th>Device Type</th><th>Set Time</th><th>Untrust (Disable PIN)</th></tr>
    </table>
    <div class="page-header">
        <h1>Login History</h1>
    </div>
    <table class="table">
    <tr><th>Device ID</th><th>Device Type</th><th>Login IP</th><th>Login Time</th><th>Login Result</th></tr>
    </table>   
</div>
<?php echofooter();?>