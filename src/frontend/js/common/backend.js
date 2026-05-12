// helpers for javascript mixins-pattern
let mix = (superclass) => new MixinBuilder(superclass);
var PASSWORD_MANAGER_VERSION = '11.08';
class MixinBuilder {
    constructor(superclass) {
        this.superclass = superclass;
    }

    with(...mixins) {
        return mixins.reduce((c, mixin) => mixin(c), this.superclass);
    }
}



function pmBackendConfig() {
    if (typeof window.pmConfig === "function") {
        return window.pmConfig();
    }

    throw "frontend_routes.js is not loaded";
}

//Base Class for Backends
class commonBackend {
    doPost(endpoint, data) {
        data = data || {};

        var cfg = pmBackendConfig();
        var endpointDomain = this.domain || cfg.apiBaseUrl || "";

        if (endpointDomain === "") {
            throw "PASSWORD_MANAGER_CONFIG.apiBaseUrl is not configured";
        }

        if (endpointDomain.slice(-1) !== "/") {
            endpointDomain += "/";
        }

        var body = new FormData();

        for (let key in data) {
            if (!Object.prototype.hasOwnProperty.call(data, key)) {
                continue;
            }

            if (typeof data[key] === "undefined" || data[key] === null) {
                continue;
            }

            body.append(key, data[key]);
        }

        /*
         * Split frontend/backend session transport.
         *
         * api_session_id is a current-tab login session value. It belongs in
         * sessionStorage, not localStorage.
         */
        if (!Object.prototype.hasOwnProperty.call(data, "api_session_id") && sessionStorage.pm_api_session_id) {
            body.append("api_session_id", sessionStorage.pm_api_session_id);
        }

        /*
         * PIN cookies live on the trusted frontend domain, so info.php needs the
         * frontend to forward these values explicitly.
         */
        if (endpoint === "info" && typeof getCookie === "function") {
            body.append("frontend_username", getCookie("username") || "");
            body.append("frontend_device", getCookie("device") || "");
        }

        /*
         * TOTP trusted-device state is intentionally persistent. It is not a login
         * session token, so it remains in localStorage.
         */
        if (endpoint === "check" && localStorage.pm_totp_trust) {
            body.append("frontend_totp_trust", localStorage.pm_totp_trust);
        }

        var request = new Request(endpointDomain + "rest/" + endpoint + ".php", {
            method: "post",
            cache: "no-cache",
            credentials: "omit",
            body: body
        });

        return fetch(request)
            .then(function (response) {
                var ct = response.headers.get("Content-Type") || "";

                if (!response.ok || ct.indexOf("application/json") !== 0) {
                    return Promise.reject(response);
                }

                return response.json()
                    .then(commonBackend.checkApplicationResult);
            });
    }

    get sessionToken() {
        if (this._sessionToken) {
            return this._sessionToken;
        }

        return sessionStorage.session_token;
    }

    static checkApplicationResult(msg) {
        if (!msg || msg["status"] !== "success") {
            throw (msg && msg["message"] ? msg["message"] : "Request failed");
        }

        if (msg["api_session_id"]) {
            sessionStorage.pm_api_session_id = msg["api_session_id"];
        }

        if (msg["session_token"]) {
            sessionStorage.session_token = msg["session_token"];
        }

        if (msg["totp_trust"]) {
            localStorage.pm_totp_trust = msg["totp_trust"];
        }

        if (msg["totp_clear"]) {
            localStorage.removeItem("pm_totp_trust");
        }

        return msg;
    }
};

//mixin for events
let EventHandler = (superclass) => class extends superclass {
    initEvents() {
        this.eventSubscribers = {};
        for (let event of this.events) {
            this.eventSubscribers[event] = [];
        }
    }
    registerEvent(entry, callback) {
        this.eventSubscribers[entry].push(callback);
    }
    callEvent(entry, data) {
        for (var callback of this.eventSubscribers[entry]) {
            callback(data);
        }
    }
};

