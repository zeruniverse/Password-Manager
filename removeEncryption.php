<?php
require_once("/src/function/sqllink.php");
function decrypt($data, $key)
{
	$key = md5($key);
    $x = 0;
	$char='';
	$str='';
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}
$link = sqllink();
if(!checksession($link)) {
    die('0');
}
if(!$link->beginTransaction()) {
    die('0');
}
$sql = "SELECT * FROM `password`";
$res = sqlexec($sql,[],$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $update = "UPDATE `password` SET pubkey = "", pwd = ? WHERE userid=?, index=?";
    $newpwd=$i['pwd'];
    if ($i['pubkey']!= "")
        $newpwd = decrypt($i['pwd'],$i['pubkey']);
    $ures = sqlexec($update,array($newpwd,$i['userid'],$i['index']), $link);
    if ($res == NULL) {
        $link->rollBack();
        die(1);
    }
}
$sql = "AÖTER TABLE `password` DROP `pubkey`";
$res = sqlexec($sql,[],$link);
if ($res == NULL) {
        $link->rollBack();
        die(2);
}

$sql = "SELECT * FROM `pwdusrrecord`";
$res = sqlexec($sql,[],$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $update = "UPDATE `pwdusrrecord` SET password = ? WHERE id=?";
    $newpwd=$i['pwd'];
    $newpwd = decrypt($i['pwd'],$GLOBAL_SALT_3);
    $ures = sqlexec($update,array($newpwd,$i['id']), $link);
    if ($res == NULL) {
        $link->rollBack();
        die(3);
    }
}
$link->commit();
?>
