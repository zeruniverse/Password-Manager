<?php
$pw = $_POST['pwd'];
$usr = $_POST['user'];
$email = $_POST['email'];
if($pw == ''||$usr == ''||$email == "") {
    die("7");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("5");
}
require_once(dirname(__FILE__)."/../function/sqllink.php");
if ($ALLOW_SIGN_UP === False){
    http_response_code(405);
    die('Method not allowed');
}
$link = sqllink();
if(!$link) {
    die('6');
}
if(!$link->beginTransaction()) {
    die('4');
}
$sql = "SELECT COUNT(*) FROM `pwdusrrecord` WHERE `username` = ?";
$res = sqlexec($sql,array($usr),$link);
$num = $res->fetch(PDO::FETCH_NUM);
if($num[0] != 0) {
    $link->commit(); 
    die("0");
}
$sql = "SELECT COUNT(*) FROM `pwdusrrecord` WHERE `email` = ?";
$res = sqlexec($sql,array($email),$link);
$num = $res->fetch(PDO::FETCH_NUM);
if($num[0] != 0) {
    $link->commit();
    die("1");
}
$res = sqlquery('SELECT max(`id`) FROM `pwdusrrecord`',$link);
$result = $res->fetch(PDO::FETCH_NUM);
$maxnum = $result == FALSE ? 0:(int)($result[0]);
$sql = "INSERT INTO `pwdusrrecord` VALUES (?,?,?,?,?)";
$rett = sqlexec($sql,array($maxnum+1,$usr,$pw,$DEFAULT_FIELDS,$email),$link);
if(!$rett) {
    $link->rollBack();
    die('8');
}
$link->commit();
die('9');
?>
