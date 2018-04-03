class RecoveryBackend {
    parseBackup(backupData, password) {
        var self = this;
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
                //ToDo Files
                console.log(data);
            });
    }
    generateBackupKeys(password) {
        var self = this;
        var preKey;
        return self.encryptionWrapper.generateSecretKey(password)
            .then(function(key) {
                preKey = key;
                self.secretKey = String(CryptoJS.SHA512(key + self.encryptionWrapper.pwSalt));
                return self.encryptionWrapper.generateKey(password + preKey);
            })
            .then(function(_confKey) {
                self.confKey = _confKey;
                return self.encryptionWrapper.multiGenerateKey(self.secretKey, 32);
            });
    }

}
