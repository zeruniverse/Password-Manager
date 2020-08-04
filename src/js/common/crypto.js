/*  Jeffery Zhao Aug. 3, 2020
    Use Web proto API
*/

// https://stackoverflow.com/questions/34309988/byte-array-to-hex-string-conversion-in-javascript
function _toHexString(byteArray) {
  return Array.from(byteArray, function(byte) {
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
            .then(function(key_obj){
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
            .then(function(bytes){
                return _toHexString(new Uint8Array(bytes));
            });
}

function SHA512(text){
    const text_byte = new TextEncoder().encode(text);
    return crypto.subtle.digest('SHA-512', text_byte)
        .then(function(bytes){
            return _toHexString(new Uint8Array(bytes));
        });
}

// Below two functions adapted from: https://gist.github.com/chrisveness/43bcda93af9f646d083fad678071b90a
// MIT license

async function AESGCM256Encrypt(plaintext, password) {
    const pwUtf8 = new TextEncoder().encode(password);                                 // encode password as UTF-8
    const pwHash = await crypto.subtle.digest('SHA-256', pwUtf8);                      // hash the password

    const iv = crypto.getRandomValues(new Uint8Array(12));                             // get 96-bit random iv

    const alg = { name: 'AES-GCM', iv: iv };                                           // specify algorithm to use

    const key = await crypto.subtle.importKey('raw', pwHash, alg, false, ['encrypt']); // generate key from pw

    const ptUint8 = new TextEncoder().encode(plaintext);                               // encode plaintext as UTF-8
    const ctBuffer = await crypto.subtle.encrypt(alg, key, ptUint8);                   // encrypt plaintext using key

    const ctArray = Array.from(new Uint8Array(ctBuffer));                              // ciphertext as byte array
    const ctStr = ctArray.map(byte => String.fromCharCode(byte)).join('');             // ciphertext as string
    const ctBase64 = btoa(ctStr);                                                      // encode ciphertext as base64

    const ivHex = Array.from(iv).map(b => ('00' + b.toString(16)).slice(-2)).join(''); // iv as hex string

    return ivHex+ctBase64;                                                             // return iv+ciphertext
}

async function AESGCM256Decrypt(ciphertext, password) {
    const pwUtf8 = new TextEncoder().encode(password);                                  // encode password as UTF-8
    const pwHash = await crypto.subtle.digest('SHA-256', pwUtf8);                       // hash the password

    const iv = ciphertext.slice(0,24).match(/.{2}/g).map(byte => parseInt(byte, 16));   // get iv from ciphertext

    const alg = { name: 'AES-GCM', iv: new Uint8Array(iv) };                            // specify algorithm to use

    const key = await crypto.subtle.importKey('raw', pwHash, alg, false, ['decrypt']);  // use pw to generate key

    const ctStr = atob(ciphertext.slice(24));                                           // decode base64 ciphertext
    const ctUint8 = new Uint8Array(ctStr.match(/[\s\S]/g).map(ch => ch.charCodeAt(0))); // ciphertext as Uint8Array
    // note: why doesn't ctUint8 = new TextEncoder().encode(ctStr) work?

    const plainBuffer = await crypto.subtle.decrypt(alg, key, ctUint8);                 // decrypt ciphertext using key
    const plaintext = new TextDecoder().decode(plainBuffer);                            // decode password from UTF-8

    return plaintext;                                                                   // return the plaintext
}