<?php
require_once(dirname(__FILE__).'/config.php');
function sqllink()
{
    global $DB_HOST,$DB_NAME,$DB_USER,$DB_PASSWORD;
    $dbhost=$DB_HOST;
    $dbname=$DB_NAME;
    $dbusr=$DB_USER;
    $dbpwd=$DB_PASSWORD;
    $dbhdl=NULL;
    if( defined( 'PDO::MYSQL_ATTR_MAX_BUFFER_SIZE' ) ) {
        $opt = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::MYSQL_ATTR_MAX_BUFFER_SIZE => 1024 * 1024 * 19);
    }
    else {
        $opt = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
    }
    $dsn='mysql:host=' . $dbhost . ';dbname=' . $dbname.';charset=utf8';
    try {
        $dbhdl = new PDO($dsn, $dbusr, $dbpwd, $opt);
        $dbhdl->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbhdl->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);//Display exception
    } 
    catch (PDOExceptsddttrtion $e) {//return PDOException
        return NULL;
    }
    return $dbhdl;
}
function sqlexec($sql,$array,$link)
{
    $stmt = $link->prepare($sql);
    $exeres = $stmt->execute($array);
    if($exeres) return $stmt; else return NULL;
    
}
function sqlquery($sql,$link)
{
    return $link->query($sql);
}
function checksession($link)
{
    global $SERVER_TIMEOUT, $HOSTDOMAIN;
    session_start();
    if(!isset($_SESSION['loginok'])||$_SESSION['loginok']!=1) {session_destroy();return FALSE;}
    if(isset($_SERVER['HTTP_REFERER'])&&($_SERVER['HTTP_REFERER']!='')&&(strpos(strtolower($_SERVER['HTTP_REFERER']), strtolower($HOSTDOMAIN))!==0))
    {
        //Users from other sites are banned
        session_destroy();
        return FALSE;
    }
    if(($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['session_token']!==$_SESSION['session_token']))
    {
        //Must check session_token to prevent cross-site attack
        session_destroy();
        return FALSE;
    }
    if(!$link||!isset($_SESSION['create_time'])||$_SESSION['create_time']+$SERVER_TIMEOUT<time()) {session_destroy(); return FALSE;}
    $usr=$_SESSION['user'];
    $pw=$_SESSION['pwd'];
    $id=$_SESSION['userid'];
    if($usr==''||$pw==''||$id=='') {session_destroy(); return FALSE;}
    $sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
    $res=sqlexec($sql,array($usr,$pw,$id),$link);
    $record= $res->fetch(PDO::FETCH_ASSOC);
    if($record==FALSE) {session_destroy();return FALSE;}
    $_SESSION['create_time']=time();
	setcookie("ServerRenew", "1");
    return TRUE;
}   
$currentCookieParams = session_get_cookie_params();  
session_set_cookie_params(0, $currentCookieParams['path'], $currentCookieParams['domain'], (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443, true);
?>
