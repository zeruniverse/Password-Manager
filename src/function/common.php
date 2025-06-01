<?php

$VERSION = '11.05';
require_once dirname(__FILE__).'/config.php';
function sqllink()
{
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD;
    $dbhost = $DB_HOST;
    $dbname = $DB_NAME;
    $dbusr = $DB_USER;
    $dbpwd = $DB_PASSWORD;
    $dbhdl = null;
    if (defined('PDO::MYSQL_ATTR_MAX_BUFFER_SIZE')) {
        $opt = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::MYSQL_ATTR_MAX_BUFFER_SIZE => 1024 * 1024 * 19];
    } else {
        $opt = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
    }
    $dsn = 'mysql:host='.$dbhost.';dbname='.$dbname.';charset=utf8';

    try {
        $dbhdl = new PDO($dsn, $dbusr, $dbpwd, $opt);
        $dbhdl->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbhdl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Display exception
    } catch (PDOExceptsddttrtion $e) {//return PDOException
        return;
    }

    return $dbhdl;
}
function sqlexec($sql, $array, $link)
{
    $stmt = $link->prepare($sql);
    $exeres = $stmt->execute($array);
    if ($exeres) {
        return $stmt;
    } else {
        return;
    }
}
function sqlquery($sql, $link)
{
    return $link->query($sql);
}
function start_session()
{
    if (session_id() !== '') {
        return;
    }
    // set a unique session name to get rid of php default session name
    session_name('password_manager_session_uid');
    // for compatibility with PHP < 7.3.0
    session_set_cookie_params(0, '/; samesite=strict', null, true, true);
    session_start();
}
function checksession($link)
{
    global $SERVER_TIMEOUT, $HOSTDOMAIN;
    start_session();
    if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] != 1) {
        invalidateSession();

        return false;
    }
    if (isset($_SERVER['HTTP_REFERER']) && ($_SERVER['HTTP_REFERER'] != '')
        && (strpos(strtolower($_SERVER['HTTP_REFERER']), strtolower($HOSTDOMAIN)) !== 0)) {
        //Users from other sites are banned
        invalidateSession();

        return false;
    }
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && ($_POST['session_token'] !== $_SESSION['session_token'])) {
        //Must check session_token to prevent cross-site attack
        invalidateSession();

        return false;
    }
    if (!$link || !isset($_SESSION['create_time']) || $_SESSION['create_time'] + $SERVER_TIMEOUT < time()) {
        invalidateSession();

        return false;
    }
    if ($_SESSION['refresh_time'] + 16 < time()) {
        // allow for 16 seconds. Client polls every 5 seconds so we allow 2 consecutive misses.
        invalidateSession();

        return false;
    }
    $usr = $_SESSION['user'];
    $pw = $_SESSION['pwd'];
    $id = $_SESSION['userid'];
    if ($usr == '' || $pw == '' || $id == '') {
        invalidateSession();

        return false;
    }
    $sql = 'SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?';
    $res = sqlexec($sql, [$usr, $pw, $id], $link);
    $record = $res->fetch(PDO::FETCH_ASSOC);
    if (!$record) {
        invalidateSession();

        return false;
    }

    $_SESSION['refresh_time'] = time();

    return true;
}
function invalidateSession()
{
    foreach ($_SESSION as $key => $value) {
        unset($_SESSION[$key]);
    }
    //As suggested by owasp, change sessionId when changing context
    session_regenerate_id(true);
    session_destroy();
}