//mixin for events
let AuthenticatedSession = (superclass) => class extends superclass {
    doPost(endpoint, data) {
        data = data || {};
        data["session_token"] = this.sessionToken;
        return super.doPost(endpoint, data);
    }
    logout(reason) {
        reason = reason || "";
        var self = this;
        callPlugins("preLogout", {});
        return self.doPost("logout", {})
            .catch(function () {
                /*
                 * Local logout must still happen if the backend session is already gone
                 * or the network is unavailable.
                 */
                return {};
            })
            .then(function () {
                sessionStorage.removeItem("session_token");
                sessionStorage.removeItem("pm_api_session_id");
                sessionStorage.removeItem("pwdsk");
                sessionStorage.removeItem("confusion_key");
                self.callEvent("logout", { reason: reason });
                return reason;
            });
    }
    untrustAndLogout() {
        var self = this;
        localStorage.clear();
        var promises = [];
        var username = getCookie("username");
        var device = getCookie("device");
        if ((device != null) && (device !== "")) {
            promises.push(self.doPost("deletepin", { user: username, device: device }));
        }
        return Promise.all(promises)
            .then(function () {
                deleteCookie("device");
                deleteCookie("username");
                deleteCookie("pwdrecord_" + encodeURIComponent(username));
                return self.logout();
            });
    }
};

//mixin for timeout function
let Timeout = (superclass) => class extends superclass {
    //Timout
    resetTimeout() {
        if (!this.default_timeout) {
            return;
        }
        this.timeout = this.default_timeout + Math.floor(Date.now() / 1000);
    }
    clientCountdown() {
        if (this.isTimeout) {
            this.logout("Logged out due to inactivity");
            this.clearTimeout();
        }
    }
    checkSession() {
        var self = this;
        this.doPost("sessionalive")
            .catch(function (err) {
                var text = String(err || "");
                if (
                    text.indexOf("session unavailable") !== -1 ||
                    text.indexOf("AUTHENTICATION") !== -1 ||
                    text.indexOf("Invalid session token") !== -1
                ) {
                    self.logout("Session timed out");
                    self.clearTimeout();
                    return;
                }
                console.log(err);
            });
    }
    initTimeout() {
        this.countdownInterval = setInterval(this.clientCountdown.bind(this), 5000);
        this.sessionCountdownInterval = setInterval(this.checkSession.bind(this), 5000);
    }
    clearTimeout() {
        clearInterval(this.sessionCountdownInterval);
        this.sessionCountdownInterval = -1;
        clearInterval(this.countdownInterval);
        this.countdownInterval = -1;

    }
    // extended timeout for actions that take a long time
    extendedTimeout() {
        this.timeout = 1000000 + Math.floor(Date.now() / 1000);
    }
    get isTimeout() {
        return this.timeout < Math.floor(Date.now() / 1000);
    }
};

