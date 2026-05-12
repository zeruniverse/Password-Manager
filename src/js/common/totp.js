var PasswordManagerMFA = (function() {
    var DEFAULT_ALGORITHM = "SHA1";
    var DEFAULT_DIGITS = 6;
    var DEFAULT_PERIOD = 30;
    var BASE32_ALPHABET = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

    function leftPad(value, length) {
        value = String(value);
        while (value.length < length) {
            value = "0" + value;
        }
        return value;
    }

    function normalizeSecret(secret) {
        if (!secret) {
            throw "MFA secret can't be empty.";
        }

        secret = String(secret).replace(/[\s\-=]/g, "").toUpperCase();

        if (!/^[A-Z2-7]+$/.test(secret)) {
            throw "MFA secret must be a Base32 string.";
        }

        return secret;
    }

    function base32ToBytes(secret) {
        secret = normalizeSecret(secret);

        var bits = "";
        var bytes = [];

        for (var i = 0; i < secret.length; i++) {
            var value = BASE32_ALPHABET.indexOf(secret.charAt(i));
            if (value < 0) {
                throw "MFA secret must be a Base32 string.";
            }

            bits += leftPad(value.toString(2), 5);
        }

        for (var j = 0; j + 8 <= bits.length; j += 8) {
            bytes.push(parseInt(bits.substring(j, j + 8), 2));
        }

        return new Uint8Array(bytes);
    }

    function normalizeAlgorithm(algorithm) {
        algorithm = String(algorithm || DEFAULT_ALGORITHM).replace(/[-_]/g, "").toUpperCase();

        if (algorithm === "SHA1") {
            return "SHA1";
        }

        if (algorithm === "SHA256") {
            return "SHA256";
        }

        if (algorithm === "SHA512") {
            return "SHA512";
        }

        throw "Unsupported MFA algorithm: " + algorithm;
    }

    function webCryptoHashName(algorithm) {
        algorithm = normalizeAlgorithm(algorithm);

        if (algorithm === "SHA1") {
            return "SHA-1";
        }

        if (algorithm === "SHA256") {
            return "SHA-256";
        }

        if (algorithm === "SHA512") {
            return "SHA-512";
        }

        throw "Unsupported MFA algorithm: " + algorithm;
    }

    function normalizeConfig(config) {
        config = config || {};

        var secret = normalizeSecret(config.secret);
        var digits = parseInt(config.digits || DEFAULT_DIGITS, 10);
        var period = parseInt(config.period || DEFAULT_PERIOD, 10);

        if ([6, 7, 8].indexOf(digits) === -1) {
            throw "MFA digits must be 6, 7 or 8.";
        }

        if (!period || period < 1) {
            throw "MFA period must be a positive number.";
        }

        return {
            "type": "totp",
            "version": 1,
            "secret": secret,
            "issuer": config.issuer || "",
            "account": config.account || "",
            "algorithm": normalizeAlgorithm(config.algorithm),
            "digits": digits,
            "period": period
        };
    }

    function parseConfig(input, fallback) {
        input = String(input || "").trim();
        fallback = fallback || {};

        if (input === "") {
            throw "MFA setup code can't be empty.";
        }

        if (/^otpauth:\/\//i.test(input)) {
            var url = new URL(input);

            if (url.protocol.toLowerCase() !== "otpauth:") {
                throw "Unsupported MFA URI.";
            }

            var otpType = url.hostname.toLowerCase();
            if (otpType !== "totp") {
                throw "Only TOTP MFA URI is supported.";
            }

            var label = decodeURIComponent(url.pathname.replace(/^\//, ""));
            var issuer = url.searchParams.get("issuer") || "";
            var account = fallback.account || "";

            if (label) {
                var parts = label.split(":");

                if (!issuer && parts.length > 1) {
                    issuer = parts[0];
                }

                account = parts.length > 1 ? parts.slice(1).join(":") : label;
            }

            return normalizeConfig({
                "secret": url.searchParams.get("secret"),
                "issuer": issuer,
                "account": account,
                "algorithm": url.searchParams.get("algorithm") || DEFAULT_ALGORITHM,
                "digits": url.searchParams.get("digits") || DEFAULT_DIGITS,
                "period": url.searchParams.get("period") || DEFAULT_PERIOD
            });
        }

        return normalizeConfig({
            "secret": input,
            "issuer": fallback.issuer || "",
            "account": fallback.account || "",
            "algorithm": fallback.algorithm || DEFAULT_ALGORITHM,
            "digits": fallback.digits || DEFAULT_DIGITS,
            "period": fallback.period || DEFAULT_PERIOD
        });
    }

    function counterToBytes(counter) {
        var bytes = new Uint8Array(8);

        for (var i = 7; i >= 0; i--) {
            bytes[i] = counter & 0xff;
            counter = Math.floor(counter / 256);
        }

        return bytes;
    }

    function secondsRemaining(config, time) {
        config = normalizeConfig(config);

        var epoch = Math.floor((time || Date.now()) / 1000);
        var remaining = config.period - (epoch % config.period);

        return remaining === 0 ? config.period : remaining;
    }

    function generateCode(config, time) {
        try {
            config = normalizeConfig(config);
        } catch (error) {
            return Promise.reject(error);
        }

        if (!window.crypto || !window.crypto.subtle) {
            return Promise.reject("WebCrypto is unavailable in this browser.");
        }

        var keyBytes = base32ToBytes(config.secret);
        var counter = Math.floor(Math.floor((time || Date.now()) / 1000) / config.period);

        return window.crypto.subtle.importKey(
            "raw",
            keyBytes,
            {
                "name": "HMAC",
                "hash": {
                    "name": webCryptoHashName(config.algorithm)
                }
            },
            false,
            ["sign"]
        ).then(function(key) {
            return window.crypto.subtle.sign("HMAC", key, counterToBytes(counter));
        }).then(function(signature) {
            var hmac = new Uint8Array(signature);
            var offset = hmac[hmac.length - 1] & 0xf;

            var binary =
                ((hmac[offset] & 0x7f) << 24) |
                ((hmac[offset + 1] & 0xff) << 16) |
                ((hmac[offset + 2] & 0xff) << 8) |
                (hmac[offset + 3] & 0xff);

            var mod = Math.pow(10, config.digits);

            return {
                "code": leftPad(binary % mod, config.digits),
                "remaining": secondsRemaining(config, time),
                "config": config
            };
        });
    }

    function decodeQRCodeFile(file) {
        if (!file) {
            return Promise.reject("No MFA QR code image selected.");
        }

        if (typeof jsQR !== "function") {
            return Promise.reject("jsQR is not loaded.");
        }

        return new Promise(function(resolve, reject) {
            var reader = new FileReader();

            reader.onload = function(event) {
                var image = new Image();

                image.onload = function() {
                    try {
                        var canvas = document.createElement("canvas");
                        var width = image.naturalWidth || image.width;
                        var height = image.naturalHeight || image.height;

                        canvas.width = width;
                        canvas.height = height;

                        var context = canvas.getContext("2d", {
                            "willReadFrequently": true
                        });

                        if (!context) {
                            reject("Canvas 2D context is unavailable.");
                            return;
                        }

                        context.drawImage(image, 0, 0, width, height);

                        var imageData = context.getImageData(0, 0, width, height);
                        var result = jsQR(imageData.data, width, height);

                        if (!result || !result.data) {
                            reject("No QR code found in this image.");
                            return;
                        }

                        resolve(result.data);
                    } catch (error) {
                        reject(error && error.message ? error.message : "Failed to decode MFA QR code.");
                    }
                };

                image.onerror = function() {
                    reject("Failed to load MFA QR code image.");
                };

                image.src = event.target.result;
            };

            reader.onerror = function() {
                reject("Failed to read MFA QR code image.");
            };

            reader.readAsDataURL(file);
        });
    }

    return {
        "normalizeConfig": normalizeConfig,
        "parseConfig": parseConfig,
        "generateCode": generateCode,
        "secondsRemaining": secondsRemaining,
        "decodeQRCodeFile": decodeQRCodeFile
    };
})();
