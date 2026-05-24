<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

if (!isset($DB_NAME) || $DB_NAME === '') {
  ajaxError('database');
}

if (!pm_is_allowed_request_origin()) {
  ajaxError('origin');
}

function pm_post_or_cookie($postName, $cookieName)
{
  if (isset($_POST[$postName])) {
    return (string) $_POST[$postName];
  }
  if (isset($_COOKIE[$cookieName])) {
    return (string) $_COOKIE[$cookieName];
  }
  return '';
}

function usepin()
{
  global $PIN_EXPIRE_TIME;

  $user = pm_post_or_cookie('frontend_username', 'username');
  $device = pm_post_or_cookie('frontend_device', 'device');

  if ($user === '' || $device === '') {
    return false;
  }

  $link = sqllink();
  if (!$link) {
    return false;
  }

  $sql = 'SELECT id FROM `pwdusrrecord` WHERE `username`= ?';
  $res = sqlexec($sql, [$user], $link);
  $record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
  if (!$record) {
    return false;
  }

  $id = (int) $record['id'];
  $sql = 'DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?';
  sqlexec($sql, [$PIN_EXPIRE_TIME], $link);

  $sql = 'SELECT * FROM `pin` WHERE `userid`= ? AND `device`= ?';
  $res = sqlexec($sql, [$id, $device], $link);
  $record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;

  return (bool) $record;
}

$loggedIn = false;
if (pm_has_auth_credentials()) {
  $link = sqllink();
  $loggedIn = $link ? checksession($link) : false;
}

ajaxSuccess([
  'loggedIn' => $loggedIn ? 1 : 0,
  'use_pin' => usepin() ? 1 : 0,
  'banTime' => $ACCOUNT_BAN_TIME,
  'allowSignup' => $ALLOW_SIGN_UP
]);
