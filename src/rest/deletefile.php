<?php

require_once dirname(__FILE__).'/../function/common.php';
require_once dirname(__FILE__).'/../function/ajax.php';
$link = sqllink();
if (!checksession($link)) {
    ajaxError('general');
}
$id = $_SESSION['userid'];

$index = (int) $_POST['index'];
if (!$link->beginTransaction()) {
    ajaxError('general');
}

$sql = 'DELETE FROM `files` WHERE `userid` = ? AND `index` = ?';
$res = sqlexec($sql, [$id, $index], $link);
if ($res == null) {
    $link->rollBack();
    ajaxError('general');
}

$link->commit();

ajaxSuccess();
