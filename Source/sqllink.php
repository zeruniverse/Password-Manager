<?php
function sqllink()
{
$dbname = '';

$host = "localhost";

$user = "";

$pwd = "";
$link = @mysql_connect($host,$user,$pwd,true);
if(!$link) {
    return NULL;
}

mysql_query("set names utf8");
if(!mysql_select_db($dbname,$link)) return NULL;
return $link;
}
?>