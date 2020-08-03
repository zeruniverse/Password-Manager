<?php

require_once dirname(__FILE__).'/../function/common.php';
require_once dirname(__FILE__).'/../function/ajax.php';
if (!$ALLOW_SIGN_UP) {
    http_response_code(405);
    ajaxError('signup');
}
$pw = $_POST['pwd'];
$usr = $_POST['user'];
$email = $_POST['email'];
if ($pw == '' || $usr == '' || $email == '') {
    ajaxError('parameter');
}
// check length of password hash for pbkdf2
if (strlen($pw) > 130) {
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
$num = $res->fetch(PDO::FETCH_NUM);
if ($num[0] != 0) {
    $link->commit();
    ajaxError('occupiedUser');
}
$sql = 'SELECT COUNT(*) FROM `pwdusrrecord` WHERE `email` = ?';
$res = sqlexec($sql, [$email], $link);
$num = $res->fetch(PDO::FETCH_NUM);
if ($num[0] != 0) {
    $link->commit();
    ajaxError('occupiedEmail');
}
$salt = random_bytes(64);
$pw = hash_pbkdf2('sha3-512', $pw, $salt, $PBKDF2_ITERATIONS);
$res = sqlquery('SELECT max(`id`) FROM `pwdusrrecord`', $link);
$result = $res->fetch(PDO::FETCH_NUM);
$maxnum = !$result ? 0 : (int) ($result[0]);
$sql = 'INSERT INTO `pwdusrrecord` VALUES (?,?,?,?,?,?)';
$rett = sqlexec($sql, [$maxnum + 1, $usr, $pw, $salt, $DEFAULT_FIELDS, $email], $link);
if (!$rett) {
    $link->rollBack();
    ajaxError('general');
}
$link->commit();
ajaxSuccess();
