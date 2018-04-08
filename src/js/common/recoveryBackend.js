class RecoveryBackend {
    parseBackup(backupData, password) {
        var self = this;
        self.accounts = [];
        var json = JSON.parse(sanitize_json(backupData));
        var preKey;
        if(json.status!="OK" && json.status!="success") {
            throw("INVALID BACKUP FILE");
        }
        self.encryptionWrapper = new EncryptionWrapper(null, json.JSsalt, json.PWsalt, json.ALPHABET);
        return self.generateBackupKeys(password)
            .then(function(dkey){
                return EncryptionWrapper.decryptCharUsingKey(json.data, dkey);
            })
            .then(function(data) {
                data = JSON.parse(data);
                var i = 0;
                let resultPromises = [];
                for (let acc in data) {
                    resultPromises.push(Account.fromEncrypted(self.encryptionWrapper,
                                { index: i, 
                                    name:data[acc][0], 
                                    kss:data[acc][1], 
                                    additional:data[acc][2]}));
                    i += 1;
                }
                return Promise.all(resultPromises);
                //ToDo:Files
            })
            .then(function(accounts){
                for (let account of accounts) {
                    self.accounts.push(account);
                }
                return self.accounts;
            });
    }
    generateBackupKeys(password) {
        var self = this;
        var preKey;
        return self.encryptionWrapper.generateSecretKey(password)
            .then(function(key) {
                preKey = key;
                self.encryptionWrapper.secretkey = String(CryptoJS.SHA512(key + self.encryptionWrapper.pwSalt));
                return self.encryptionWrapper.generateKey(password + preKey);
            })
            .then(function(confkey) {
                self.encryptionWrapper._confkey = confkey;
                return self.encryptionWrapper.multiGenerateKey(self.encryptionWrapper.secretkey, 32);
            });
    }
}
