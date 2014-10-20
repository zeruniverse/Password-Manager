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
    
function encryptchar(encryptch,key){  
    if(encryptchar==""||key==""){  
        alert("加密私钥为空!");  
        return;  
    }  
   return tosend(CryptoJS.AES.encrypt(encryptch,key));
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
    return CryptoJS.AES.decrypt(getsend(char),key).toString(CryptoJS.enc.Utf8);  
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