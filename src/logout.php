<?php
session_start();
if(isset($_SESSION['loginok'])) unset($_SESSION['loginok']);
if(isset($_SESSION['user'])) unset($_SESSION['user']);
if(isset($_SESSION['pwd'])) unset($_SESSION['pwd']);
if(isset($_SESSION['fields'])) unset($_SESSION['fields']);
if(isset($_SESSION['userid'])) unset($_SESSION['userid']);
if(isset($_SESSION['random_login_stamp'])) unset($_SESSION['random_login_stamp']);
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
);
session_regenerate_id(true);//as suggested by owasp, change sessionId when changing context
session_destroy();
$reason = "";
if(isset($_GET["reason"]))
    $reason .= "?reason=".urlencode($_GET["reason"]);
header("Location: ./".$reason);
?>
