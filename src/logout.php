<?php
session_start();
if(isset($_SESSION['loginok'])) unset($_SESSION['loginok']);
if(isset($_SESSION['user'])) unset($_SESSION['user']);
if(isset($_SESSION['pwd'])) unset($_SESSION['pwd']);
if(isset($_SESSION['fields'])) unset($_SESSION['fields']);
if(isset($_SESSION['userid'])) unset($_SESSION['userid']);
if(isset($_SESSION['random_login_stamp'])) unset($_SESSION['random_login_stamp']);
session_destroy();
header("Location: ./");
?>
