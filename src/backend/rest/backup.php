<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();
if (!checksession($link)) {
  ajaxError('authentication');
}

$id = pm_auth_userid();

$arr = [];
$sql = 'SELECT `index`, `name`, `pwd`, `other` FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);
if ($res) {
  while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
    $arr[(int) $row['index']] = [$row['name'], $row['pwd'], $row['other']];
  }
}

$farray = [
  'status' => 'NO',
  'random' => random_int(10000, 50000)
];

if (isset($_POST['a']) && $_POST['a'] === 'farray' && $FILE_ENABLED) {
  $tmparr = [];
  $sql = 'SELECT `index`, `fname`, `key`, `files` FROM `files` WHERE `userid` = ?';
  $res = sqlexec($sql, [$id], $link);
  if ($res) {
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
      $tmparr[(int) $row['index']] = [$row['fname'], $row['key'], $row['files']];
    }
  }
  $farray = [
    'status' => 'OK',
    'data' => $tmparr
  ];
}

ajaxSuccess([
  'data' => $arr,
  'fdata' => $farray
]);
