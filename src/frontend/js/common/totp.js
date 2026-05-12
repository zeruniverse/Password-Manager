var PasswordManagerMFA = (function () {
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
        ).then(function (key) {
            return window.crypto.subtle.sign("HMAC", key, counterToBytes(counter));
        }).then(function (signature) {
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

    function getImageSize(image) {
        return {
            "width": image.naturalWidth || image.width,
            "height": image.naturalHeight || image.height
        };
    }

    function loadImageFromFile(file) {
        return new Promise(function (resolve, reject) {
            var urlApi = window.URL || window.webkitURL;

            if (!urlApi || typeof urlApi.createObjectURL !== "function") {
                reject("Blob URL is unavailable in this browser.");
                return;
            }

            var image = new Image();
            var objectUrl = urlApi.createObjectURL(file);

            image.onload = function () {
                urlApi.revokeObjectURL(objectUrl);

                var size = getImageSize(image);
                if (!size.width || !size.height) {
                    reject("Failed to read MFA QR code image size.");
                    return;
                }

                resolve(image);
            };

            image.onerror = function () {
                urlApi.revokeObjectURL(objectUrl);
                reject("Failed to load MFA QR code image.");
            };

            image.src = objectUrl;
        });
    }

    function addQRDecodePlan(plans, seen, sx, sy, sw, sh, maxSide) {
        sx = Math.max(0, Math.floor(sx));
        sy = Math.max(0, Math.floor(sy));
        sw = Math.max(1, Math.floor(sw));
        sh = Math.max(1, Math.floor(sh));
        maxSide = Math.max(256, Math.floor(maxSide));

        var key = sx + ":" + sy + ":" + sw + ":" + sh + ":" + maxSide;

        if (seen[key]) {
            return;
        }

        seen[key] = true;

        plans.push({
            "sx": sx,
            "sy": sy,
            "sw": sw,
            "sh": sh,
            "maxSide": maxSide
        });
    }

    function buildQRDecodePlans(width, height) {
        var plans = [];
        var seen = {};
        var fullMaxSides = [3072, 2048, 1536, 1024];

        for (var i = 0; i < fullMaxSides.length; i++) {
            addQRDecodePlan(plans, seen, 0, 0, width, height, fullMaxSides[i]);
        }

        var centerRatios = [0.85, 0.70, 0.55, 0.40, 0.28];

        for (var j = 0; j < centerRatios.length; j++) {
            var ratio = centerRatios[j];
            var cropW = width * ratio;
            var cropH = height * ratio;

            addQRDecodePlan(
                plans,
                seen,
                (width - cropW) / 2,
                (height - cropH) / 2,
                cropW,
                cropH,
                2048
            );
        }

        /*
        * Phone photos often contain the QR code away from the exact center.
        * Try a light 3x3 crop grid. Each crop is scaled up, which helps when
        * the QR code occupies only a small part of the photo.
        */
        var gridRatio = 0.50;
        var gridCenters = [0.25, 0.50, 0.75];

        for (var gx = 0; gx < gridCenters.length; gx++) {
            for (var gy = 0; gy < gridCenters.length; gy++) {
                var gridW = width * gridRatio;
                var gridH = height * gridRatio;
                var cx = width * gridCenters[gx];
                var cy = height * gridCenters[gy];

                addQRDecodePlan(
                    plans,
                    seen,
                    cx - gridW / 2,
                    cy - gridH / 2,
                    gridW,
                    gridH,
                    1800
                );
            }
        }

        return plans;
    }

    function drawQRDecodeCandidate(image, plan) {
        var canvas = document.createElement("canvas");
        var scale = plan.maxSide / Math.max(plan.sw, plan.sh);
        var targetWidth = Math.max(1, Math.round(plan.sw * scale));
        var targetHeight = Math.max(1, Math.round(plan.sh * scale));

        canvas.width = targetWidth;
        canvas.height = targetHeight;

        var context = canvas.getContext("2d", {
            "willReadFrequently": true
        });

        if (!context) {
            throw "Canvas 2D context is unavailable.";
        }

        context.imageSmoothingEnabled = true;

        if ("imageSmoothingQuality" in context) {
            context.imageSmoothingQuality = "high";
        }

        context.drawImage(
            image,
            plan.sx,
            plan.sy,
            plan.sw,
            plan.sh,
            0,
            0,
            targetWidth,
            targetHeight
        );

        return canvas;
    }

    function decodeQRCodeCanvas(canvas) {
        var context = canvas.getContext("2d", {
            "willReadFrequently": true
        });

        if (!context) {
            throw "Canvas 2D context is unavailable.";
        }

        var imageData = context.getImageData(0, 0, canvas.width, canvas.height);

        var result = jsQR(
            imageData.data,
            canvas.width,
            canvas.height,
            {
                "inversionAttempts": "attemptBoth"
            }
        );

        if (result && result.data) {
            return result.data;
        }

        return null;
    }

    function createContrastEnhancedCanvas(sourceCanvas) {
        var canvas = document.createElement("canvas");
        canvas.width = sourceCanvas.width;
        canvas.height = sourceCanvas.height;

        var sourceContext = sourceCanvas.getContext("2d", {
            "willReadFrequently": true
        });

        var targetContext = canvas.getContext("2d", {
            "willReadFrequently": true
        });

        if (!sourceContext || !targetContext) {
            throw "Canvas 2D context is unavailable.";
        }

        var imageData = sourceContext.getImageData(0, 0, sourceCanvas.width, sourceCanvas.height);
        var data = imageData.data;
        var min = 255;
        var max = 0;
        var gray;
        var i;

        for (i = 0; i < data.length; i += 4) {
            gray = Math.round(data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
            if (gray < min) {
                min = gray;
            }
            if (gray > max) {
                max = gray;
            }
        }

        var range = max - min;

        if (range < 16) {
            targetContext.putImageData(imageData, 0, 0);
            return canvas;
        }

        for (i = 0; i < data.length; i += 4) {
            gray = Math.round(data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
            gray = Math.max(0, Math.min(255, Math.round((gray - min) * 255 / range)));

            data[i] = gray;
            data[i + 1] = gray;
            data[i + 2] = gray;
        }

        targetContext.putImageData(imageData, 0, 0);

        return canvas;
    }

    function otsuThresholdFromHistogram(histogram, total) {
        var sum = 0;
        var sumBackground = 0;
        var weightBackground = 0;
        var weightForeground;
        var maxVariance = 0;
        var threshold = 127;

        for (var i = 0; i < 256; i++) {
            sum += i * histogram[i];
        }

        for (var t = 0; t < 256; t++) {
            weightBackground += histogram[t];

            if (weightBackground === 0) {
                continue;
            }

            weightForeground = total - weightBackground;

            if (weightForeground === 0) {
                break;
            }

            sumBackground += t * histogram[t];

            var meanBackground = sumBackground / weightBackground;
            var meanForeground = (sum - sumBackground) / weightForeground;
            var variance = weightBackground * weightForeground *
                Math.pow(meanBackground - meanForeground, 2);

            if (variance > maxVariance) {
                maxVariance = variance;
                threshold = t;
            }
        }

        return threshold;
    }

    function createThresholdCanvas(sourceCanvas) {
        var canvas = document.createElement("canvas");
        canvas.width = sourceCanvas.width;
        canvas.height = sourceCanvas.height;

        var sourceContext = sourceCanvas.getContext("2d", {
            "willReadFrequently": true
        });

        var targetContext = canvas.getContext("2d", {
            "willReadFrequently": true
        });

        if (!sourceContext || !targetContext) {
            throw "Canvas 2D context is unavailable.";
        }

        var imageData = sourceContext.getImageData(0, 0, sourceCanvas.width, sourceCanvas.height);
        var data = imageData.data;
        var histogram = new Array(256);
        var grayValues = new Uint8Array(data.length / 4);
        var i;
        var pixelIndex = 0;

        for (i = 0; i < 256; i++) {
            histogram[i] = 0;
        }

        for (i = 0; i < data.length; i += 4) {
            var gray = Math.round(data[i] * 0.299 + data[i + 1] * 0.587 + data[i + 2] * 0.114);
            grayValues[pixelIndex++] = gray;
            histogram[gray]++;
        }

        var threshold = otsuThresholdFromHistogram(histogram, grayValues.length);

        pixelIndex = 0;

        for (i = 0; i < data.length; i += 4) {
            var value = grayValues[pixelIndex++] >= threshold ? 255 : 0;

            data[i] = value;
            data[i + 1] = value;
            data[i + 2] = value;
        }

        targetContext.putImageData(imageData, 0, 0);

        return canvas;
    }

    function decodeQRCodeFile(file) {
        if (!file) {
            return Promise.reject("No MFA QR code image selected.");
        }

        if (typeof jsQR !== "function") {
            return Promise.reject("jsQR is not loaded.");
        }

        return loadImageFromFile(file).then(function (image) {
            var size = getImageSize(image);
            var plans = buildQRDecodePlans(size.width, size.height);
            var lastError = null;

            for (var i = 0; i < plans.length; i++) {
                try {
                    var canvas = drawQRDecodeCandidate(image, plans[i]);

                    var decoded = decodeQRCodeCanvas(canvas);
                    if (decoded) {
                        return decoded;
                    }

                    var contrastCanvas = createContrastEnhancedCanvas(canvas);
                    decoded = decodeQRCodeCanvas(contrastCanvas);
                    if (decoded) {
                        return decoded;
                    }

                    var thresholdCanvas = createThresholdCanvas(canvas);
                    decoded = decodeQRCodeCanvas(thresholdCanvas);
                    if (decoded) {
                        return decoded;
                    }
                } catch (error) {
                    lastError = error;
                }
            }

            if (lastError) {
                throw "No QR code found in this image. Last decode error: " + lastError;
            }

            throw "No QR code found in this image. Try cropping the photo so the QR code fills most of the image, avoid glare, and keep the camera parallel to the QR code.";
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
