<?php

function totp_normalize_secret($secret)
{
    $secret = strtoupper(preg_replace('/[\s\-=]/', '', (string) $secret));
    if ($secret === '' || !preg_match('/^[A-Z2-7]+$/', $secret)) {
        return false;
    }
    return $secret;
}

function totp_base32_decode($secret)
{
    $secret = totp_normalize_secret($secret);
    if ($secret === false) {
        return false;
    }

    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $buffer = 0;
    $bitsLeft = 0;
    $result = '';

    for ($i = 0, $len = strlen($secret); $i < $len; $i++) {
        $value = strpos($alphabet, $secret[$i]);
        if ($value === false) {
            return false;
        }

        $buffer = ($buffer << 5) | $value;
        $bitsLeft += 5;

        if ($bitsLeft >= 8) {
            $bitsLeft -= 8;
            $result .= chr(($buffer >> $bitsLeft) & 0xff);
            $buffer = $bitsLeft > 0 ? ($buffer & ((1 << $bitsLeft) - 1)) : 0;
        }
    }

    return $result;
}

function totp_code_at($secret, $timestamp = null, $period = 30, $digits = 6)
{
    if ($timestamp === null) {
        $timestamp = time();
    }

    $secretBytes = totp_base32_decode($secret);
    if ($secretBytes === false) {
        return false;
    }

    $counter = intdiv((int) $timestamp, (int) $period);
    $high = intdiv($counter, 4294967296);
    $low = $counter % 4294967296;
    $counterBytes = pack('N2', $high, $low);

    $hmac = hash_hmac('sha1', $counterBytes, $secretBytes, true);
    $offset = ord($hmac[strlen($hmac) - 1]) & 0x0f;

    $binary = ((ord($hmac[$offset]) & 0x7f) << 24)
        | ((ord($hmac[$offset + 1]) & 0xff) << 16)
        | ((ord($hmac[$offset + 2]) & 0xff) << 8)
        | (ord($hmac[$offset + 3]) & 0xff);

    $mod = 10 ** (int) $digits;
    return str_pad((string) ($binary % $mod), (int) $digits, '0', STR_PAD_LEFT);
}

function totp_verify_code($secret, $code, $window = 1, $period = 30, $digits = 6)
{
    $code = trim((string) $code);
    if (!preg_match('/^\d{'.((int) $digits).'}$/', $code)) {
        return false;
    }

    $now = time();
    for ($i = -$window; $i <= $window; $i++) {
        $expected = totp_code_at($secret, $now + ($i * $period), $period, $digits);
        if ($expected !== false && hash_equals($expected, $code)) {
            return true;
        }
    }

    return false;
}

function totp_secret_equals($storedSecret, $inputSecret)
{
    $storedSecret = totp_normalize_secret($storedSecret);
    $inputSecret = totp_normalize_secret($inputSecret);

    if ($storedSecret === false || $inputSecret === false) {
        return false;
    }

    return hash_equals($storedSecret, $inputSecret);
}

function totp_cookie_name($username)
{
    return 'pwdrecord_'.urlencode((string) $username);
}

function totp_trust_cookie_value($passwordHash, $username, $secret)
{
    global $GLOBAL_SALT_3, $PBKDF2_ITERATIONS;

    $secret = totp_normalize_secret($secret);
    if ($secret === false) {
        $secret = '';
    }

    return hash_pbkdf2(
        'sha3-512',
        (string) $passwordHash,
        $GLOBAL_SALT_3.'.totp.'.urlencode((string) $username).'.'.$secret,
        max(intdiv($PBKDF2_ITERATIONS, 100), 10)
    );
}

function totp_is_trusted_device($username, $passwordHash, $secret)
{
    $cookieName = totp_cookie_name($username);
    if (!isset($_COOKIE[$cookieName])) {
        return false;
    }

    $expected = totp_trust_cookie_value($passwordHash, $username, $secret);
    return hash_equals($expected, (string) $_COOKIE[$cookieName]);
}

function totp_set_trust_cookie($username, $passwordHash, $secret)
{
    global $PIN_EXPIRE_TIME;

    setcookie(
        totp_cookie_name($username),
        totp_trust_cookie_value($passwordHash, $username, $secret),
        time() + $PIN_EXPIRE_TIME + 3600,
        '/; samesite=strict',
        null,
        true,
        false
    );
}

function totp_clear_trust_cookie($username)
{
    setcookie(
        totp_cookie_name($username),
        '',
        time() - 3600,
        '/; samesite=strict',
        null,
        true,
        false
    );
}