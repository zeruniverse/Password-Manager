class Backend {
    constructor() {
        this.cleanUp();
    }
    doPost(endpoint, data) {
        data["session_token"] = localStorage.session_token;
        return $.post("rest/" + endpoint + ".php", data)
            .then(function(msg) {
                return Backend.checkResult(msg);
            });
    }
    cleanUp(){
        this.accounts = [];
        this.fields = [];
    }
    load() {
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
                return self.prepareCrypto(data["global_salt_2"], data["default_letter_used"]);
            })
            .then(function(){
                return self.decryptAccounts(self.receivedData["accounts"]);
            })
            .then(function(){
                self.prepareFields(self.receivedData["fields"]);
                return self.prepareFiles(self.receivedData["fdata"]);
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

    setPin(pin) {
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

    //Timout
    resetTimeout() {
        this.timeout = this.defaut_timeout + Math.floor(Date.now() / 1000);
    }
    // extended timeout for actions that take a long time
    extendedTimeout() {
        this.timeout = 1000000 + Math.floor(Date.now() / 1000);
    }
    get isTimeout() {
        return this.timeout < Math.floor(Date.now() / 1000);
    }

    static checkResult(msg) {
        if(msg["status"] != "success") {
            throw(msg["message"]);
        }
        return msg;
    }
    getHistory() {
        return this.doPost("history", {});
    }
}
