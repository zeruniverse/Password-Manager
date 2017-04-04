<?php
if(!isset($_POST['a']) || ($_POST['a'] != 'a' && $_POST['a'] != 'farray')) {
    die('Forbidden'); //disallow direct visit;
}
require_once(dirname(__FILE__).'/../function/ajax.php');
require_once(dirname(__FILE__).'/../function/sqllink.php');
$link = sqllink();

if(!checksession($link)) {
    error("AUTHENTICATION ERROR, PLEASE RELOGIN");
}
$id = $_SESSION['userid'];
$arr = array();
$sql = "SELECT * FROM `password` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $index = $i['index'];
	$name = $i['name'];
    $other = $i['other'];
	$kss = $i['pwd'];
	$arr[(int)$index] = array($name,$kss,$other);
}
$farray = array('status' => 'NO', 'random' => mt_rand(10000,50000));
if($_POST['a'] == 'farray')
{
    $tmparr = array();
    $sql = "select * from `files` WHERE `userid` = ?";
    $res = sqlexec($sql,array($id),$link);
    while ($i = $res->fetch(PDO::FETCH_ASSOC)){
        $index = $i['index'];
        $fname = $i['fname'];
        $key = $i['key'];
        $files = $i['files'];
        $tmparr[(int)$index] = array($fname,$key,$files);
    }
    $farray = array('status' => 'OK', 'data' => $tmparr);
}
$ret = array('status' => 'OK', 'VERSION' => $VERSION, 'JSsalt' => $GLOBAL_SALT_1, 'PWsalt' => $GLOBAL_SALT_2, 'ALPHABET' => $DEFAULT_LETTER_USED, 'data' => $arr, 'fdata' => $farray);
echo json_encode($ret);
?>
