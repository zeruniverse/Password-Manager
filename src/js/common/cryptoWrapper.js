class EncryptionWrapper {
    constructor(secretkey, pwSalt, alphabet) {
        this.secretkey = secretkey;
        this.pwSalt = pwSalt;
        this.alphabet = alphabet;
    }
    static fromLocalStorage(salt, alphabet) {
        return EncryptionWrapper.getPwdStoreUsingSalt(salt)
            .then(function(secretkey0) {
                if (secretkey0 == "") {
                    throw "secretkey emtpy";
                }
                secretkey0 = String(CryptoJS.SHA512(secretkey0 + salt));
                return new EncryptionWrapper(secretkey0, salt, alphabet);
            });
    }
    static fromPassword(password, salt, alphabet, login_sig) {
        let secretkey0 = String(CryptoJS.SHA512(login_sig + salt))
        return Promise.resolve(new EncryptionWrapper(secretkey0, salt, alphabet));
    }
    decryptChar(crypt){
        return EncryptionWrapper.decryptCharUsingKey(crypt, this.secretkey);
    }
    encryptChar(char){
        return EncryptionWrapper.encryptCharUsingKey(char, this.secretkey);
    }
    decryptPassword(name, kss){
        var self = this;
        return self.decryptChar(kss)
            .then(function(thekey) {
                if (thekey == ""){
                    return Promise.resolve("");
                }
                return EncryptionWrapper.getOrigPwd(self.confkey, self.pwSalt, String(CryptoJS.SHA512(name)), self.alphabet, thekey);
            });
    }
    encryptPassword(name, pass){
        var self = this;
        return EncryptionWrapper.genTempPwd(self.confkey, self.pwSalt, String(CryptoJS.SHA512(name)), self.alphabet, pass)
            .then(function(pass) {
                return self.encryptChar(pass);
            });
    }
    static genTempPwd(key, salt, account_sig, orig_alphabet, pwd) {
        return EncryptionWrapper.genAlphabet(key, salt, account_sig, orig_alphabet)
            .then(function(new_alphabet) {
                var temp_pwd = "";
                var i, j, pwd_len, alphabet_len;
                var shift = String(CryptoJS.SHA512(account_sig + key));
                var shift_len = shift.length;
                pwd_len = pwd.length;
                alphabet_len = new_alphabet.length;
                for(i = 0; i < pwd_len; i++){
                    for(j=0; j < alphabet_len; j++){
                        if(pwd.charAt(i) === orig_alphabet.charAt(j)){
                            temp_pwd = temp_pwd + new_alphabet.charAt((j+shift.charCodeAt(i % shift_len)) % alphabet_len);
                            break;
                        }
                    }

                    //LETTER NOT IN ALPHABET, DIRECT MAPPING
                    if(j === alphabet_len) {
                        temp_pwd = temp_pwd + pwd.charAt(i);
                    }
                }

                return temp_pwd;
            });
    }
    static getOrigPwd(key,salt,account_sig,orig_alphabet,temp_pwd) {
        return EncryptionWrapper.genAlphabet(key, salt, account_sig, orig_alphabet)
            .then(function(new_alphabet) {
                var pwd = "";
                var i, j, pwd_len, alphabet_len;
                var shift = String(CryptoJS.SHA512(account_sig+key));
                var shift_len = shift.length;
                pwd_len = temp_pwd.length;
                alphabet_len = new_alphabet.length;
                for(i = 0; i < pwd_len; i++){
                    for(j = 0; j < alphabet_len; j++){
                        if(temp_pwd.charAt(i) === new_alphabet.charAt(j)){
                            pwd = pwd + orig_alphabet.charAt((j + alphabet_len - (shift.charCodeAt(i % shift_len) % alphabet_len)) % alphabet_len);
                            break;
                        }
                    }
                    //LETTER NOT IN ALPHABET, DIRECT MAPPING
                    if (j === alphabet_len) {
                        pwd = pwd + temp_pwd.charAt(i);
                    }
                }
                return pwd;
            });
    }
    static genAlphabet(key, salt, account_sig, orig_alphabet){
        var new_alphabet = "";
        var shift_str;
        var shift_str_len = 0;
        var i, j, k;
        var tempchar;
        var orig_alphabet_len = orig_alphabet.length;
        return EncryptionWrapper.generateKey(key + account_sig, salt, 100)
            .then(function(shift_str) {
                shift_str_len = shift_str.length;

                for (i = 0;i < orig_alphabet_len;i++){
                    j = 0;
                    for(k = 0;k<6;k++){
                        j = j + shift_str.charCodeAt((i*6+k)%shift_str_len);
                    }

                    tempchar = orig_alphabet.charAt(j % orig_alphabet.length);
                    new_alphabet = new_alphabet + tempchar;
                    orig_alphabet = orig_alphabet.replace(tempchar, '');
                }

                return new_alphabet;
            });
    }
    get confkey() {
        return this._confkey || EncryptionWrapper.getConfKeyUsingSalt(this.pwSalt);
    }
    static getConfKeyUsingSalt(salt) {
        if(!sessionStorage.confusion_key) {
            return "";
        }
        return EncryptionWrapper.decryptCharUsingKey(sessionStorage.confusion_key, salt);
    }
    get pwdStore() {
        return EncryptionWrapper.getPwdStoreUsingSalt(this.pwSalt);
    }
    static getPwdStoreUsingSalt(salt) {
        if(!sessionStorage.pwdsk) {
            return "";
        }
        return EncryptionWrapper.decryptCharUsingKey(sessionStorage.pwdsk, salt);
    }
    static encryptCharUsingKey(encryptch, key){
        if(encryptch == "" || key == ""){  
            return Promise.reject("ERROR: empty key detected!");
        }
        var p = CryptoJS.AES.encrypt(encryptch,key).toString();
        return Promise.resolve(p);
    }
    static decryptCharUsingKey(echar, key){
        if(echar == "" || key == ""){  
            return Promise.reject("ERROR: empty key detected!");
        }
        var p = CryptoJS.enc.Utf8.stringify(CryptoJS.AES.decrypt(echar,key));
        return Promise.resolve(p);
    }
    static generateKey(key, orig_salt, iter){
        var hash = CryptoJS.SHA512(key);
        var salt = CryptoJS.SHA512(orig_salt);
        var gen_key = CryptoJS.PBKDF2(hash, salt, { keySize: 512/32, iterations: iter });   
        return Promise.resolve(String(gen_key));
    }

    generatePassphrase(plength) {
        var charlist = this.alphabet;
        var maxPos = charlist.length;
        var pwd = '';
        var i;
        for (i = 0; i < parseInt(plength); i++) {
            pwd += charlist.charAt(Math.floor(Math.random() * maxPos));
        }
        return pwd;
    }
}
