<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();
$newpass = isset($_POST['newpass']) ? (string) $_POST['newpass'] : '';
if ($newpass === '' || strlen($newpass) > 130) {
  ajaxError('general');
}

$accarray = isset($_POST['accarray']) ? json_decode((string) $_POST['accarray']) : null;
if (!is_array($accarray)) {
  ajaxError('parameter');
}

$salt = random_bytes(64);
$newhash = hash_pbkdf2('sha3-512', $newpass, $salt, $PBKDF2_ITERATIONS);

if (!$link->beginTransaction()) {
  ajaxError('general');
}

$sql = 'UPDATE `pwdusrrecord` SET `password` = ?, `salt` = ? WHERE `id` = ?';
$res = sqlexec($sql, [$newhash, $salt, $id], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}

$sql = 'SELECT `index` FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
  $idx = (int) $row['index'];
  if (!isset($accarray[$idx])) {
    $link->rollBack();
    ajaxError('parameter');
  }
  $account = $accarray[$idx];
  $sql = 'UPDATE `password` SET `name` = ?, `pwd` = ?, `other` = ? WHERE `userid` = ? AND `index` = ?';
  $ok = sqlexec($sql, [$account->name, $account->kss, $account->other, $id, $idx], $link);
  if (!$ok) {
    $link->rollBack();
    ajaxError('general');
  }
}

$sql = 'SELECT `index` FROM `files` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
if (!$res) {
  $link->rollBack();
  ajaxError('general');
}
while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
  $idx = (int) $row['index'];
  if (!isset($accarray[$idx])) {
    $link->rollBack();
    ajaxError('parameter');
  }
  $account = $accarray[$idx];
  $fk = isset($account->fk) ? $account->fk : (isset($account->fkey) ? $account->fkey : '');
  $fname = isset($account->fname) ? $account->fname : '';
  $sql = 'UPDATE `files` SET `key` = ?, `fname` = ? WHERE `userid` = ? AND `index` = ?';
  $ok = sqlexec($sql, [$fk, $fname, $id, $idx], $link);
  if (!$ok) {
    $link->rollBack();
    ajaxError('general');
  }
}

sqlexec('DELETE FROM `pin` WHERE `userid` = ?', [$id], $link);
$link->commit();

ajaxSuccess(['password' => $newhash]);
