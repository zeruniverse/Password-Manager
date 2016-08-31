<?php
function error($msg) {
    echo json_encode(array("status" => "error", "message" => $msg));
    die();
}
require_once("function/sqllink.php");
$link=sqllink();
if(!checksession($link)) error("AUTHENTICATION ERROR, PLEASE RELOGIN");
$id=$_SESSION['userid'];
if(!isset($_POST['id'])||(int)$_POST['id']<1) error("Parameter Error");
$index=(int)$_POST['id'];
$result=array();
$sql = "SELECT `key`,`files` FROM `files` WHERE `userid`= ? and `index`=?";
$res = sqlexec($sql,array($id,$index),$link);
$res->bindColumn(1, $result['key']);
$res->bindColumn(2, $lob, PDO::PARAM_LOB);
$res->fetch(PDO::FETCH_BOUND);
if($res==False) error("No File Can Be Downloaded");

$result['status']='success';
$result['data']=$lob;
echo json_encode($result);
?>