class Account {
    constructor(index, name, enpassword) {
        this.index = index;
        this.name = name;
        this.enpassword = enpassword;
        this.other = {};
        this.mFile = null;
        this.mEncryptionWrapper = null;
    }
    // reads the account from a encrypted dict
    static fromEncrypted(encryptionWrapper, encryptedAccount) {
        return encryptionWrapper.decryptChar(encryptedAccount["name"])
            .then(function(accountName) {
                let account = new Account(encryptedAccount["index"], accountName, encryptedAccount["kss"]);
                return account.setEncryptionWrapper(encryptionWrapper);
            })
            .then(function(account){
                let resultPromise;
                if (encryptedAccount["additional"] != "") {
                    //decrypt and extract json
                    resultPromise = encryptionWrapper.decryptChar(encryptedAccount["additional"])
                        .then(function(additional) {
                            let data = JSON.parse(additional);
                            for (var x in data)
                                account.setOther(x, data[x]);
                            return account;
                        });
                }
                else {
                    resultPromise = Promise.resolve(account);
                }
                return resultPromise;
            });
    }

    setEncryptionWrapper(wrapper) {
        var self = this;
        if (self.encryptionWrapper != null) {
            var decryptedPassword;
            var decryptedFileKey;
            return self.getPassword()
                .then(function(password) {
                    decryptedPassword = password;
                    if (self.hasFile()) {
                        return self.getFileKey();
                    }
                    else {
                        return "";
                    }
                })
                .then(function(fileKey) {
                    decryptedFileKey = fileKey;
                    return;
                })
                .then(function(){
                    self.mEncryptionWrapper = wrapper;
                    return self.setPassword(decryptedPassword);
                })
                .then(function() {
                    if (decryptedFileKey != '') {
                        return self.setFileKey(decryptedFileKey);
                    }
                    else {
                        return;
                    }
                })
                .then(function(){
                    return self;
                });
        }
        else {
            self.mEncryptionWrapper = wrapper;
            return Promise.resolve(self);
        }
    }
    get encryptionWrapper() {
        return this.mEncryptionWrapper;
    }

    getEncrypted(withFile){
        withFile = withFile || false;
        var self = this;
        let encryptedResult = { "kss":self.enpassword };
        if (self.index != null)
            encryptedResult["index"] = self.index;
        return self.encryptionWrapper.encryptChar(self.name)
            .then(function(enName) {
                encryptedResult["name"] = enName;
                let other = JSON.stringify(self.other);
                return self.encryptionWrapper.encryptChar(other);
            })
            .then(function(enOther) {
                encryptedResult["other"] = enOther;
                if (withFile && self.hasFile()) {
                    encryptedResult["fk"] = self.file.key;
                    return self.encryptionWrapper.encryptChar(self.file.name)
                        .then(function(fname) {
                            encryptedResult["fname"] = fname;
                        });
                }
            })
            .then(function(){
                return encryptedResult;
            });
    }

    get accountName() {
        return this.name;
    }
    setAccountName(name) {
        var self = this;
        //reencrypt password
        return self.getPassword()
            .then(function(password){;
                self.name = name;
                return self.setPassword(password);
            });
    }
    getPassword(){
        return this.encryptionWrapper.decryptPassword(this.name, this.enpassword);
    }
    setPassword(password){
        var self = this;
        return this.encryptionWrapper.encryptPassword(this.name, password)
            .then(function(enPass){
                self.enpassword = enPass;
                return enPass;
            });
    }
    clearOther() {
        this.other = {};
    }
    clearVisibleOther() {
        for (let item in this.other) {
            if (item.substring(0,1) != "_") {
                delete this.other[item];
            }
        }
    }
    setOther(name, value) {
        this.other[name] = value;
    }
    getOther(name) {
        return this.other[name];
    }
    getOtherJSON() {
        return JSON.stringify(this.other);
    }
    addEncryptedFile(name, fkey) {
        var self = this;
        self.file = { "name":"", "key": fkey };
        return self.encryptionWrapper.decryptChar(name)
            .then(function(decryptedName) {
                self.file.name = decryptedName;
                return self.file;
            });
    }
    hasFile() {
        return 'file' in this;
    }
    getFileKey(){
        return EncryptionWrapper.WgenerateKeyWithSalt(this.encryptionWrapper.secretkey, this.file.name)
            .then(function(genkey){
                return EncryptionWrapper.decryptCharUsingKey(this.file.key, genkey);
            });
    }
    setFileKey(key){
        return EncryptionWrapper.WgenerateKeyWithSalt(this.encryptionWrapper.secretkey, this.file.name)
            .then(function(genkey){
                return EncryptionWrapper.encryptCharUsingKey(key, genkey);
            })
            .then(function(enKey){
                this.file.key = enKey;
                return enKey;
            });
    }
}
