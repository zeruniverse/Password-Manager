<?php
$VERSION = '11.07';

require_once dirname(__FILE__).'/config.php';
require_once dirname(__FILE__).'/cors.php';

function sqllink() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD;

    if (!isset($DB_NAME) || $DB_NAME === '') {
        return null;
    }

    $opt = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'];
    if (defined('PDO::MYSQL_ATTR_MAX_BUFFER_SIZE')) {
        $opt[PDO::MYSQL_ATTR_MAX_BUFFER_SIZE] = 1024 * 1024 * 19;
    }

    $dsn = 'mysql:host='.$DB_HOST.';dbname='.$DB_NAME.';charset=utf8';
    try {
        $dbhdl = new PDO($dsn, $DB_USER, $DB_PASSWORD, $opt);
        $dbhdl->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $dbhdl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbhdl;
    } catch (PDOException $e) {
        return null;
    }
}

function sqlexec($sql, $array, $link) {
    if (!$link) {
        return null;
    }
    $stmt = $link->prepare($sql);
    return $stmt->execute($array) ? $stmt : null;
}

function sqlquery($sql, $link) {
    if (!$link) {
        return null;
    }
    return $link->query($sql);
}

function pm_valid_session_id($sid) {
    return is_string($sid) && preg_match('/^[A-Za-z0-9,-]{16,128}$/', $sid);
}

function start_session() {
    if (session_id() !== '') {
        return;
    }

    session_name('password_manager_session_uid');

    // Frontend/backend split does not rely on browser cookies, but setting SameSite=None
    // keeps same-site/custom-domain deployments compatible if useCredentials is enabled.
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=None', null, true, true);
    }

    if (isset($_POST['api_session_id']) && pm_valid_session_id($_POST['api_session_id'])) {
        session_id($_POST['api_session_id']);
    }

    session_start();
}

function checksession($link) {
    global $SERVER_TIMEOUT;

    start_session();

    if (!pm_is_allowed_request_origin()) {
        invalidateSession();
        return false;
    }

    if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] != 1) {
        invalidateSession();
        return false;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['session_token']) || !isset($_SESSION['session_token']) || !hash_equals((string) $_SESSION['session_token'], (string) $_POST['session_token'])) {
            invalidateSession();
            return false;
        }
    }

    if (!$link || !isset($_SESSION['create_time']) || $_SESSION['create_time'] + $SERVER_TIMEOUT < time()) {
        invalidateSession();
        return false;
    }

    if (!isset($_SESSION['refresh_time']) || $_SESSION['refresh_time'] + 16 < time()) {
        invalidateSession();
        return false;
    }

    $usr = isset($_SESSION['user']) ? $_SESSION['user'] : '';
    $pw = isset($_SESSION['pwd']) ? $_SESSION['pwd'] : '';
    $id = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

    if ($usr === '' || $pw === '' || $id === '') {
        invalidateSession();
        return false;
    }

    $sql = 'SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?';
    $res = sqlexec($sql, [$usr, $pw, $id], $link);
    $record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;

    if (!$record) {
        invalidateSession();
        return false;
    }

    $_SESSION['refresh_time'] = time();
    return true;
}

function invalidateSession() {
    if (session_id() === '') {
        start_session();
    }

    foreach ($_SESSION as $key => $value) {
        unset($_SESSION[$key]);
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        session_destroy();
    }
}
