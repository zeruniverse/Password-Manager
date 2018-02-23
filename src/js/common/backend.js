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
        return $.post("rest/" + endpoint + ".php", data)
            .then(function(msg) {
                return commonBackend.checkResult(msg);
            });
    }
    get sessionToken() {
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
        if (getcookie('device') != "") {
            promises.push(self.doPost("deletepin", {user:getcookie('username'), device:getcookie('device')}));
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
            server_timeout  = backend.default_server_timeout+Math.floor(Date.now() / 1000);
        if(ck == "-1" || server_timeout < Math.floor(Date.now() / 1000)) // Timer has expired
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
        account.encryptionWrapper = self.encryptionWrapper;
        account.password = pwd;

        if(!("_system_passwordLastChangeTime" in other))
            other["_system_passwordLastChangeTime"] = Math.floor(Date.now() / 1000);
        for (let key in other) {
            account.setOther(key, other[key]);
        }
        return account.getEncrypted()
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
        account.accountName = name;
        account.clearVisibleOther();
        for (let x in other) {
            account.setOther(x, other[x]);
        }
        var promises = [];
        if (newpwd != "")
            promises.push(account.setPassword(newpwd))
        return Promise.all(promises)
            .then(function(){
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
            .then(function(device) {
                salt = self.encryptionWrapper.generatePassphrase(500);
                return self.doPost("setpin" , {user:self.user, device: device, sig:String(CryptoJS.SHA512(pin+salt))});
            })
            .then(function(msg) {
                setPINstore(device, salt, EncryptionWrapper.encryptCharUsingKey(getpwdstore(self.encryptionWrapper.pwSalt), pin + msg["pinpk"]), EncryptionWrapper.encryptCharUsingKey(self.encryptionWrapper.confkey, pin + msg["pinpk"]));
            });
    }

}

//Backend class
class AccountBackend extends mix(commonBackend).with(EventHandler, AuthenticatedSession, Timeout, PinHandling, Accounts) {
    constructor() {
        super();
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
                return self.prepareCrypto(data["global_salt_2"], data["default_letter_used"]);
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
        //Todo necessary? see checksessionalive
        this.server_timeout = this.default_server_timeout + Math.floor(Date.now() / 1000);
        this.default_length = data["default_length"];
        this.salt1 = data["global_salt_1"]; //Only needed for changing the logon password
        this.user = data["user"];
        this.loginInformation =  data["loginInformation"];
    }
    prepareCrypto(salt, default_letter) {
        try {
            this.encryptionWrapper = EncryptionWrapper.fromLocalStorage(salt, default_letter);
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
                return msg;
            });
    }
    //ToDo Timeout
}
