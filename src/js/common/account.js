class Account {
    constructor(index, name, enpassword) {
        this.index = index;
        this.name = name;
        this.enpassword = enpassword;
        this.other = {};
    }
    // reads the account from a dict (password still encrypted)
    static fromObject(obj) {
        this = new Account(obj["index"], obj["name"], obj["kss"]);
        return this;
    }
    // reads the account from a encrypted dict
    static fromEncrypted(encryptedAccount) {
        this = new Account(encryptedAccount["index"], decryptchar(encryptedAccount["name"], secretkey), encryptedAccount["kss"]);
        if (encryptedAccount["additional"] != "") {
            //decrypt and extract json
            var data = $.parseJSON(decryptchar(encryptedAccount["additional"], secretkey));
            for (var x in data)
                this.setOther(x, data[x]);
        }
        return this;
    }

    // get as encrypted object
    get encrypted() {
        let encryptedResult = { "kss":this.enpassword, "index":this.index};
        encryptedResult["name"] = encryptchar(this.name, secretkey);
        let other = JSON.stringify(this.other);
        encryptedResult["other"] = encryptchar(other, secretkey);
        return encryptedResult;
    }

    get index() {
        return this.index;
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
        return decryptPassword(this.name, this.enpassword);
    }
    set password(password) {
        //encrypt before storing
        this.enpassword = encryptPassword(this.name, password);
    }
    get availableOthers() {
        let availableOthers = [];
        for (let otherName in this.other) {
            availableOthers += otherName;
        }
        return availableOthers();
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
}
