<?php
require_once("src/function/sqllink.php");
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
if(!$link->beginTransaction()) {
    die('0.1');
}
$sql = "SELECT * FROM `password`";
$res = sqlexec($sql,[],$link);
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $update = "UPDATE `password` SET `key`='', `pwd`=? WHERE `userid`=? AND `index`=?";
    $newpwd=$i['pwd'];
    if ($i['key']!= "")
        $newpwd = decrypt($i['pwd'],$i['key']);
    $ures = sqlexec($update,array($newpwd,$i['userid'],$i['index']), $link);
    if ($res == NULL) {
        $link->rollBack();
        die(1);
    }
}
$sql = "ALTER TABLE `password` DROP `key`";
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
die('Done');
?>
