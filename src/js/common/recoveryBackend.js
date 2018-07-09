class RecoveryBackend {
    parseBackup(backupData, password) {
        var self = this;
        self.accounts = [];
        var json = JSON.parse(sanitize_json(backupData));
        if(json.status != "OK" && json.status != "success") {
            throw("INVALID BACKUP FILE");
        }
        var backupKey;
        self.encryptionWrapper = new EncryptionWrapper(null, json.JSsalt, json.PWsalt, json.ALPHABET);
        return self.generateBackupKeys(password)
            .then(function(dkey){
                backupKey = dkey;
                return EncryptionWrapper.decryptCharUsingKey(json.data, dkey);
            })
            .then(function(data) {
                return self.importAccounts(data);
            })
            .then(function(accounts){
                if (typeof json.fdata !== 'undefined')
                    return EncryptionWrapper.decryptCharUsingKey(json.fdata, backupKey)
                        .then(function(fdata) {
                            return self.importFiles(fdata);
                        });
                else
                    return;
            })
            .then(function() {
                return self.accounts;
            });
    }
    importAccounts(data) {
        var self = this;
        data = JSON.parse(data);
        let resultPromises = [];
        for (let acc in data) {
            data[acc][3] = acc;
            resultPromises.push(Account.fromEncrypted(self.encryptionWrapper,
                { index: data[acc][3], 
                    name: data[acc][0], 
                    kss: data[acc][1], 
                    additional:data[acc][2]}));
        }
        return Promise.all(resultPromises)
            .then(function(accounts) {
                for (let account of accounts) {
                    self.accounts.push(account);
                }
                return self.accounts;
            });
    }
    importFiles(data) {
        var self = this;
        //ToDo
        var filedata = JSON.parse(data);
        if (filedata.status === 'NO') 
            return;
        if (filedata.status != 'OK')
            throw('invalid status for encrypted files');
        let filePromises = [];
        for (let id in filedata["data"]) {
            var file = {"id": id};
            var thisFilePromise = self.encryptionWrapper.decryptChar(filedata["data"][id][0])
                .then(function(fname) {
                    file["name"] = fname;
                    return self.encryptionWrapper.decryptPassword(fname, filedata["data"][id][1]);
                })
                .then(function(fkey) {
                    file["key"] = fkey;
                    return EncryptionWrapper.decryptCharUsingKey(filedata["data"][id][2], fkey)
                })
                .then(function(fdata) {
                    file["data"] = fdata;
                    return file;
                });
            filePromises.push(thisFilePromise);
        }
        return Promise.all(filePromises)
            .then(function(files) {
                self.files = [];
                for (let file of files) {
                    self.files[file['id']] = file;
                }
                return self.files;
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
    getAccountsRaw() {
        var self = this;
        var promiseList = [];
        for (let account of self.accounts) {
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
        return promiseList;
    }
    exportCSV() {
        var self = this;
        return Promise.all(self.getAccountsRaw())
            .then(function(results) {
                var result = [];
                for (let account of results) {
                    let tmp = {};
                    account = account['data'];
                    tmp['name'] = account['account'];
                    tmp['password'] = account['password'];
                    let other = JSON.parse(account['other']);
                    for (let item in other) {
                        tmp[item] = other[item];
                    }
                    result.push(tmp);
                }
                var csv = $.csv.fromObjects(result);
                return new Blob([csv], {type: "text/plain;charset=utf-8"});
            });
    }
    exportRaw() {
        var self = this;
        return Promise.all(self.getAccountsRaw())
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
