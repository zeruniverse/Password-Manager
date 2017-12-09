<?php
require_once dirname(__FILE__).'/../function/ajax.php';
require_once dirname(__FILE__).'/../function/sqllink.php';
if ($ALLOW_SIGN_UP === false) {
    http_response_code(405);
    error('Signup is not allowed.');
}
$pw = $_POST['pwd'];
$usr = $_POST['user'];
$email = $_POST['email'];
if ($pw == '' || $usr == '' || $email == '') {
    error("Not all required parameters have been entered.");
}
// check length of password hash for pbkdf2
if (strlen($pw) > 130) {
    error("password too long");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error("Invalid E-mail address.");
}
$link = sqllink();
if (!$link) {
    error("There're some errors, please retry");
}
if (!$link->beginTransaction()) {
    error("There're some errors, please retry");
}
$sql = 'SELECT COUNT(*) FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$usr], $link);
$num = $res->fetch(PDO::FETCH_NUM);
if ($num[0] != 0) {
    $link->commit();
    error('User name already occupied, please choose another user name.');
}
$sql = 'SELECT COUNT(*) FROM `pwdusrrecord` WHERE `email` = ?';
$res = sqlexec($sql, [$email], $link);
$num = $res->fetch(PDO::FETCH_NUM);
if ($num[0] != 0) {
    $link->commit();
    error('This E-mail has already been used.');
}
$salt = openssl_random_pseudo_bytes(32);
$pw = hash_pbkdf2('sha256', $pw, $salt, $PBKDF2_ITERATIONS);
$res = sqlquery('SELECT max(`id`) FROM `pwdusrrecord`', $link);
$result = $res->fetch(PDO::FETCH_NUM);
$maxnum = $result == false ? 0 : (int) ($result[0]);
$sql = 'INSERT INTO `pwdusrrecord` VALUES (?,?,?,?,?,?)';
$rett = sqlexec($sql, [$maxnum + 1, $usr, $pw, $salt, $DEFAULT_FIELDS, $email], $link);
if (!$rett) {
    $link->rollBack();
    error("There're some errors, please retry");
}
$link->commit();
echo json_encode(['status' => "success"]);
