<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';

$link = sqllink();

if (!checksession($link)) {
    ajaxError('authentication');
}

$id = $_SESSION['userid'];

$result = [];

$result['user'] = $_SESSION['user'];
$result['fields'] = $_SESSION['fields'];
$result['fields_allow_change'] = $CUSTOMIZE_FIELDS;
$result['server_timeout'] = $SERVER_TIMEOUT;
$result['file_enabled'] = $FILE_ENABLED;

$sql = 'SELECT `totp_sec` FROM `pwdusrrecord` WHERE `id` = ?';
$res = sqlexec($sql, [$id], $link);
$userRecord = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;

$result['totp_enabled'] = ($userRecord && trim((string) $userRecord['totp_sec']) !== '') ? 1 : 0;

// Select accounts.
$sql = 'SELECT * FROM `password` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);

$accounts = [];

if ($res) {
    while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
        $accounts[] = [
            'index' => $i['index'],
            'name' => $i['name'],
            'additional' => $i['other'],
            'kss' => $i['pwd'],
        ];
    }
}

$result['accounts'] = $accounts;

// Select files.
$sql = 'SELECT `index`,`fname`,`key` FROM `files` WHERE `userid` = ?';
$res = sqlexec($sql, [$id], $link);

$fdata = [];

if ($res) {
    while ($i = $res->fetch(PDO::FETCH_ASSOC)) {
        $fdata[] = [
            'index' => $i['index'],
            'fname' => $i['fname'],
            'fkey' => $i['key'],
        ];
    }
}

$result['fdata'] = $fdata;

// Select last successful login before the current one.
$sql = 'SELECT `id`, UNIX_TIMESTAMP(`time`) AS `time` FROM `history` WHERE `userid` = ? AND `outcome` = 1 ORDER BY `id` DESC LIMIT 1 OFFSET 1';
$res = sqlexec($sql, [$id], $link);
$data = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;

if ($data) {
    $loginID = (int) $data['id'];
    $result['loginInformation'] = [
        'lastLogin' => (int) $data['time'],
    ];
} else {
    $loginID = 0;
    $result['loginInformation'] = [
        'lastLogin' => 0,
    ];
}

// Select failed login attempts after last successful login.
$sql = 'SELECT COUNT(*) AS `failedLogins` FROM `history` WHERE `userid` = ? AND `outcome` = 0 AND `id` > ?';
$res = sqlexec($sql, [$id, $loginID], $link);
$data = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;

$result['loginInformation']['failedCount'] = $data ? (int) $data['failedLogins'] : 0;

ajaxSuccess($result);