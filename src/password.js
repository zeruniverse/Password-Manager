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