<?php
require_once("function/config.php");
session_start();
if(!isset($_SESSION['create_time'])||$_SESSION['create_time']+$SERVER_TIMEOUT<time()) {session_destroy(); die('0');}
die('1');
?>
