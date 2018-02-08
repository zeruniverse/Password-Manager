function decryptPassword(name, kss){
    var thekey=decryptchar(kss, secretkey);
    if (thekey==""){
        return "";
    }
    return get_orig_pwd(getconfkey(PWsalt), PWsalt, String(CryptoJS.SHA512(name)), ALPHABET, thekey);
}
function encryptPassword(name, pass){
    pass=gen_temp_pwd(getconfkey(PWsalt), PWsalt, String(CryptoJS.SHA512(name)), ALPHABET, pass);
    return encryptchar(pass, secretkey);
}

class Account {
    constructor(index, name, enpassword) {
        this.index = index;
        this.name = name;
        this.enpassword = enpassword;
        this.other = {};
        this.mFile = null;
        this.mEncryptionWrapper = null;
    }
    // reads the account from a dict (password still encrypted)
    static fromObject(obj) {
        //ToDo
    }
    // reads the account from a encrypted dict
    static fromEncrypted(encryptionWrapper, encryptedAccount) {
        let account = new Account(encryptedAccount["index"], encryptionWrapper.decryptChar(encryptedAccount["name"]), encryptedAccount["kss"]);
        account.encryptionWrapper = encryptionWrapper;
        if (encryptedAccount["additional"] != "") {
            //decrypt and extract json
            var data = $.parseJSON(encryptionWrapper.decryptChar(encryptedAccount["additional"]));
            for (var x in data)
                account.setOther(x, data[x]);
        }
        return account;
    }

    set encryptionWrapper(wrapper) {
        if (this.encryptionWrapper != null) {
            let pwd = this.password;
            this.mEncryptionWrapper = wrapper;
            this.password = pwd;
        }
        else {
            this.mEncryptionWrapper = wrapper;
        }
    }
    get encryptionWrapper() {
        return this.mEncryptionWrapper;
    }

    // get as encrypted object
    get encrypted() {
        let encryptedResult = { "kss":this.enpassword };
        if (this.index != null)
            encryptedResult["index"] = this.index;
        encryptedResult["name"] = this.encryptionWrapper.encryptChar(this.name);
        let other = JSON.stringify(this.other);
        encryptedResult["other"] = this.encryptionWrapper.encryptChar(other);
        return encryptedResult;
    }

    get accountName() {
        return this.name;
    }
    set accountName(name) {
        //reencrypt password
        let password = this.password;
        this.name = name;
        this.password = password;
    }
    get password() {
        //decrypt then output
        return this.encryptionWrapper.decryptPassword(this.name, this.enpassword);
    }
    set password(password) {
        //encrypt before storing
        this.enpassword = this.encryptionWrapper.encryptPassword(this.name, password);
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
    get availableOthers() {
        let availableOthers = [];
        for (let otherName in this.other) {
            availableOthers.push(otherName);
        }
        return availableOthers;
    }
    setOther(name, value) {
        this.other[name] = value;
    }
    getOther(name) {
        return this.other[name];
    }
    get file(){
        return this.mFile;
    }
    set file(file) {
        this.file = mFile;
    }
    addFile(name, key) {
        this.file = { "name": name, "key": key };
    }
}