<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

if (!isset($_POST['a']) || ($_POST['a'] !== 'a' && $_POST['a'] !== 'farray')) {
    ajaxError('method');
}

$link = sqllink();

if (!checksession($link)) {
    ajaxError('authentication');
}

$id = $_SESSION['userid'];

$arr = [];

$sql = 'SELECT * FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);

if ($res) {
    while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
        $index = $i['index'];
        $name = $i['name'];
        $other = $i['other'];
        $kss = $i['pwd'];

        $arr[(int) $index] = [$name, $kss, $other];
    }
}

$farray = [
    'status' => 'NO',
    'random' => random_int(10000, 50000),
];

if ($_POST['a'] === 'farray') {
    $tmparr = [];

    $sql = 'SELECT * FROM `files` WHERE `userid` = ?';
    $res = sqlexec($sql, [$id], $link);

    if ($res) {
        while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
            $index = $i['index'];
            $fname = $i['fname'];
            $key = $i['key'];
            $files = $i['files'];

            $tmparr[(int) $index] = [$fname, $key, $files];
        }
    }

    $farray = [
        'status' => 'OK',
        'data' => $tmparr,
    ];
}

ajaxSuccess([
    'data' => $arr,
    'fdata' => $farray
]);