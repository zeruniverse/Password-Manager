<?php
require_once(dirname(__FILE__).'/../function/ajax.php');
require_once(dirname(__FILE__).'/../function/sqllink.php');
$link=sqllink();
if(!checksession($link)){
    error('session unavailable');
}
$id = $_SESSION['userid'];
$result = array();
$result["status"] = "success";
$result["id"] = $id;
$result["usr"] = $_SESSION['user'];
$sql="SELECT `device`,UNIX_TIMESTAMP(`createtime`) AS `createtime`,`ua` FROM `pin` WHERE `userid`= ?";
$res=sqlexec($sql,array($id),$link);
$pins = array();
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $did=$i['device'];
    $ctime=(int)$i['createtime'];
    $ua=$i['ua'];
    $pins[] = array( "ua" => $ua, "ctime" => $ctime, "did" => $did);
}
$result["pins"] = $pins;

$sql="SELECT `ip`,`ua`,`outcome`,UNIX_TIMESTAMP(`time`) AS `time` FROM `history` WHERE `userid`= ? ORDER BY `id` DESC LIMIT 60";
$res=sqlexec($sql,array($id),$link);
$ips = array();
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $ip=$i['ip'];
    $ua=$i['ua'];
    $ctime=(int)$i['time'];
    $ips[] = array( "ip" => $ip, "ua" => $ua, "ctime" => $ctime, "outcome" => $i['outcome']==0);
}
$result["ips"] = $ips;
echo json_encode($result);
?>
