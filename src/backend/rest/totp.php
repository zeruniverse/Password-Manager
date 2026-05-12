<?php
require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';
require_once dirname(__FILE__) . '/../function/totp.php';

$link = sqllink();

if (!checksession($link)) {
    ajaxError('authentication');
}

$id = (int) $_SESSION['userid'];
$username = (string) $_SESSION['user'];
$action = isset($_POST['action']) ? (string) $_POST['action'] : 'status';

$sql = 'SELECT `password`, `totp_sec` FROM `pwdusrrecord` WHERE `id` = ?';
$res = sqlexec($sql, [$id], $link);
$record = $res->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    ajaxError('authentication');
}

$totpSecret = trim((string) $record['totp_sec']);

if ($action === 'status') {
    ajaxSuccess(['enabled' => ($totpSecret !== '' ? 1 : 0)]);
}

if ($action === 'off') {
    $sql = 'UPDATE `pwdusrrecord` SET `totp_sec` = ? WHERE `id` = ?';
    $update = sqlexec($sql, ['', $id], $link);

    if (!$update) {
        ajaxError('general');
    }

    totp_clear_trust_cookie($username);
    ajaxSuccess(['enabled' => 0]);
}

if ($action === 'on') {
    $secret = isset($_POST['secret']) ? totp_normalize_secret($_POST['secret']) : false;
    $code = isset($_POST['code']) ? trim((string) $_POST['code']) : '';

    if ($secret === false || $code === '') {
        ajaxError('parameter');
    }

    if (!totp_verify_code($secret, $code)) {
        ajaxError('TotpWrong');
    }

    $sql = 'UPDATE `pwdusrrecord` SET `totp_sec` = ? WHERE `id` = ?';
    $update = sqlexec($sql, [$secret, $id], $link);

    if (!$update) {
        ajaxError('general');
    }

    // The browser that just completed setup should count as trusted.
    totp_set_trust_cookie($username, $record['password'], $secret);

    ajaxSuccess(['enabled' => 1]);
}

ajaxError('parameter');
