/** 
 *  
 * @param encryptchar 加密字符 
 * @param key 加密私钥 
 * @return 
 */  
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
 var salt1="sadkn)(U*(H*Sch9vsdgj92j389fdfjv-0q930fsdf++=)sdc/df)scsdf\'sdfcefwe]\")(813^csjncnoiefmoioijOIJOINOSUBCnelfod)";
 var salt2="*&(@8ue0hf9sdb9v9h9hIBYsadninib2iuh89bsoadoiUIU)_+@~~!!@~!@+_E=-fjisnowoin3n9s8b9b9bv9b939h0j-0-+——）——@Jsfoiiej";
 var salt3="~`32122df\'sdfcefwe]\")(813^csjncnoiefmoiosfmpomponn+@~~!!@~!@+_E=-fjisnowoin3n9s8b9b9bv9+@~~!!@~!@+_E=-fjisnow";
 var salt4="sampdcoofnieoij0()98hr93biofnvsi9*（QHbrcan3vnf89shfa82kfw0d9OO@(*&RH(HRQ9en9mef9jfq039mu0v))io)()-w0-if-w-e0-f_";
 var salt5="asdniio98h98090()!_i09u0d-c-~!@~`2#@$#%#^$%%Y$^@$TG@$T@T%#VGFRFWEHWEG@$FT#$#^#^#$YB$%Y^%B$%Y#T$%V%$YVTUTsfdsfg1";   
function encryptchar(encryptch,key){  
    if(encryptchar==""||key==""){  
        alert("加密私钥为空!");  
        return;  
    }
   var ckey=String(CryptoJS.SHA512(String(CryptoJS.SHA512(String(CryptoJS.SHA512(String(CryptoJS.SHA512(String(CryptoJS.SHA512(key+salt1))+salt2))+salt3))+salt4))+salt5));
   var p=tosend(CryptoJS.AES.encrypt(encryptch,ckey));
   return p;
}  
/** 
 *  
 * @param char 密文 
 * @param key 解密私钥 
 * @return 
 */  
function decryptchar(char,key){  
    if(char==""||key==""){  
        alert("私钥为空!");  
        return;  
    }
	var ckey=String(CryptoJS.SHA512(String(CryptoJS.SHA512(String(CryptoJS.SHA512(String(CryptoJS.SHA512(String(CryptoJS.SHA512(key+salt1))+salt2))+salt3))+salt4))+salt5));
	var p=CryptoJS.AES.decrypt(getsend(char),ckey).toString(CryptoJS.enc.Utf8);
    return p;  
} 

/*产生字符*/
function getpwd() {
  var len=11;
　　len = len || 32;
　　var $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; 
　　var maxPos = $chars.length;
　　var pwd = '';
　　for (i = 0; i < len; i++) {
　　　　pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
　　}
　　return pwd;
}