class Backend {
    constructor() {
        this.cleanUp();
    }
    function cleanUp(){
        this.accounts = [];
        this.fields = [];
    }
    function load() {
        return $.post("rest/password.php", {})
            .then(function(data){
                callPlugins("dataReady", {"data":data});
                if (data["status"] == "error") {
                    throw(data["message"]);
                }
                this.receivedData = data;
                this.prepareData(data);
                return this.prepareCrypto(data["global_salt_2"], data["default_letter_used"])
            })
            .then(function(){
                return this.decryptAccounts(this.receivedData["accounts"]);
            })
            .then(function(){
                this.prepareFields(this.receivedData["fields"]);
                return this.prepareFiles(this.receivedData["fdata"]);
            });
    }
    function prepareData(data) {
        this.default_timeout = data["default_timeout"];
        this.default_server_timeout = data["server_timeout"];		
        this.file_enabled = data["file_enabled"];		
        this.fields_allow_change = data["fields_allow_change"];
        this.server_timeout = default_server_timeout + Math.floor(Date.now() / 1000);		
        this.timeout = default_timeout + Math.floor(Date.now() / 1000);		
        this.default_length = data["default_length"];		
        this.salt1 = data["global_salt_1"]; //Only needed for changing the logon password
        this.user = data["user"];		
        this.loginInformation =  data["loginInformation"];
    }
    function prepareCrypto(salt, default_letter) {
        try {
            this.encryptionWrapper = EncryptionWrapper.fromLocalStorage(salt, default_letter);
        }
        catch (err) {
            throw("Missing secretkey");
        }
    }
    function decryptAccounts(accountData) {
        var accountPromises = [];
        for(var i = 0; i < accountData.length; i++) {
            accountPromises.push(Account.fromEncrypted(this.encryptionWrapper, accountData[i])
                .then(function(newAccount) {
                    account = newAccount.account;
                    callPlugins("readAccount", {"account": account});
                    return account;
                }));
        }
        return Promise.All(accountPromises)
            .then(function(accounts){
                for (account of accounts) {
                    this.accounts[account.index] = account;:w
                }
                callPlugins("accountsReady");
            });
    }
    function prepareFields(fields) {
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
    function prepareFiles(files) {
        var filesPromises = [];
        for (file of files) {
            filesPromises.push(this.accounts[file["index"]].addEncryptedFile(file["fname"], file["fkey"]));
        }
        return Promise.All(filesPromises);
    }
    function addAccount(name, pwd, other) {
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
    function updateAccount(id, name, newpwd, other) {
        //do check for empty name
        //do check for new password
    }
    function deleteAccount(id) {
    }
    function getFiles() {
    }
    function setPin(pin) {
    }
    function updateFields(fields) {
    }
    get fileEnabled() {
        return this.file_enabled;
    }
    get allowFieldChange() {
        return this.fields_allow_change;
    }
}