//mixin for accounts
let Accounts = (superclass) => class extends superclass {
    // create account with name, pwd and other
    // if pwd is empty a random password is generated
    addAccount(name, pwd, other) {
        var self = this;
        if (name == "") {
            return Promise.reject("Account name can't be empty");
        }
        if (pwd == "") {
            pwd = this.encryptionWrapper.generatePassphrase(this.default_length);
        }
        let account = new Account(null, name, "");
        return account.setEncryptionWrapper(self.encryptionWrapper)
            .then(function () {
                return account.setPassword(pwd);
            })
            .then(function () {
                for (let key in other) {
                    if (other[key] !== null && typeof other[key] !== "undefined") {
                        account.setOther(key, other[key]);
                    }
                }
                callPlugins("addAccountPreSend", { "account": account, "name": name, "password": pwd, "other": other });
                return account.getEncrypted()
            })
            .then(function (encAccount) {
                return self.doPost("insert", encAccount);
            });
    }
    updateAccount(id, name, newpwd, other) {
        var self = this;
        if (name == "") {
            return Promise.reject("Account name can't be empty");
        }
        var account = this.accounts[id];
        var oldData = {
            "name": account.accountName,
            "other": account.other
        };
        var prepareOldDataPromise;
        if (newpwd != "") {
            prepareOldDataPromise = account.getPassword()
                .then(function (pwd) {
                    oldData["password"] = pwd;
                });
        }
        else {
            prepareOldDataPromise = Promise.resolve();
        }
        return prepareOldDataPromise
            .then(function () {
                return account.setAccountName(name)
            })
            .then(function () {
                account.clearVisibleOther();
                for (let x in other) {
                    if (other[x] === null || typeof other[x] === "undefined") {
                        account.deleteOther(x);
                    } else {
                        account.setOther(x, other[x]);
                    }
                }
                var promises = [];
                if (newpwd != "") {
                    promises.push(account.setPassword(newpwd))
                }
                return Promise.all(promises)
            })
            .then(function () {
                return callPlugins("updateAccountPreSend", { "account": account, "name": name, "newPassword": newpwd, "other": other, "oldData": oldData });
            })
            .then(function () {
                return account.getEncrypted();
            })
            .then(function (encAccount) {
                return self.doPost("change", encAccount);
            });
    }
    deleteAccount(id) {
        var self = this;
        return self.doPost("delete", { index: id });
    }
    deleteFile(id) {
        return this.doPost("deletefile", { index: id });
    }
}

//mixin for pin handling
let PinHandling = (superclass) => class extends superclass {
    getDevice() {
        var self = this;
        var device = getCookie('device');
        if (device != null) {
            if (device <= 0)
                device = "";
            if (device != "")
                return Promise.resolve(device);
        }
        // Random string of length 9
        device = Math.random().toString(36).substring(2, 11);
        // check if this key is already used
        return self.doPost("getpinpk", { user: self.user, device: device, sig: '1' })
            .then(function (msg) {
                // unlikely since sig is random.
                return self.getDevice();
            })
            .catch(function (msg) {
                if (msg == 'No PIN available') {
                    //failure means this is not a duplicate, so everything is ok
                    setCookie('device', device);
                    return device;
                } else {
                    // Oops, we already have this device
                    return self.getDevice();
                }
            });
    }
    unSetPin(device) {
        return this.doPost("deletepin", { user: this.user, device: device });
    }
    setPin(pin) {
        var self = this;
        var device;
        var salt;
        return self.getDevice()
            .then(function (_device) {
                device = _device;
                salt = self.encryptionWrapper.generatePassphrase(100);

                return EncryptionWrapper.SgenerateKeyWithSalt(pin, salt);
            })
            .then(function (raw_signature) {
                return self.doPost("setpin", { user: self.user, device: device, sig: raw_signature });
            })
            .then(function (msg) {
                // no need for strong encryption since this key can not be verified.
                return EncryptionWrapper.WgenerateKeyWithSalt(pin + msg["pinpk"], salt);
            })
            .then(function (new_pin) {
                return self.encryptionWrapper.storePIN(device, salt, new_pin);
            });
    }
    delLocalPinStore() {
        return this.encryptionWrapper.deletePIN();
    }
    delPin() {
        var self = this;
        var device = getCookie('device');
        if ((device != null) && (device != "")) {
            return self.doPost("deletepin", { 'user': getCookie('username'), 'device': device })
                .then(function (msg) {
                    self.delLocalPinStore();
                });
        }
    }
    get pinActive() {
        var device = getCookie('device');
        return ((device != null) && (device != "")) && (this.usePin != 0);
    }
}

