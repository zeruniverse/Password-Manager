<?php

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/cors.php';

function sqllink()
{
  global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASSWORD;

  if ($DB_HOST === '' || $DB_NAME === '' || $DB_USER === '') {
    return null;
  }

  try {
    $dsn = 'mysql:host=' . $DB_HOST . ';dbname=' . $DB_NAME . ';charset=utf8mb4';
    $link = new PDO($dsn, $DB_USER, $DB_PASSWORD, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false
    ]);
    return $link;
  } catch (Exception $e) {
    return null;
  }
}

function sqlexec($sql, $params, $link)
{
  if (!$link) {
    return null;
  }

  try {
    $stmt = $link->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  } catch (Exception $e) {
    return null;
  }
}

function sqlquery($sql, $link)
{
  if (!$link) {
    return null;
  }

  try {
    return $link->query($sql);
  } catch (Exception $e) {
    return null;
  }
}

$GLOBALS['PM_AUTH_RECORD'] = null;

function pm_request_auth_user()
{
  return isset($_POST['auth_user']) ? trim((string) $_POST['auth_user']) : '';
}

function pm_request_auth_password()
{
  return isset($_POST['auth_password']) ? (string) $_POST['auth_password'] : '';
}

function pm_has_auth_credentials()
{
  return pm_request_auth_user() !== '' || pm_request_auth_password() !== '';
}

function pm_is_sha3_512_hash($value)
{
  return is_string($value) && preg_match('/\A[a-f0-9]{128}\z/i', $value) === 1;
}

function checksession($link)
{
  $GLOBALS['PM_AUTH_RECORD'] = null;

  if (!pm_is_allowed_request_origin()) {
    return false;
  }

  if (!$link) {
    return false;
  }

  $usr = pm_request_auth_user();
  $pw = strtolower(pm_request_auth_password());

  if ($usr === '' || !pm_is_sha3_512_hash($pw)) {
    return false;
  }

  $sql = 'SELECT * FROM `pwdusrrecord` WHERE `username` = ? AND `password` = ?';
  $res = sqlexec($sql, [$usr, $pw], $link);
  $record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;

  if (!$record) {
    return false;
  }

  $GLOBALS['PM_AUTH_RECORD'] = $record;
  return true;
}

function pm_auth_record()
{
  $record = isset($GLOBALS['PM_AUTH_RECORD']) ? $GLOBALS['PM_AUTH_RECORD'] : null;
  return is_array($record) ? $record : null;
}

function pm_auth_userid()
{
  $record = pm_auth_record();
  return $record ? (int) $record['id'] : 0;
}

function pm_auth_user()
{
  $record = pm_auth_record();
  return $record ? (string) $record['username'] : '';
}

function pm_auth_fields()
{
  $record = pm_auth_record();
  return $record ? (string) $record['fields'] : '';
}

function pm_auth_password_hash()
{
  $record = pm_auth_record();
  return $record ? (string) $record['password'] : '';
}

function pm_client_ip()
{
  if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
    $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    return trim($ips[0]);
  }

  return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
}

function pm_user_agent()
{
  return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
}
