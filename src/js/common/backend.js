class Backend {
    constructor() {
        this.cleanUp = this.cleanUp.bind(this);
        this.load = this.load.bind(this);
        this.prepareData = this.prepareData.bind(this);
        this.prepareCrypto = this.prepareCrypto.bind(this);
        this.decryptAccounts = this.decryptAccounts.bind(this);
        this.cleanUp();
    }
    cleanUp(){
        this.accounts = [];
        this.fields = [];
    }
    load() {
        var self = this;
        return $.post("rest/password.php", {})
            .then(function(data){
                callPlugins("dataReady", {"data":data});
                if (data["status"] == "error") {
                    throw(data["message"]);
                }
                self.receivedData = data;
                self.prepareData(data);
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
        this.server_timeout = this.default_server_timeout + Math.floor(Date.now() / 1000);		
        this.timeout = self.default_timeout + Math.floor(Date.now() / 1000);		
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
                if (typeof(accounts) == "undefined") {
                    return;
                }
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
        for (file of files) {
            filesPromises.push(self.accounts[file["index"]].addEncryptedFile(file["fname"], file["fkey"]));
        }
        return Promise.all(filesPromises);
    }
    addAccount(name, pwd, other) {
        if (name == "") {
            return Promise.reject("Account name can't be empty");
        }
        let account = new Account(null, name, "");
        account.encryptionWrapper = this.encryptionWrapper;
        account.password = pwd;

        if(!("_system_passwordLastChangeTime" in other)) 
            other["_system_passwordLastChangeTime"] = Math.floor(Date.now() / 1000);
        for (let key in other) {
            account.setOther(key, other[key]);
        }
        return account.getEncrypted()
            .then(function(encAccount) {
                return $.post("rest/insert.php", encAccount);
            })
            .then(function(result) {
                if (result["status"] != "success") {
                    throw(results["message"]);
                }
                return result;
            });
    }
    updateAccount(id, name, newpwd, other) {
        //do check for empty name
        //do check for new password
    }
    deleteAccount(id) {
    }
    getFiles() {
    }
    setPin(pin) {
    }
    updateFields(fields) {
    }
    get fileEnabled() {
        return this.file_enabled;
    }
    get allowFieldChange() {
        return this.fields_allow_change;
    }
}
