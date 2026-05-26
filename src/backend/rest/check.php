<?php

require_once dirname(__FILE__) . '/../function/common.php';
require_once dirname(__FILE__) . '/../function/ajax.php';
require_once dirname(__FILE__) . '/../function/totp.php';

function loghistory($link, $userid, $ip, $ua, $outcome)
{
  $sql = 'SELECT max(`id`) AS `m` FROM `history`';
  $res = sqlquery($sql, $link);
  $r = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
  $i = (!$r || $r['m'] === null) ? 0 : ((int) $r['m']) + 1;

  $sql = 'INSERT INTO `history` VALUES (?,?,?,?,?,CURRENT_TIMESTAMP)';
  sqlexec($sql, [$i, $userid, $ip, $ua, $outcome], $link);
}

if (!pm_is_allowed_request_origin()) {
  ajaxError('origin');
}

$usr = isset($_POST['user']) ? trim((string) $_POST['user']) : '';
$pw = isset($_POST['pwd']) ? (string) $_POST['pwd'] : '';
$totpcode = isset($_POST['totpcode']) ? trim((string) $_POST['totpcode']) : '';

if ($pw === '' || $usr === '') {
  ajaxError('general');
}

if (strlen($pw) > 130) {
  ajaxError('general');
}

$link = sqllink();
if (!$link) {
  ajaxError('general');
}

$sql = 'DELETE FROM `blockip` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?';
sqlexec($sql, [$BLOCK_IP_TIME], $link);

$sql = 'DELETE FROM `history` WHERE UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) > ?';
sqlexec($sql, [$LOG_EXPIRE_TIME], $link);

$sql = 'SELECT * FROM `blockip` WHERE `ip` = ?';
$res = sqlexec($sql, [pm_client_ip()], $link);
$record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if ($record) {
  ajaxError('blockIP');
}

$sql = 'SELECT * FROM `pwdusrrecord` WHERE `username` = ?';
$res = sqlexec($sql, [$usr], $link);
$record = $res ? $res->fetch(PDO::FETCH_ASSOC) : false;
if (!$record) {
  ajaxError('loginFailed');
}

$sql = 'SELECT count(*) as `m` FROM `history` WHERE `userid` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
$res = sqlexec($sql, [(int) $record['id'], $ACCOUNT_BAN_TIME], $link);
$count = $res ? $res->fetch(PDO::FETCH_ASSOC) : ['m' => 0];
if ((int) $count['m'] >= $BLOCK_ACCOUNT_TRY) {
  ajaxError('blockAccount');
}

$password = (string) $record['password'];
$hash_pbkdf2 = hash_pbkdf2('sha3-512', $pw, (string) $record['salt'], $PBKDF2_ITERATIONS);

if (!hash_equals($password, (string) $hash_pbkdf2)) {
  loghistory($link, (int) $record['id'], pm_client_ip(), pm_user_agent(), 0);

  $sql = 'SELECT count(*) as `m` FROM `history` WHERE `ip` = ? AND outcome = 0 AND UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`time`) < ?';
  $res = sqlexec($sql, [pm_client_ip(), $BLOCK_IP_TIME], $link);
  $count = $res ? $res->fetch(PDO::FETCH_ASSOC) : ['m' => 0];

  if ((int) $count['m'] >= $BLOCK_IP_TRY) {
    $sql = 'INSERT INTO `blockip` VALUES (?,CURRENT_TIMESTAMP)';
    sqlexec($sql, [pm_client_ip()], $link);
  }

  ajaxError('loginFailed');
}

$totpSecret = isset($record['totp_sec']) ? trim((string) $record['totp_sec']) : '';
if ($totpSecret !== '') {
  if (!totp_is_trusted_device($usr, $hash_pbkdf2, $totpSecret)) {
    if ($totpcode === '') {
      ajaxError('TotpVerify');
    }

    if (totp_secret_equals($totpSecret, $totpcode)) {
      $sql = 'UPDATE `pwdusrrecord` SET `totp_sec` = ? WHERE `id` = ?';
      $update = sqlexec($sql, ['', (int) $record['id']], $link);
      if (!$update) {
        ajaxError('general');
      }
      totp_clear_trust_cookie($usr);
      $record['totp_sec'] = '';
    } elseif (totp_verify_code($totpSecret, $totpcode)) {
      totp_set_trust_cookie($usr, $hash_pbkdf2, $totpSecret);
    } else {
      loghistory($link, (int) $record['id'], pm_client_ip(), pm_user_agent(), 0);
      ajaxError('TotpWrong');
    }
  }
}

loghistory($link, (int) $record['id'], pm_client_ip(), pm_user_agent(), 1);

$payload = [
  'user' => $usr,
  'password' => (string) $record['password']
];

if (isset($GLOBALS['PM_TOTP_TRUST_VALUE'])) {
  $payload['totp_trust'] = $GLOBALS['PM_TOTP_TRUST_VALUE'];
}
if (!empty($GLOBALS['PM_TOTP_CLEAR_TRUST'])) {
  $payload['totp_clear'] = 1;
}

ajaxSuccess($payload);