//mixin for localstorage
let LocalStorage = (superclass) => class extends superclass {
    getLocalStorage() {
        if (!sessionStorage.pwdsk) {
            return "";
        }
        var salt = self.encryptionWrapper.pwSalt;
        EncryptionWrapper.decryptCharUsingKey(sessionStorage.pwdsk, salt);
    }
    setLocalStorage(sk, confusion_key) {
        var salt = self.encryptionWrapper.pwSalt;
        sessionStorage.pwdsk = EncryptionWrapper.encryptCharUsingKey(sk, salt);
        sessionStorage.confusion_key = EncryptionWrapper.encryptCharUsingKey(confusion_key, salt);
    }
}

//Backend class
class AccountBackend extends mix(commonBackend).with(EventHandler, AuthenticatedSession, Timeout, PinHandling, Accounts) {
    constructor() {
        super();
        this.encryptionWrapper = null;
        this.cleanUp();
        this.events = ["logout"];
        this.initEvents();
        this.countdownInterval = null;
        this.sessionCountdownInterval = null;
    }
    cleanUp() {
        this.accounts = [];
        this.fields = [];
        this.fields_key = [];
    }
    loadAccounts() {
        var self = this;
        return callPlugins("preDataReady", {})
            .then(function (pluginResults) {
                return self.doPost("password", {});
            })
            .then(function (data) {
                callPlugins("dataReady", { "data": data });
                if (data["status"] == "error") {
                    throw (data["message"]);
                }
                self.receivedData = data;
                self.prepareData(data);
                self.resetTimeout();
                self.clearTimeout();
                self.initTimeout();
                return self.prepareCrypto(pmBackendConfig().globalSalt1, pmBackendConfig().globalSalt2, pmBackendConfig().defaultLetters);
            })
            .then(function () {
                return self.decryptAccounts(self.receivedData["accounts"]);
            })
            .then(function () {
                self.prepareFields(self.receivedData["fields"]);
                return self.prepareFiles(self.receivedData["fdata"]);
            })
            .catch(function (msg) {
                self.logout(msg);
                throw (msg);
            });
    }
    getTotpStatus() {
        var self = this;
        return self.doPost("totp", { action: "status" })
            .then(function (msg) {
                self.totpEnabled = msg["enabled"] == 1;
                return self.totpEnabled;
            });
    }

    enableTotp(secret, code) {
        var self = this;
        return self.doPost("totp", { action: "on", secret: secret, code: code })
            .then(function (msg) {
                self.totpEnabled = msg["enabled"] == 1;
                return self.totpEnabled;
            });
    }

    disableTotp() {
        var self = this;
        return self.doPost("totp", { action: "off" })
            .then(function (msg) {
                self.totpEnabled = false;
                return self.totpEnabled;
            });
    }
    prepareData(data) {
        this.default_timeout = pmBackendConfig().browserTimeout || data["default_timeout"];
        this.default_server_timeout = data["server_timeout"];
        this.file_enabled = data["file_enabled"];
        this.fields_allow_change = data["fields_allow_change"];
        this.server_timeout = this.default_server_timeout + Math.floor(Date.now() / 1000);
        this.default_length = pmBackendConfig().defaultPasswordLength || data["default_length"];
        this.user = data["user"];
        this.totpEnabled = data["totp_enabled"] == 1;
        this.loginInformation = data["loginInformation"];
    }
    prepareCrypto(jsSalt, pwSalt, default_letter) {
        var self = this;
        if (self.encryptionWrapper) {
            return Promise.resolve(self.encryptionWrapper);
        }
        try {
            return EncryptionWrapper.fromLocalStorage(jsSalt, pwSalt, default_letter)
                .then(function (encryptionWrapper) {
                    self.encryptionWrapper = encryptionWrapper;
                    return encryptionWrapper;
                });
        }
        catch (err) {
            throw ("Missing secretkey");
        }
    }
    decryptAccounts(accountData) {
        var self = this;
        var accountPromises = [];
        for (var i = 0; i < accountData.length; i++) {
            accountPromises.push(Account.fromEncrypted(self.encryptionWrapper, accountData[i])
                .then(function (account) {
                    callPlugins("readAccount", { "account": account });
                    return account;
                }));
        }
        return Promise.all(accountPromises)
            .then(function (accounts) {
                for (let account of accounts) {
                    self.accounts[account.index] = account;
                }
                callPlugins("accountsReady");
            });
    }
    prepareFields(fields) {
        fields = JSON.parse(fields);

        // fields_key sorted by position.
        this.fields_key = Object.keys(fields).sort(function (a, b) {
            if (('position' in fields[a]) && ('position' in fields[b]))
                return fields[a]['position'] - fields[b]['position'];
            if ('position' in fields[a]) return -1;
            if ('position' in fields[b]) return 1;
            return 0;
        });

        this.fields = fields;
    }
    prepareFiles(files) {
        var self = this;
        var filesPromises = [];
        for (let file of files) {
            filesPromises.push(self.accounts[file["index"]].addEncryptedFile(file["fname"], file["fkey"]));
        }
        return Promise.all(filesPromises);
    }

