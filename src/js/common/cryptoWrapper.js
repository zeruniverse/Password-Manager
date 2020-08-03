class EncryptionWrapper {
    constructor(secretkey, jsSalt, pwSalt, alphabet) {
        this.secretkey = secretkey;
        this.pwSalt = pwSalt;
        this.jsSalt = jsSalt;
        this.alphabet = alphabet;
    }

    static generateKeyWithSalt(input, salt, iterations, hasher) {
        var iter = iterations || 500;
        var hasher = hasher || CryptoJS.algo.SHA512;
        var hash = CryptoJS.SHA3(input, { outputLength: 512 });
        var salt = CryptoJS.SHA3(salt, { outputLength: 512 });

        var gen_key = CryptoJS.PBKDF2(hash, salt, { keySize: 512/32, iterations: iter,
                                                    hasher:hasher});
        return Promise.resolve(gen_key.toString());
    }

    static WgenerateKeyWithSalt(input, salt) {
        // a weak key.
        return generateKeyWithSalt(input, salt, 100, CryptoJS.algo.SHA3);
    }

    static SgenerateKeyWithSalt(input, salt) {
        // Didn't use SHA3 here because it's slow.
        return generateKeyWithSalt(input, salt, 5000, CryptoJS.algo.SHA512);
    }

    static fromLocalStorage(jsSalt, pwSalt, alphabet) {
        return EncryptionWrapper.getPwdStoreUsingSalt(pwSalt)
            .then(function(secretkey0) {
                if (secretkey0 == "") {
                    throw "secretkey emtpy";
                }
                return new EncryptionWrapper(secretkey0, jsSalt, pwSalt, alphabet);
            });
    }

    static fromPassword(password, jsSalt, pwSalt, alphabet, login_sig) {
        let secretkey0 = String(CryptoJS.SHA512(login_sig + pwSalt))
        return Promise.resolve(new EncryptionWrapper(secretkey0, jsSalt, pwSalt, alphabet));
    }

    decryptChar(crypt){
        return EncryptionWrapper.decryptCharUsingKey(crypt, this.secretkey);
    }
    encryptChar(char){
        return EncryptionWrapper.encryptCharUsingKey(char, this.secretkey);
    }
    decryptPassword(name, kss){
        var self = this;
        var thekey = "";
        return self.decryptChar(kss)
            .then(function(key) {
                if (key == ""){
                    return Promise.reject("");
                }
                thekey = key;
                return self.getConfkey();
            })
            .then(function(confkey){
                return EncryptionWrapper.getOrigPwd(confkey, self.pwSalt, String(CryptoJS.SHA512(name)), self.alphabet, thekey);
            });
    }

    encryptPassword(name, pass){
        var self = this;
        return self.getConfkey()
            .then(function(confkey){
                return EncryptionWrapper.genTempPwd(confkey, self.pwSalt, String(CryptoJS.SHA512(name)), self.alphabet, pass)
            })
            .then(function(pass) {
                return self.encryptChar(pass);
            });
    }

    generateSecretKey(password, store) {
        var self = this;
        var store = (typeof store !== 'undefined') ? store : true;
        return self.SgenerateKey(EncryptionWrapper.reduceInfo(password, self.alphabet))
            .then(function(sk) {
                if (store) {
                    self.secretkey = sk;
                }
                return sk;
            });
    }

    SgenerateKey(input) {
        return EncryptionWrapper.SgenerateKeyWithSalt(input, this.jsSalt);
    }

    WgenerateKey(input){
        return EncryptionWrapper.WgenerateKeyWithSalt(input, this.jsSalt);
    }

    multiGenerateKey(key, count) {
        var self = this;
        if (count == 0)
            return Promise.resolve(key);
        return self.generateKeyWithSalt(key, self.pwSalt)
            .then(function(key){
                return self.multiGenerateKey(key, count - 1);
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
    getConfkey() {
        var self = this;
        if (self._confkey)
            return Promise.resolve(self._confkey);
        return EncryptionWrapper.getConfKeyUsingSalt(self.pwSalt)
            .then(function(confkey) {
                self._confkey = confkey;
                return confkey;
            });
    }
    static getConfKeyUsingSalt(salt) {
        if(!sessionStorage.confusion_key) {
            return Promise.resolve("");
        }
        return EncryptionWrapper.decryptCharUsingKey(sessionStorage.confusion_key, salt);
    }
    getPwdStore() {
        return EncryptionWrapper.getPwdStoreUsingSalt(this.pwSalt);
    }
    static getPwdStoreUsingSalt(salt) {
        if(!sessionStorage.pwdsk) {
            return "";
        }
        return EncryptionWrapper.decryptCharUsingKey(sessionStorage.pwdsk, salt);
    }
    // not happy with the name, actually stores everything that will be read by getPwdStore, should rename getPwdStore
    persistCredentialsFromPassword(user, password) {
        var self = this;
        // No need to use strong Hash because this is going to session storage.
        return EncryptionWrapper.WgenerateKeyWithSalt(password, self.secretkey)
            .then(function(confkey) {
                return EncryptionWrapper.persistCredentials(user, self.secretkey, confkey, self.pwSalt);
            });
    }
    static persistCredentials(user, secretkey, confkey, salt) {
        setCookie("username", user);
        return EncryptionWrapper.encryptCharUsingKey(secretkey, salt)
            .then(function(pwdsk) {
                sessionStorage.pwdsk = pwdsk;
                return EncryptionWrapper.encryptCharUsingKey(confkey, salt);
            })
            .then(function(_confkey) {
                sessionStorage.confusion_key = _confkey;
                return secretkey;
            });
    }
    storePIN(device, pinSalt, pin) {
        var self = this;
        var encryptedPwdstore;
        var encryptedConfkey;
        return self.getPwdStore()
            .then(function(_pwdstore) {
                return EncryptionWrapper.encryptCharUsingKey(_pwdstore, pin);
            })
            .then(function(_pwdstore) {
                encryptedPwdstore = _pwdstore;
                return self.getConfkey();
            })
            .then(function(_confkey) {
                return EncryptionWrapper.encryptCharUsingKey(_confkey, pin);
            })
            .then(function(_confkey) {
                encryptedConfkey = _confkey;
                localStorage.setItem('pinsalt', pinSalt);
                localStorage.setItem('en_login_sec', encryptedPwdstore);
                localStorage.setItem('en_login_conf', encryptedConfkey);
                setCookie('device', device);
            });
    }
    restoreFromPIN(user, pin) {
        var self = this;
        var promises = [];
        promises.push(EncryptionWrapper.decryptCharUsingKey(localStorage.en_login_sec, pin));
        promises.push(EncryptionWrapper.decryptCharUsingKey(localStorage.en_login_conf, pin));
        return Promise.all(promises)
            .then(function(results) {
                var secretkey = results[0];
                var confkey = results[1];
                return EncryptionWrapper.persistCredentials(user, secretkey, confkey, self.pwSalt);
            })
            .then(function(secretkey) {
                return EncryptionWrapper.SgenerateKeyWithSalt(secretkey, user);
            });
    }
    deletePIN() {
        localStorage.removeItem("en_login_conf");
        localStorage.removeItem("en_login_sec");
        localStorage.removeItem("pinsalt");
        deleteCookie('device');
        deleteCookie('username');
    }
    static encryptCharUsingKey(encryptch, key){
        if(encryptch == "" || key == ""){
            return Promise.reject("ERROR: empty key detected!");
        }
        var p = CryptoJS.AES.encrypt(encryptch, key).toString();
        return Promise.resolve(p);
    }
    static decryptCharUsingKey(echar, key){
        if(echar == "" || key == ""){
            return Promise.reject("ERROR: empty key detected!");
        }
        var p = CryptoJS.enc.Utf8.stringify(CryptoJS.AES.decrypt(echar, key));
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
    static reduceInfo(key, charlist) {
        function getcharpos(x, charlist){
            var maxpos, j;
            maxpos = charlist.length;
            for(j = 0; j < maxpos; j++) if(x == charlist.charAt(j)) {
                return j;
            }
            return -1;
        }
        var maxpos = charlist.length;
        var newpw = '';
        var i,a,b;
        var keylen = key.length;
        newpw = newpw + keylen.toString();
        a = getcharpos(key.charAt(0), charlist);
        a = (a == -1) ? key.charCodeAt(0) : a;
        for(i = 1; i <= keylen - 1; i++){
            b = getcharpos(key.charAt(i), charlist);
            b = (b == -1) ? key.charCodeAt(i) : b;
            newpw = newpw + charlist.charAt( (a + b) % maxpos);
            a = b;
        }
        return newpw;
    }
}
