function pbkdf2_enc(key, orig_salt, iter){
    var hash = CryptoJS.SHA512(key);
    var salt = CryptoJS.SHA512(orig_salt);
    var gen_key = CryptoJS.PBKDF2(hash, salt, { keySize: 512/32, iterations: iter });   
    return String(gen_key);
}

function gen_alphabet(key, salt, account_sig, orig_alphabet){
    var new_alphabet="";
    var shift_str;
    var shift_str_len=0;
    var i,j,k;
    var tempchar;
    var orig_alphabet_len=orig_alphabet.length;
    shift_str=pbkdf2_enc(key+account_sig,salt,100);
    shift_str_len=shift_str.length;
    
    for (i=0;i<orig_alphabet_len;i++){
        j=0;
        for(k=0;k<6;k++){
            j = j + shift_str.charCodeAt((i*6+k)%shift_str_len);
        }
        
        tempchar=orig_alphabet.charAt(j % orig_alphabet.length);
        new_alphabet = new_alphabet + tempchar;
        orig_alphabet=orig_alphabet.replace(tempchar,'');
    }
    
    return new_alphabet;
}

function gen_temp_pwd(key, salt, account_sig,orig_alphabet,pwd)
{
    var new_alphabet = gen_alphabet(key,salt,account_sig,orig_alphabet);
    var temp_pwd = "";
    var i,j,pwd_len,alphabet_len;
    var shift = String(CryptoJS.SHA512(account_sig+key));
    var shift_len=shift.length;
    pwd_len=pwd.length;
    alphabet_len=new_alphabet.length;
    for(i=0;i<pwd_len;i++){
        for(j=0;j<alphabet_len;j++){
            if(pwd.charAt(i)==orig_alphabet.charAt(j)){
                temp_pwd = temp_pwd + new_alphabet.charAt((j+shift.charCodeAt(i % shift_len)) % alphabet_len);
                break;
            }
        }
        
        //LETTER NOT IN ALPHABET, DIRECT MAPPING
        if(j==alphabet_len) temp_pwd = temp_pwd + pwd.charAt(i);
    }
    
    return temp_pwd;
}

function get_orig_pwd(key,salt,account_sig,orig_alphabet,temp_pwd)
{
    var new_alphabet = gen_alphabet(key,salt,account_sig,orig_alphabet);
    var pwd = "";
    var i,j,pwd_len,alphabet_len;
    var shift=String(CryptoJS.SHA512(account_sig+key));
    var shift_len=shift.length;
    pwd_len=temp_pwd.length;
    alphabet_len=new_alphabet.length;
    for(i=0;i<pwd_len;i++){
        for(j=0;j<alphabet_len;j++){
            if(temp_pwd.charAt(i)==new_alphabet.charAt(j)){
                pwd = pwd + orig_alphabet.charAt((j + alphabet_len - (shift.charCodeAt(i % shift_len) % alphabet_len)) % alphabet_len);
                break;
            }
        }
        //LETTER NOT IN ALPHABET, DIRECT MAPPING
        if (j==alphabet_len) pwd = pwd + temp_pwd.charAt(i);
    }
    
    return pwd;
}

function tosend(cipherParams) {
            // create json object with ciphertext
            var send="";
               send+=cipherParams.ciphertext.toString(CryptoJS.enc.Base64)+"-";
            

            // optionally add iv and salt
            if (cipherParams.iv) {
                send+=cipherParams.iv.toString()+"-";
            }
            if (cipherParams.salt) {
                send+=cipherParams.salt.toString()+"-";
            }

            // stringify json object
            return send;
        }
 
 function getsend(sendstr) {
            // parse json string
			var i=sendstr.indexOf("-");
			var u=sendstr.substring(0,i);
            // extract ciphertext from json object, and create cipher params object
            var cipherParams = CryptoJS.lib.CipherParams.create({
                ciphertext: CryptoJS.enc.Base64.parse(u)
            });
			sendstr=sendstr.substring(i+1,sendstr.length);
            // optionally extract iv and salt
            if (sendstr!="") {
				i=sendstr.indexOf("-");
				u=sendstr.substring(0,i);
                cipherParams.iv = CryptoJS.enc.Hex.parse(u);
				sendstr=sendstr.substring(i+1,sendstr.length);
            }
            if (sendstr!="") {
				i=sendstr.indexOf("-");
				u=sendstr.substring(0,i);
                cipherParams.salt = CryptoJS.enc.Hex.parse(u);
				sendstr=sendstr.substring(i+1,sendstr.length);
            }

            return cipherParams;
        }
function encryptchar(encryptch,key){  
    if(encryptchar==""||key==""){  
        alert("ERROR: empty key detected!");  
        return;  
    }
   var p=tosend(CryptoJS.AES.encrypt(encryptch,key));
   return p;
}  

function decryptchar(char,key){  
    if(char==""||key==""){  
        alert("ERROR: empty key detected!");  
        return;  
    }
	var p=CryptoJS.AES.decrypt(getsend(char),key).toString(CryptoJS.enc.Utf8);
    return p;  
} 

function getpwd(charlist,plength) {
　　var maxPos = charlist.length;
　　var pwd = '';
　　for (i = 0; i < parseInt(plength); i++) {
　　　　pwd += charlist.charAt(Math.floor(Math.random() * maxPos));
　　}
　　return pwd;
}
