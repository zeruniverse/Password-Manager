var PasswordManagerLoginTOTP = (function() {
    var setupSecret = "";

    var BASE32_ALPHABET = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";

    function normalizeSecret(secret) {
        return String(secret || "").replace(/[\s\-=]/g, "").toUpperCase();
    }

    function bytesToBase32(bytes) {
        var bits = "";
        var output = "";

        for (var i = 0; i < bytes.length; i++) {
            var bin = bytes[i].toString(2);
            while (bin.length < 8) {
                bin = "0" + bin;
            }
            bits += bin;
        }

        for (var j = 0; j < bits.length; j += 5) {
            var chunk = bits.substring(j, j + 5);
            while (chunk.length < 5) {
                chunk += "0";
            }
            output += BASE32_ALPHABET[parseInt(chunk, 2)];
        }

        return output;
    }

    function generateSecret(byteLength) {
        byteLength = byteLength || 20;

        if (!window.crypto || typeof window.crypto.getRandomValues !== "function") {
            throw "Secure random generator is unavailable in this browser.";
        }

        var bytes = new Uint8Array(byteLength);
        window.crypto.getRandomValues(bytes);
        return bytesToBase32(bytes);
    }

    function formatSecret(secret) {
        secret = normalizeSecret(secret);
        return secret.replace(/(.{4})/g, "$1 ").trim();
    }

    function buildOtpAuthUri(secret) {
        var issuer = "Password-Manager";
        var account = backend && backend.user ? backend.user : "user";
        var label = issuer + ":" + account;

        var params = [
            ["secret", normalizeSecret(secret)],
            ["issuer", issuer],
            ["algorithm", "SHA1"],
            ["digits", "6"],
            ["period", "30"]
        ].map(function(pair) {
            return encodeURIComponent(pair[0]) + "=" + encodeURIComponent(pair[1]);
        }).join("&");

        return "otpauth://totp/" + encodeURIComponent(label) + "?" + params;
    }

    function updateMenu(enabled) {
        $("#navBtnTOTP").text(enabled ? "Turn off 2FA" : "Turn on 2FA");
    }

    function refreshStatus() {
        if (!backend || typeof backend.getTotpStatus !== "function") {
            return Promise.resolve(false);
        }

        return backend.getTotpStatus()
            .then(function(enabled) {
                updateMenu(enabled);
                return enabled;
            });
    }

    function setBusy(busy) {
        $("#navBtnTOTP").toggleClass("disabled", busy);
        $("#totpSetupSubmit").attr("disabled", busy);
        $("#totpSetupCancel").attr("disabled", busy);
    }

    function drawQrCode(uri) {
        if (typeof qrcode !== "function") {
            throw "QR generator is not loaded.";
        }

        var qr = qrcode(0, "M");
        qr.addData(uri);
        qr.make();

        $("#totpSetupQr").empty().append(qr.createSvgTag(4, 0));
    }

    function startEnable() {
        try {
            setupSecret = generateSecret(20);

            var uri = buildOtpAuthUri(setupSecret);
            $("#totpSetupSecret").text(formatSecret(setupSecret));
            $("#totpSetupUri").val(uri);
            $("#totpSetupCode").val("");

            drawQrCode(uri);

            $("#totpSetup").modal("show");
        } catch (error) {
            showMessage("warning", String(error), true);
        }
    }

    function submitEnable() {
        var code = $("#totpSetupCode").val().trim();

        if (!/^\d{6}$/.test(code)) {
            showMessage("warning", "Please enter the 6-digit code from your authenticator app.", true);
            return;
        }

        setBusy(true);

        backend.enableTotp(setupSecret, code)
            .then(function() {
                updateMenu(true);
                $("#totpSetup").modal("hide");
                showMessage("success", "successfully turned on 2FA");
            })
            .catch(function(msg) {
                showMessage("warning", msg, true);
            })
            .then(function() {
                setBusy(false);
            });
    }

    function turnOff() {
        if (!confirm("Turn off 2FA for this account?")) {
            return;
        }

        setBusy(true);

        backend.disableTotp()
            .then(function() {
                updateMenu(false);
                showMessage("success", "successfully turned off 2FA");
            })
            .catch(function(msg) {
                showMessage("warning", msg, true);
            })
            .then(function() {
                setBusy(false);
            });
    }

    function handleMenuClick(event) {
        event.preventDefault();

        refreshStatus()
            .then(function(enabled) {
                if (enabled) {
                    turnOff();
                } else {
                    startEnable();
                }
            })
            .catch(function(msg) {
                showMessage("warning", msg, true);
            });
    }

    function bind() {
        $("#navBtnTOTP").on("click", handleMenuClick);

        $("#totpSetupSubmit").on("click", submitEnable);

        $("#totpSetup").on("shown.bs.modal", function() {
            $("#totpSetupCode").focus();
        });

        $("#totpSetup").on("hidden.bs.modal", function() {
            setupSecret = "";
            $("#totpSetupQr").empty();
            $("#totpSetupSecret").text("");
            $("#totpSetupUri").val("");
            $("#totpSetupCode").val("");
        });

        refreshStatus();
    }

    return {
        "bind": bind,
        "refreshStatus": refreshStatus,
        "updateMenu": updateMenu
    };
})();