    uploadFile(id, name, payload) {
        var self = this;
        var fkey = self.encryptionWrapper.generatePassphrase(Math.floor(Math.random() * 6) + 32);
        let data = { "id": id };
        return EncryptionWrapper.WgenerateKeyWithSalt(self.encryptionWrapper.secretkey, name)
            .then(function (genkey) {
                return EncryptionWrapper.encryptCharUsingKey(fkey, genkey)
            })
            .then(function (encryptedKey) {
                data["fkey"] = encryptedKey;
                return EncryptionWrapper.encryptCharUsingKey(payload, fkey);
            })
            .then(function (filedata) {
                data["data"] = filedata;
                return self.encryptionWrapper.encryptChar(name);
            })
            .then(function (fname) {
                data["fname"] = fname;
                return self.doPost('uploadfile', data);
            });
    }
    downloadFile(id) {
        var self = this;
        var filedata;
        var file = {};
        return self.doPost('downloadfile', { id: id })
            .then(function (encfiledata) {
                filedata = encfiledata;
                file["name"] = self.accounts[id].file["name"];
                return EncryptionWrapper.WgenerateKeyWithSalt(self.encryptionWrapper.secretkey, file["name"]);
            })
            .then(function (genkey) {
                return EncryptionWrapper.decryptCharUsingKey(filedata["key"], genkey);
            })
            .then(function (fkey) {
                return EncryptionWrapper.decryptCharUsingKey(filedata["data"], fkey);
            })
            .then(function (data) {
                var typedata = data.substring(5, data.search(";"));
                data = data.substring(data.search(",") + 1);
                file["data"] = base64toBlob(data, typedata);
                return file;
            });
    }

    changePassword(oldpass, newpass) {
        var self = this;
        var newseckey;
        var postnewpass;
        var newconfkey;
        var promises = [];
        promises.push(self.encryptionWrapper.generateSecretKey(oldpass, self.user, false)
            .then(function (_old_sec_key) {
                if (self.encryptionWrapper.secretkey != _old_sec_key) {
                    throw ("Incorrect Old Password!");
                }
                return Promise.resolve(true);
            }));
        promises.push(self.encryptionWrapper.generateSecretKey(newpass, self.user, false)
            .then(function (_new_sec_key) {
                newseckey = _new_sec_key;
                var new_acc_promises = [];
                new_acc_promises.push(EncryptionWrapper.WgenerateKeyWithSalt(newseckey, self.user));
                new_acc_promises.push(EncryptionWrapper.WgenerateKeyWithSalt(newpass, newseckey));
                return Promise.all(new_acc_promises);
            }));
        return Promise.all(promises)
            .then(function (all_results) {
                postnewpass = all_results[1][0];
                newconfkey = all_results[1][1];
                return Promise.resolve(new EncryptionWrapper(newseckey,
                    self.encryptionWrapper.jsSalt,
                    self.encryptionWrapper.pwSalt,
                    self.encryptionWrapper.alphabet));
            })
            .then(function (newEncryptionWrapper) {
                newEncryptionWrapper._confkey = newconfkey;
                var promises = [];
                for (let account of self.accounts) {
                    if (!account)
                        continue;
                    promises.push(account.setEncryptionWrapper(newEncryptionWrapper)
                        .then(function (account) {
                            return account.getEncrypted(true);
                        }));
                }
                return Promise.all(promises)
            })
            .then(function (accounts) {
                //jetzt weiterleiten
                var accarray = [];
                for (let account of accounts) {
                    accarray[account["index"]] = account;
                }
                return self.doPost("changeuserpw", { newpass: postnewpass, accarray: JSON.stringify(accarray) });
            })
            .then(function () {
                return self.logout("Password changed, please relogin");
            });
    }

