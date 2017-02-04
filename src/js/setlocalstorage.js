function setpwdstore(sk,confusion_key,salt)
{
	sessionStorage.pwdsk=encryptchar(sk,salt);
  sessionStorage.confusion_key=encryptchar(confusion_key,salt);
}
function getpwdstore(salt)
{
	if(!sessionStorage.pwdsk) {return "";}
	return decryptchar(sessionStorage.pwdsk,salt);
}
function getconfkey(salt)
{
    if(!sessionStorage.confusion_key) {return "";}
	return decryptchar(sessionStorage.confusion_key,salt);
}
function delpwdstore()
{
  sessionStorage.clear();
}

//set a cookie never expires.
function setCookie(name,value)
{
    var exp = new Date();
    exp.setTime(exp.getTime() + 10 * 365 * 24 * 60 * 60 * 1000);
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}

function getcookie(name){    
   var cookieArray=document.cookie.split("; ");   
   var cookie=new Object();    
   for (var i=0;i<cookieArray.length;i++){    
      var arr=cookieArray[i].split("=");  
      if(arr[0]==name)return unescape(arr[1]);
   } 
   return ""; 
} 

function deleteCookie(name)
{
   document.cookie = name+"=;expires="+(new Date(0)).toGMTString();
}

function setlocalstore(name,value)
{
    localStorage.setItem(name, value);
}

function setPINstore(device,salt,encryptsec,encryptconf)
{
    setlocalstore('pinsalt',salt);
    setlocalstore('en_login_sec',encryptsec);
    setlocalstore('en_login_conf',encryptconf);
    setCookie('device',device);
}

function delpinstore()
{
    localStorage.clear();
    if(getcookie('device')!="") $.post("rest/deletepin.php",{user:getcookie('username'),device:getcookie('device')},function(msg){});
    deleteCookie('device');
}