<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

if (!pm_is_allowed_request_origin()) {
  ajaxError('origin');
}

if (!$ALLOW_SIGN_UP) {
  ajaxError('signup');
}

$usr = isset($_POST['user']) ? trim((string) $_POST['user']) : '';
$pw = isset($_POST['pwd']) ? (string) $_POST['pwd'] : '';
$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';

if ($usr === '' || $pw === '' || $email === '') {
  ajaxError('parameter');
}
if (strlen($pw) > 130 || strlen($usr) > 50 || strlen($email) > 50) {
  ajaxError('parameter');
}
if (!preg_match('/\A[A-Za-z0-9\-_\.]+\z/', $usr)) {
  ajaxError('parameter');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  ajaxError('invalidEmail');
}

$link = sqllink();
if (!$link) {
  ajaxError('general');
}

if (!$link->beginTransaction()) {
  ajaxError('general');
}

$sql = 'SELECT COUNT(*) FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$usr], $link);
$num = $res ? $res->fetch(PDO::FETCH_NUM) : [0];
if ((int) $num[0] !== 0) {
  $link->commit();
  ajaxError('occupiedUser');
}

$sql = 'SELECT COUNT(*) FROM `pwdusrrecord` WHERE `email` = ?';
$res = sqlexec($sql, [$email], $link);
$num = $res ? $res->fetch(PDO::FETCH_NUM) : [0];
if ((int) $num[0] !== 0) {
  $link->commit();
  ajaxError('occupiedEmail');
}

$salt = random_bytes(64);
$password = hash_pbkdf2('sha3-512', $pw, $salt, $PBKDF2_ITERATIONS);
$res = sqlquery('SELECT max(`id`) FROM `pwdusrrecord`', $link);
$result = $res ? $res->fetch(PDO::FETCH_NUM) : false;
$maxnum = (!$result || $result[0] === null) ? 0 : (int) $result[0];

$sql = 'INSERT INTO `pwdusrrecord` (`id`, `username`, `password`, `salt`, `fields`, `email`, `totp_sec`) VALUES (?, ?, ?, ?, ?, ?, ?)';
$rett = sqlexec($sql, [$maxnum + 1, $usr, $password, $salt, $DEFAULT_FIELDS, $email, ''], $link);
if (!$rett) {
  $link->rollBack();
  ajaxError('general');
}
$link->commit();

ajaxSuccess();
