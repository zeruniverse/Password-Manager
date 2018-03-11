// still used in cryptoWrapper
function pbkdf2_enc(key, orig_salt, iter){
    var hash = CryptoJS.SHA512(key);
    var salt = CryptoJS.SHA512(orig_salt);
    var gen_key = CryptoJS.PBKDF2(hash, salt, { keySize: 512/32, iterations: iter });   
    return String(gen_key);
}

// still used in recovery
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
            if(temp_pwd.charAt(i)===new_alphabet.charAt(j)){
                pwd = pwd + orig_alphabet.charAt((j + alphabet_len - (shift.charCodeAt(i % shift_len) % alphabet_len)) % alphabet_len);
                break;
            }
        }
        //LETTER NOT IN ALPHABET, DIRECT MAPPING
        if (j===alphabet_len) {pwd = pwd + temp_pwd.charAt(i);}
    }
    
    return pwd;
}

// still used in recovery
function encryptchar(encryptch,key){  
    if(encryptch==""||key==""){  
        alert("ERROR: empty key detected!");  
        return;  
    }
   var p=CryptoJS.AES.encrypt(encryptch,key).toString();
   return p;
}  

// still used in recovery, setlocalstorage
function decryptchar(echar,key){  
    if(echar==""||key==""){  
        alert("ERROR: empty key detected!");  
        return;  
    }
	var p=CryptoJS.enc.Utf8.stringify(CryptoJS.AES.decrypt(echar,key));
    return p;  
} 

// still used in password.js : reducedinfo
function getcharpos(x,charlist){
	var maxpos,j;
	maxpos = charlist.length;
	for(j=0;j<maxpos;j++) if(x == charlist.charAt(j)) {
			return j;
	}
	return -1;	
}
// still used in cryptoWrapper
function reducedinfo(key,charlist) {
	var maxpos = charlist.length;
	var newpw = '';
	var i,a,b;
	var keylen = key.length;
    	newpw = newpw + keylen.toString();
    	a = getcharpos(key.charAt(0),charlist);
    	a = (a==-1)?key.charCodeAt(0):a;
	for(i=1;i<= keylen - 1;i++){
		b = getcharpos(key.charAt(i),charlist);
		b = (b==-1)?key.charCodeAt(i):b;
		newpw = newpw + charlist.charAt((a+b) % maxpos);
        	a=b;
	}
	return newpw;
}