    backup(includeFiles, progress_callback) {
        var self = this;
        var cfg = pmBackendConfig();

        progress_callback = progress_callback || function () { };

        self.extendedTimeout();

        if (includeFiles) {
            includeFiles = "farray";
        } else {
            includeFiles = "a";
        }

        var keyIter = Math.max(0, Number(cfg.backupKeyIterations) || 0);

        var data;
        var backup = {};
        var key;

        return self.doPost("backup", {
            a: includeFiles
        })
            .then(function (msg) {
                data = msg;
                backup.VERSION = PASSWORD_MANAGER_VERSION;
                backup.JSsalt = cfg.globalSalt1;
                backup.PWsalt = cfg.globalSalt2;
                backup.KEYiter = keyIter;
                backup.ALPHABET = cfg.defaultLetters;
                backup.KEYsalt = self.encryptionWrapper.generatePassphrase(100);

                return EncryptionWrapper.SgenerateKeyWithSalt(
                    self.encryptionWrapper.secretkey,
                    backup.KEYsalt
                );
            })
            .then(async function (key) {
                for (var i = 0; i < backup.KEYiter; i++) {
                    progress_callback(Math.round(i * 90.0 / backup.KEYiter));
                    key = await EncryptionWrapper.SgenerateKeyWithSalt(key, backup.KEYsalt);
                }

                return key;
            })
            .then(function (_key) {
                key = _key;
                progress_callback(90);

                return EncryptionWrapper.encryptCharUsingKey(JSON.stringify(data.data), key);
            })
            .then(function (encData) {
                backup.data = encData;
                progress_callback(95);

                return EncryptionWrapper.encryptCharUsingKey(JSON.stringify(data.fdata), key);
            })
            .then(function (encfdata) {
                backup.fdata = encfdata;
                progress_callback(99);

                self.resetTimeout();
                backup.status = "success";

                return new Blob([JSON.stringify(backup)], {
                    type: "text/plain"
                });
            });
    }

    updateFields(fields) {
        var self = this;
        var p = fields.replace(/\r\n/g, '');
        p = p.replace(/\n/g, '');
        var j;
        try {
            j = JSON.parse(p);
        } catch (e) {
            return Promise.reject("parse");
        }
        for (var x in j) {
            if (x.substr(0, 1) == '_') {
                return Promise.reject("illegalFields");
            }
        }
        return self.doPost("changefields", { fields: JSON.stringify(j) });
    }

    get fileEnabled() {
        return this.file_enabled;
    }
    get allowFieldChange() {
        return this.fields_allow_change;
    }
}

class HistoryBackend extends mix(commonBackend).with(EventHandler, AuthenticatedSession, Timeout, PinHandling) {
    getHistory() {
        var self = this;
        return this.doPost("history", {})
            .then(function (msg) {
                self.user = msg["usr"];
                self.default_timeout = pmBackendConfig().browserTimeout || msg["default_timeout"];
                self.default_server_timeout = msg["server_timeout"];
                self.resetTimeout();
                self.clearTimeout();
                self.initTimeout();
                return msg;
            });
    }
}
class LogonBackend extends mix(commonBackend).with(EventHandler, PinHandling) {
    doPost(endpoint, data) {
        data = data || {};
        data["session_token"] = sessionStorage.session_token;

        return super.doPost(endpoint, data);
    }

