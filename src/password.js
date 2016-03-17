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
            if(pwd.charAt(i)===orig_alphabet.charAt(j)){
                temp_pwd = temp_pwd + new_alphabet.charAt((j+shift.charCodeAt(i % shift_len)) % alphabet_len);
                break;
            }
        }
        
        //LETTER NOT IN ALPHABET, DIRECT MAPPING
        if(j===alphabet_len) {temp_pwd = temp_pwd + pwd.charAt(i);}
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

function encryptchar(encryptch,key){  
    if(encryptchar==""||key==""){  
        alert("ERROR: empty key detected!");  
        return;  
    }
   var p=CryptoJS.AES.encrypt(encryptch,key).toString();
   return p;
}  

function decryptchar(echar,key){  
    if(echar==""||key==""){  
        alert("ERROR: empty key detected!");  
        return;  
    }
	var p=CryptoJS.enc.Utf8.stringify(CryptoJS.AES.decrypt(echar,key));
    return p;  
} 

function getpwd(charlist,plength) {
    var maxPos = charlist.length;
    var pwd = '';
    var i;
    for (i = 0; i < parseInt(plength); i++) {
        pwd += charlist.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
}

function getcharpos(x,charlist){
	var maxpos,j;
	maxpos = charlist.length;
	for(j=0;j<maxpos;j++) if(x == charlist.charAt(j)) {
			return j;
	}
	return -1;	
}
function reducedinfo(key,charlist) {
	var maxpos = charlist.length;
	var newpw = '';
	var i,a,b;
	var keylen = key.length;
    newpw = newpw + keylen.toString();
    a = getcharpos(key.charAt(0),charlist);
	for(i=1;i< keylen - 1;i++){
		b = getcharpos(key.charAt(i),charlist);
		newpw = newpw + charlist.charAt((a+b) % maxpos);
        a=b;
	}
	return newpw;
}
