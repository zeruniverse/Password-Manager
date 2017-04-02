<?php
require_once(dirname(__FILE__).'/../function/ajax.php');
require_once(dirname(__FILE__).'/../function/sqllink.php');
$link = sqllink();
if(!checksession($link)) {
    error("AUTHENTICATION ERROR, PLEASE RELOGIN");
}
$id = $_SESSION['userid'];
$result = array();
$result["status"] = "success";
$result["default_timeout"] = $BROWSER_TIMEOUT;
$result["default_letter_used"] = $DEFAULT_LETTER_USED;
$result["default_length"] = $DEFAULT_LENGTH;
$result["global_salt_1"] = $GLOBAL_SALT_1;
$result["global_salt_2"] = $GLOBAL_SALT_2;
$result["user"] = $_SESSION['user'];
$result["fields"] = $_SESSION['fields'];
$result["fields_allow_change"] = $CUSTOMIZE_FIELDS;
$result["server_timeout"] = $SERVER_TIMEOUT;
$result["file_enabled"] = $FILE_ENABLED?1:0;

// Select Accounts
$sql = "SELECT * FROM `password` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
$accounts = array();
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $accounts[] = array( "index" => $i['index'], "name" => $i['name'], "additional" => $i['other'], "kss" => $i['pwd']);
}
$result["accounts"] = $accounts;

// Select Files
$sql = "SELECT `index`,`fname`,`key` FROM `files` WHERE `userid` = ?";
$res = sqlexec($sql,array($id),$link);
$fdata = array();
while ($i = $res->fetch(PDO::FETCH_ASSOC)){
    $fdata[] = array( "index" => $i['index'], "fname" => $i['fname'], "fkey" => $i['key']);
}
$result["fdata"] = $fdata;

// Select last login
$sql = "SELECT `id`, UNIX_TIMESTAMP(`time`) AS `time` FROM `history` WHERE `userid` = ? AND `outcome` = 1 ORDER BY `id` DESC LIMIT 1 OFFSET 1";
$res = sqlexec($sql, array($id),$link);
$data = $res->fetch(PDO::FETCH_ASSOC);
$loginID = $data["id"];
$result["loginInformation"] = array( "lastLogin" => $data["time"] );

// Select failed login attempts
$sql = "SELECT COUNT(*) AS `failedLogins` FROM `history` WHERE `userid` = ? AND `outcome` = 0 AND `id` > ?";
$res = sqlexec($sql, array($id, $loginID),$link);
$result["loginInformation"]["failedCount"] = int($res->fetch(PDO::FETCH_ASSOC)["failedLogins"]);

echo json_encode($result);
?>
