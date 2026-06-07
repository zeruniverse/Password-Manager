/*  Jeffery Zhao Aug. 3, 2020
    Use Web proto API
*/

// https://stackoverflow.com/questions/34309988/byte-array-to-hex-string-conversion-in-javascript
function _toHexString(byteArray) {
    return Array.from(byteArray, function (byte) {
        return ('0' + (byte & 0xFF).toString(16)).slice(-2);
    }).join('')
}

function PBKDF2_SHA512(password, salt, iterations) {
    const encoder = new TextEncoder();
    const pass_byte = encoder.encode(password);
    const salt_byte = encoder.encode(salt);
    return crypto.subtle.importKey(
        'raw',
        pass_byte,
        'PBKDF2',
        false,
        ['deriveBits']
    )
        .then(function (key_obj) {
            return crypto.subtle.deriveBits(
                {
                    name: "PBKDF2",
                    hash: "SHA-512",
                    salt: salt_byte,
                    iterations: iterations
                },
                key_obj,
                512
            );
        })
        .then(function (bytes) {
            return _toHexString(new Uint8Array(bytes));
        });
}

function SHA512(text) {
    const text_byte = new TextEncoder().encode(text);
    return crypto.subtle.digest('SHA-512', text_byte)
        .then(function (bytes) {
            return _toHexString(new Uint8Array(bytes));
        });
}

function _bytesToBase64Url(bytes) {
    var binary = '';
    var chunkSize = 0x8000;
    var i;

    for (i = 0; i < bytes.length; i += chunkSize) {
        binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunkSize));
    }

    return btoa(binary)
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/g, '');
}

function _base64UrlToBytes(str) {
    if (typeof str !== 'string' || !/^[A-Za-z0-9_-]+$/.test(str)) {
        throw 'Invalid ciphertext.';
    }

    var base64 = str.replace(/-/g, '+').replace(/_/g, '/');
    var remainder = base64.length % 4;
    var binary;
    var bytes;
    var i;

    if (remainder === 1) {
        throw 'Invalid ciphertext.';
    }
    if (remainder > 0) {
        base64 += '='.repeat(4 - remainder);
    }

    binary = atob(base64);
    bytes = new Uint8Array(binary.length);

    for (i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    return bytes;
}

async function _deriveAESGCMKey(password, usages) {
    const pwUtf8 = new TextEncoder().encode(password);
    const pwHash = await crypto.subtle.digest('SHA-256', pwUtf8);

    return crypto.subtle.importKey('raw', pwHash, 'AES-GCM', false, usages);
}


var _aesGcm256UsedIVs = new Set();
var _aesGcm256IVReservation = Promise.resolve();

function _aesGcm256IVKey(iv) {
    return _bytesToBase64Url(iv);
}

function _aesGcm256RememberRawIV(raw) {
    _aesGcm256UsedIVs.add(_aesGcm256IVKey(raw.slice(0, 12)));
}

function AESGCM256RememberCiphertextIV(ciphertext) {
    var raw;

    try {
        raw = _base64UrlToBytes(ciphertext);
    } catch (err) {
        return false;
    }

    if (raw.length < 28) { // 12-byte nonce + 16-byte tag, even for empty plaintext
        return false;
    }

    _aesGcm256RememberRawIV(raw);
    return true;
}

function _generateUniqueAESGCM256IV() {
    var iv;
    var ivKey;

    do {
        iv = crypto.getRandomValues(new Uint8Array(12));
        ivKey = _aesGcm256IVKey(iv);
    } while (_aesGcm256UsedIVs.has(ivKey));

    _aesGcm256UsedIVs.add(ivKey);
    return iv;
}

function _reserveUniqueAESGCM256IV() {
    _aesGcm256IVReservation = _aesGcm256IVReservation.then(
        function () {
            return _generateUniqueAESGCM256IV();
        },
        function () {
            return _generateUniqueAESGCM256IV();
        }
    );

    return _aesGcm256IVReservation.then(function (iv) {
        return new Uint8Array(iv);
    });
}


async function AESGCM256Encrypt(plaintext, password, username) {
    const key = await _deriveAESGCMKey(password, ['encrypt']);
    const iv = await _reserveUniqueAESGCM256IV();
    const alg = { name: 'AES-GCM', iv: iv, additionalData: new TextEncoder().encode(username || ''), tagLength: 128 };
    const ptUint8 = new TextEncoder().encode(plaintext);
    const ctBuffer = await crypto.subtle.encrypt(alg, key, ptUint8);
    const ctUint8 = new Uint8Array(ctBuffer);
    const out = new Uint8Array(iv.length + ctUint8.length);

    out.set(iv, 0);
    out.set(ctUint8, iv.length);

    return _bytesToBase64Url(out);
}

async function AESGCM256Decrypt(ciphertext, password, username) {
    const raw = _base64UrlToBytes(ciphertext);

    if (raw.length < 28) { // 12-byte nonce + 16-byte tag, even for empty plaintext
        throw 'Invalid ciphertext.';
    }

    _aesGcm256RememberRawIV(raw);

    const key = await _deriveAESGCMKey(password, ['decrypt']);
    const iv = raw.slice(0, 12);
    const ctUint8 = raw.slice(12);
    const alg = { name: 'AES-GCM', iv: iv, additionalData: new TextEncoder().encode(username || ''), tagLength: 128 };
    const plainBuffer = await crypto.subtle.decrypt(alg, key, ctUint8);

    return new TextDecoder().decode(plainBuffer);
}