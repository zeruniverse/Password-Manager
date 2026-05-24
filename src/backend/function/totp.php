<?php

function totp_base32_decode($secret)
{
  $secret = strtoupper(preg_replace('/\s+/', '', (string) $secret));
  $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  $buffer = 0;
  $bitsLeft = 0;
  $result = '';

  for ($i = 0; $i < strlen($secret); $i++) {
    if ($secret[$i] === '=') {
      break;
    }

    $val = strpos($alphabet, $secret[$i]);
    if ($val === false) {
      return false;
    }

    $buffer = ($buffer << 5) | $val;
    $bitsLeft += 5;

    if ($bitsLeft >= 8) {
      $bitsLeft -= 8;
      $result .= chr(($buffer >> $bitsLeft) & 0xff);
    }
  }

  return $result;
}

function totp_normalize_secret($secret)
{
  return strtoupper(preg_replace('/\s+/', '', (string) $secret));
}

function totp_secret_equals($knownSecret, $candidateSecret)
{
  $known = totp_normalize_secret($knownSecret);
  $candidate = totp_normalize_secret($candidateSecret);
  return $known !== '' && hash_equals($known, $candidate);
}

function totp_code_at($secret, $timestamp = null)
{
  $key = totp_base32_decode($secret);
  if ($key === false || $key === '') {
    return false;
  }

  if ($timestamp === null) {
    $timestamp = time();
  }

  $counter = (int) floor($timestamp / 30);
  $binaryCounter = pack('N*', 0) . pack('N*', $counter);
  $hash = hash_hmac('sha1', $binaryCounter, $key, true);
  $offset = ord(substr($hash, -1)) & 0x0f;
  $value = unpack('N', substr($hash, $offset, 4));
  $code = ($value[1] & 0x7fffffff) % 1000000;
  return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
}

function totp_verify_code($secret, $code, $window = 1)
{
  $code = preg_replace('/\s+/', '', (string) $code);
  if (!preg_match('/\A\d{6}\z/', $code)) {
    return false;
  }

  $now = time();
  for ($i = -$window; $i <= $window; $i++) {
    $candidate = totp_code_at($secret, $now + ($i * 30));
    if ($candidate !== false && hash_equals($candidate, $code)) {
      return true;
    }
  }

  return false;
}

function totp_trust_payload($username, $passwordHash, $secret)
{
  $expires = time() + 7776000;
  $data = $username . '|' . $expires . '|' . hash('sha256', $secret);
  $mac = hash_hmac('sha256', $data, $passwordHash);
  return base64_encode($data . '|' . $mac);
}

function totp_parse_trust_payload($payload)
{
  $decoded = base64_decode((string) $payload, true);
  if ($decoded === false) {
    return null;
  }

  $parts = explode('|', $decoded);
  if (count($parts) !== 4) {
    return null;
  }

  return [
    'username' => $parts[0],
    'expires' => (int) $parts[1],
    'secret_hash' => $parts[2],
    'mac' => $parts[3]
  ];
}

function totp_is_trusted_device($username, $passwordHash, $secret)
{
  $payload = isset($_POST['frontend_totp_trust']) ? (string) $_POST['frontend_totp_trust'] : '';
  if ($payload === '') {
    return false;
  }

  $parsed = totp_parse_trust_payload($payload);
  if (!$parsed || $parsed['expires'] < time()) {
    return false;
  }

  if (!hash_equals((string) $username, (string) $parsed['username'])) {
    return false;
  }

  $data = $parsed['username'] . '|' . $parsed['expires'] . '|' . $parsed['secret_hash'];
  $expected = hash_hmac('sha256', $data, $passwordHash);

  return hash_equals($expected, $parsed['mac']) && hash_equals(hash('sha256', $secret), $parsed['secret_hash']);
}

function totp_set_trust_cookie($username, $passwordHash, $secret)
{
  $GLOBALS['PM_TOTP_TRUST_VALUE'] = totp_trust_payload($username, $passwordHash, $secret);
}

function totp_clear_trust_cookie($username)
{
  $GLOBALS['PM_TOTP_CLEAR_TRUST'] = 1;
}