    loadInfo() {
        var self = this;
        var cfg = pmBackendConfig();

        return this.doPost("info", {})
            .then(function (data) {
                self.encryptionWrapper = new EncryptionWrapper(
                    null,
                    cfg.globalSalt1,
                    cfg.globalSalt2,
                    cfg.defaultLetters
                );

                self.allowSignup = data["allowSignup"];
                self.version = PASSWORD_MANAGER_VERSION;
                self.banTime = data["banTime"];
                self.usePin = data["use_pin"];
                self.loggedIn = data["loggedIn"];

                /*
                 * These are frontend form validation settings.
                 * Do not fetch them from the backend.
                 */
                self.minPasswordLength = cfg.minPasswordLength;
                self.minNameLength = cfg.minNameLength;

                if (data["session_token"]) {
                    sessionStorage.session_token = data["session_token"];
                }

                if (!self.pinActive) {
                    self.delLocalPinStore();
                }

                return data;
            });
    }

    doPinLogin(pin) {
        var self = this;
        var user = getCookie("username");

        /*
         * SHA2-512 is faster.
         */
        return EncryptionWrapper.SgenerateKeyWithSalt(pin, localStorage.pinsalt)
            .then(function (post_signature) {
                return self.doPost("getpinpk", {
                    user: user,
                    device: getCookie("device"),
                    sig: post_signature
                });
            })
            .then(function (msg) {
                /*
                 * Weak hash here is OK since the correctness of this one cannot be verified.
                 */
                return EncryptionWrapper.WgenerateKeyWithSalt(pin + msg["pinpk"], localStorage.pinsalt);
            })
            .then(function (new_pin) {
                return self.encryptionWrapper.restoreFromPIN(user, new_pin);
            })
            .then(function (loginpwd) {
                return self.doPost("check", {
                    pwd: loginpwd,
                    user: user
                });
            })
            .catch(function (msg) {
                var text = String(msg);

                if (
                    msg === "No PIN available" ||
                    text.indexOf("2FA") !== -1 ||
                    text.indexOf("authenticator") !== -1
                ) {
                    self.delPin();
                }

                throw (msg);
            });
    }

    doLogin(user, password, totpcode) {
        var self = this;

        return self.encryptionWrapper.generateSecretKey(password, user)
            .then(function (_secretkey) {
                return EncryptionWrapper.WgenerateKeyWithSalt(_secretkey, user);
            })
            .then(function (login_sig) {
                return self.doPost("check", {
                    pwd: login_sig,
                    user: user,
                    totpcode: totpcode
                });
            })
            .then(function () {
                return self.encryptionWrapper.persistCredentialsFromPassword(user, password);
            });
    }

    doRegister(user, email, password1, password2) {
        var self = this;

        if (password1 !== password2) {
            return Promise.reject("PasswordMismatch");
        }

        if (password1.length < self.minPasswordLength) {
            return Promise.reject("PasswordLength");
        }

        if (!self.validEmail(email)) {
            return Promise.reject("EmailInvalid");
        }

        if (user.length < self.minNameLength || !self.validUserName(user)) {
            return Promise.reject("UserNameError");
        }

        return self.encryptionWrapper.generateSecretKey(password1, user)
            .then(function (secretkey) {
                return EncryptionWrapper.WgenerateKeyWithSalt(secretkey, user);
            })
            .then(function (login_sig) {
                return self.doPost("reg", {
                    email: email,
                    pwd: login_sig,
                    user: user
                });
            });
    }

    validEmail(aEmail) {
        return RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(aEmail);
    }

    validUserName(username) {
        return RegExp(/^[A-Za-z0-9\-_\.]+$/).test(username);
    }

}
