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
                    data[acc][3] = i;
                    resultPromises.push(Account.fromEncrypted(self.encryptionWrapper,
                        { index: data[acc][3], 
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
                return self.encryptionWrapper.generateKey(String(CryptoJS.SHA512(password + preKey)))
            })
            .then(function(confkey) {
                self.encryptionWrapper._confkey = confkey;
                return self.encryptionWrapper.multiGenerateKey(self.encryptionWrapper.secretkey, 32);
            });
    }
    exportCSV() {
    }
    exportRaw() {
        var self = this;
        var promiseList = [];
        for (let account of self.accounts)
        {
            var nextAccountPromise = account.getPassword()
                .then(function(password) {
                    return { 
                        'index': account.index,
                        'data': {
                            'account': account.accountName,
                            'password': password,
                            'other': account.getOtherJSON()
                        }
                    };
                });
            promiseList.push(nextAccountPromise);
        }
        return Promise.all(promiseList)
            .then(function(results) {
                var result = { };
                result.status = "RAW_OK";
                result.data = { };
                for (let account of results) {
                    result.data[account.index] = account['data'];
                }
                return JSON.stringify(result);
            });
        //Todo files
        //if(has_file == 1 && x in fname_array) {
        //    result.data[x].fname = fname_array[x];
        //    result.data[x].filedata = fdata_array[x];
        //}
    }
}
