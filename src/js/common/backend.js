// helpers for javascript mixins-pattern
let mix = (superclass) => new MixinBuilder(superclass);
class MixinBuilder {  
  constructor(superclass) {
    this.superclass = superclass;
  }

  with(...mixins) { 
    return mixins.reduce((c, mixin) => mixin(c), this.superclass);
  }
}

//Base Class for Backends
class commonBackend {
    doPost(endpoint, data) {
        data = data || {};
        var endpointDomain = this.domain || "";
        return $.post(endpointDomain + "rest/" + endpoint + ".php", data)
            .then(function(msg) {
                return commonBackend.checkResult(msg);
            });
    }
    get sessionToken() {
        if (this._sessionToken) {
            return this._sessionToken;
        }
        return localStorage.session_token;
    }
    static checkResult(msg) {
        if(msg["status"] != "success") {
            throw(msg["message"]);
        }
        return msg;
    }
};

//mixin for events
let EventHandler = (superclass) => class extends superclass {
    initEvents() {
        this.eventSubscribers = {};
        for (let event of this.events){
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
        //Todo raise event
        sessionStorage.clear();
        return self.doPost("logout", {})
            .then(function(){
                self.callEvent("logout", {reason: reason});
                return reason;
            });
    }
    untrustAndLogout() {
        var self = this;
        //Todo raise event
        localStorage.clear();
        var promises = [];
        if (getCookie('device') != "") {
            promises.push(self.doPost("deletepin", {user:getCookie('username'), device:getCookie('device')}));
        }
        return Promise.all(promises)
            .then(function(){
                deleteCookie('device');
                deleteCookie('username');
                return self.logout();
            });
    }
}

//mixin for timeout function
let Timeout = (superclass) => class extends superclass {
    doPost(endpoint, data) {
        this.resetTimeout();
        return super.doPost(endpoint, data);
    }
    //Timout
    resetTimeout() {
        let newTimeout = this.default_timeout + Math.floor(Date.now() / 1000);
        if (this.timeout < newTimeout) {
            this.timeout = newTimeout;
        }
    }
    countdown() {
        if (this.isTimeout) {
            this.logout("Logged out due to inactivity");
        }
    }
    sessionCountdown() {
        var ck = getCookie("ServerRenew");
        if(ck == '1') // Reset timer
            this.server_timeout  = this.default_server_timeout+Math.floor(Date.now() / 1000);
        if(ck == "-1" || this.server_timeout < Math.floor(Date.now() / 1000)) // Timer has expired
            this.logout("Session timed out");
        setCookie("ServerRenew", '0');// nothing happened
    }
    initTimeout() {
        setInterval(this.countdown.bind(this), 5000);
        setInterval(this.sessionCountdown.bind(this), 5000);
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
    addAccount(name, pwd, other) {
        var self = this;
        if (name == "") {
            return Promise.reject("Account name can't be empty");
        }
        let account = new Account(null, name, "");
        return account.setEncryptionWrapper(self.encryptionWrapper)
            .then(function(){
                return account.setPassword(pwd);
            })
            .then(function(){
                for (let key in other) {
                    account.setOther(key, other[key]);
                }
                callPlugins("addAccountPreSend", {"account":account, "name":name, "password":pwd, "other":other});
                return account.getEncrypted()
            })
            .then(function(encAccount) {
                return self.doPost("insert", encAccount);
            });
    }
    updateAccount(id, name, newpwd, other) {
        var self = this;
        if (name == "") {
            return Promise.reject("Account name can't be empty");
        }
        var account = this.accounts[id];
        return account.setAccountName(name)
            .then(function(){;
                account.clearVisibleOther();
                for (let x in other) {
                    account.setOther(x, other[x]);
                }
                var promises = [];
                if (newpwd != "") {
                    promises.push(account.setPassword(newpwd))
                }
                return Promise.all(promises)
            })
            .then(function(){
                callPlugins("updateAccountPreSend", {"account":account, "name":name, "newPassword":newpwd, "other":other});
                return account.getEncrypted();
            })
            .then(function(encAccount){
                return self.doPost("change", encAccount);
            });
    }
    deleteAccount(id) {
        var self = this;
        return self.doPost("delete", {index: id});
    }
}

//mixin for pin handling
let PinHandling = (superclass) => class extends superclass {
    getDevice() {
        var self = this;
        var device = getCookie('device');
        if (device <= 0)
            device = "";
        if (device != "")
            return Promise.resolve(device);
        device =  self.encryptionWrapper.generatePassphrase(9);
        // check if this key is already used
        return self.doPost("getpinpk", { user:self.user, device: device, sig:'1'})
            .then(function(msg) {
                // success means a duplicate exists, so we do this all again
                return self.getDevice();
            })
            .catch(function(msg) {
                //failure means this is not a duplicate, so everything is ok
                setCookie('device', device);
                return device;
            });
    }
    unSetPin(device) {
        return this.doPost("deletepin", {user:this.user, device:device});
    }
    setPin(pin) {
        var self = this;
        var device;
        var salt;
        return self.getDevice()
            .then(function(_device) {
                device = _device;
                salt = self.encryptionWrapper.generatePassphrase(500);
                return self.doPost("setpin" , {user: self.user, device: device, sig: String(CryptoJS.SHA512(pin + salt))});
            })
            .then(function(msg) {
                return self.encryptionWrapper.storePIN(device, salt, pin + msg["pinpk"]);
            });
    }
    delLocalPinStore() {
        return this.encryptionWrapper.deletePIN();
    }
    delPin() {
        var self = this;
        if(getCookie('device') != "") {
            return self.doPost("deletepin", {user:getCookie('username'), device:getCookie('device')})
                .then(function(msg){
                    self.delLocalPinStore();
                });
        }
    }
}

//mixin for localstorage
//todo move to encryptionWrapper
let LocalStorage = (superclass) => class extends superclass {
    getLocalStorage() {
        if(!sessionStorage.pwdsk) {
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
    }
    cleanUp(){
        this.accounts = [];
        this.fields = [];
    }
    loadAccounts() {
        var self = this;
        return self.doPost("password", {})
            .then(function(data){
                callPlugins("dataReady", {"data":data});
                if (data["status"] == "error") {
                    throw(data["message"]);
                }
                self.receivedData = data;
                self.prepareData(data);
                self.resetTimeout();
                self.initTimeout();
                return self.prepareCrypto(data["global_salt_1"], data["global_salt_2"], data["default_letter_used"]);
            })
            .then(function(){
                return self.decryptAccounts(self.receivedData["accounts"]);
            })
            .then(function(){
                self.prepareFields(self.receivedData["fields"]);
                return self.prepareFiles(self.receivedData["fdata"]);
            })
            .catch(function(msg) {
                self.logout(msg);
                throw(msg);
            });
    }
    prepareData(data) {
        this.default_timeout = data["default_timeout"];
        this.default_server_timeout = data["server_timeout"];
        this.file_enabled = data["file_enabled"];
        this.fields_allow_change = data["fields_allow_change"];
        this.server_timeout = this.default_server_timeout + Math.floor(Date.now() / 1000);
        this.default_length = data["default_length"];
        this.user = data["user"];
        this.loginInformation =  data["loginInformation"];
    }
    prepareCrypto(jsSalt, pwSalt, default_letter) {
        var self = this;
        if (self.encryptionWrapper) {
            return Promise.resolve(self.encryptionWrapper);
        }
        try {
            return EncryptionWrapper.fromLocalStorage(jsSalt, pwSalt, default_letter)
                .then(function(encryptionWrapper) {
                    self.encryptionWrapper = encryptionWrapper;
                    return encryptionWrapper;
                });
        }
        catch (err) {
            throw("Missing secretkey");
        }
    }
    decryptAccounts(accountData) {
        var self = this;
        var accountPromises = [];
        for(var i = 0; i < accountData.length; i++) {
            accountPromises.push(Account.fromEncrypted(self.encryptionWrapper, accountData[i])
                .then(function(account) {
                    callPlugins("readAccount", {"account": account});
                    return account;
                }));
        }
        return Promise.all(accountPromises)
            .then(function(accounts){
                for (let account of accounts) {
                    self.accounts[account.index] = account;
                }
                callPlugins("accountsReady");
            });
    }
    prepareFields(fields) {
        this.fields = $.parseJSON(fields);
        for (let x in this.fields) {
            this.fields[x]["count"] = 0;
        }
        for (var index in this.accounts) {
            let others = this.accounts[index].availableOthers;
            for (let x of others) {
                if ( (this.accounts[index].getOther[x] != "") && (x in this.fields) ) {
                    this.fields[x]["count"] += 1;
                }
            }
        }
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
        var fkey = self.encryptionWrapper.generatePassphrase(Math.floor(Math.random() * 18) + 19);
        var data = {
            id:id, 
            fkey:self.encryptionWrapper.encryptPassword(name, fkey),
            data: EncryptionWrapper.encryptCharUsingKey(payload, fkey),
            fname: self.encryptionWrapper.encryptChar(name)
        };

        return self.doPost('uploadfile', data);
    }
    downloadFile(id) {
        var self = this;
        return self.doPost('downloadfile', {id:id})
            .then(function(filedata) {
                var file = {};
                file["name"] = self.accounts[id].file["name"];
                var fkey = self.encryptionWrapper.decryptPassword(file["name"], filedata["key"]);
                var data = EncryptionWrapper.decryptCharUsingKey(filedata["data"], fkey);
                var typedata = data.substring(5, data.search(";"));
                data = data.substring(data.search(",") + 1);
                file["data"] = base64toBlob(data, typedata);
                return file;
            })
        ;
    }
    
    changePassword(oldpass, newpass) {
        var self = this;
        var login_sig;
        var postnewpass;
        var newconfkey;
        return self.encryptionWrapper.generateSecretKey(oldpass, false)
            .then(function(old_login_sig) {
                if(self.encryptionWrapper.secretkey != String(CryptoJS.SHA512(old_login_sig + self.encryptionWrapper.pwSalt))) {
                    throw("Incorrect Old Password!");
                }
                return self.encryptionWrapper.generateSecretKey(newpass, false)
            })
            .then(function(key){
                login_sig = key;
                return self.encryptionWrapper.generateKey(login_sig);
            })
            .then(function(_postnewpass) {
                postnewpass = _postnewpass;
                return self.encryptionWrapper.generateKey(String(CryptoJS.SHA512(newpass + login_sig)));
            })
            .then(function(_newconfkey) {
                newconfkey = _newconfkey;
                return EncryptionWrapper.fromPassword(newpass, self.encryptionWrapper.jsSalt, self.encryptionWrapper.pwSalt, self.encryptionWrapper.alphabet, login_sig)
            })
            .then(function(newEncryptionWrapper) {
                newEncryptionWrapper._confkey = newconfkey;
                var promises = [];
                for (let account of self.accounts) {
                    if (!account)
                        continue;
                    promises.push(account.setEncryptionWrapper(newEncryptionWrapper)
                        .then(function(account){
                            return account.getEncrypted(true);
                        }));
                }
                return Promise.all(promises)
            })
            .then(function(accounts) {
                //jetzt weiterleiten
                var accarray= [];
                for (let account of accounts) {
                    accarray[account["index"]] = account;
                }
                return self.doPost("changeuserpw", {newpass:String(CryptoJS.SHA512(postnewpass + self.user)), accarray:JSON.stringify(accarray)});
            })
            .then(function(){
                return self.logout("Password changed, please relogin");
            });
    }

    backup(includeFiles) {
        var self = this;
        self.extendedTimeout();
        if (includeFiles)
            includeFiles = "farray";
        else
            includeFiles = "a";
        var data;
        var backup;
        var key;
        return self.doPost("backup", { a: includeFiles}) 
            .then(function(msg) {
                data = msg;
                backup = data;
                return self.encryptionWrapper.multiGenerateKey(self.encryptionWrapper.secretkey, 32);
            })
            .then(function(_key) {
                key = _key;
                return EncryptionWrapper.encryptCharUsingKey(JSON.stringify(data.data), key);
            })
            .then(function(encData) {
                backup.data = encData;
                return EncryptionWrapper.encryptCharUsingKey(JSON.stringify(data.fdata), key);
            })
            .then(function(encfdata) {
                backup.fdata = encfdata;
                return new Blob([JSON.stringify(backup)], {type: "text/plain;charset=utf-8"});
            });
    }

    updateFields(fields) {
        var self = this;
        var p = fields.replace(/\r\n/g,'');
        p = p.replace(/\n/g,'');
        var j;
        try {
            j = JSON.parse(p);
        } catch (e) {
            return Promise.reject("parse");
        }
        for (var x in j) {
            if (x.substr(0,1) == '_') {
                return Promise.reject("illegalFields");
            }
        }
        return self.doPost("changefields", {fields: JSON.stringify(j)});
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
            .then(function(msg){
                self.user = msg["usr"];
                self.default_timeout = msg["default_timeout"];
                self.default_server_timeout = msg["server_timeout"];
                self.initTimeout();
                return msg;
            });
    }
}
class LogonBackend extends mix(commonBackend).with(EventHandler, PinHandling) {
    doPost(endpoint, data) {
        data = data || {};
        data["session_token"] = localStorage.session_token;
        return super.doPost(endpoint, data);
    }
    loadInfo() {
        var self = this;
        return this.doPost("info", {})
            .then(function(data){
                self.encryptionWrapper = new EncryptionWrapper(null, data["global_salt_1"], data["global_salt_2"], data["default_letter_used"]);
                self.allowSignup = data["allowSignup"];
                self.hostdomain = data["hostdomain"];
                self.version = data["version"];
                self.banTime = data["banTime"];
                self.randomLoginStamp = data["random_login_stamp"];
                self.usePin = data["use_pin"];
                self.loggedIn = data["loggedIn"];
                self.minPasswordLength = data["minPasswordLength"];
                self.minNameLength = data["minNameLength"];

                localStorage.session_token = data["session_token"];

                if (!self.checkHostdomain) {
                    throw ('Hostdomain mismatch. Please check your config file.');
                }

                if (!self.pinActive) {
                    self.delLocalPinStore();
                }
                return data;
            });
    }
    doPinLogin(pin) {
        var self = this;
        var user = getCookie('username');
        var sig = String(CryptoJS.SHA512(String(CryptoJS.SHA512(pin + localStorage.pinsalt)) + self.randomLoginStamp));
        return self.doPost('getpinpk', {user:user, device:getCookie('device'), sig:sig})
            .then(function(msg) {
                return self.encryptionWrapper.restoreFromPIN(user, pin + msg["pinpk"]);
            })
            .then(function(loginpwd) {
                return self.doPost('check', {pwd: loginpwd, user:user});
            })
            .catch(function(msg) {
                //Todo clearpwdstore
                if (msg == "No PIN available") {
                    self.delLocalPinStore();
                }
                throw(msg);
            });

    }
    doLogin(user, password) {
        var self = this;
        var secretkey = '';
        return self.encryptionWrapper.generateSecretKey(password)
            .then(function(_secretkey){
                secretkey = _secretkey;
                return self.encryptionWrapper.generateKey(secretkey);
            })
            .then(function(login_sig) {
                return self.doPost('check', {pwd:String(CryptoJS.SHA512(login_sig + user)), user: user});
            })
            .then(function(confkey) {
                return self.encryptionWrapper.persistCredentialsFromPassword(user, password);
            });
    }
    doRegister(user, email, password1, password2) {
        var self = this;
        if (password1 != password2) {
            return Promise.reject("PasswordMismatch");
        }
        if (password1.length < self.minPasswordLength) {
            return Promise.reject("PasswordLength");
        }
        if (!self.validEmail(email)) {
            return Promise.reject("EmailInvalid");
        }
        if (user.length < self.minNameLength) {
            return Promise.reject("UserLength");
        }
        return self.encryptionWrapper.generateSecretKey(password1)
            .then(function(secretkey){
                return self.encryptionWrapper.generateKey(secretkey);
            })
            .then(function(login_sig) {
                return self.doPost('reg' , {email: email, pwd:String(CryptoJS.SHA512(login_sig + user)), user: user});
            });
    }
    validEmail(aEmail) {
        var bValidate = RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(aEmail);
        if (bValidate) {
            return true;
        }
        else 
            return false;
    }
    checkHostdomain() {
        var full = location.protocol + '//' + location.hostname;
        return this.hostdomain.toLowerCase().startsWith(full.toLowerCase());
    }
    get pinActive() {
        return (getCookie('device') != "") && (this.usePin == 1);
    }
}
