<?php
session_start();
 if(isset($_SESSION['loginok'])) unset($_SESSION['loginok']);
 if(isset($_SESSION['user'])) unset($_SESSION['user']);
 if(isset($_SESSION['pwd'])) unset($_SESSION['pwd']);
header("Location: ./");
?>