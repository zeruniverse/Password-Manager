function setpwdstore(sk,confusion_key,salt)
{
	sessionStorage.pwdsk=encryptchar(sk,salt);
    sessionStorage.confusion_key=encryptchar(confusion_key,salt);
}
function getpwdstore(salt)
{
	if(!sessionStorage.pwdsk) return "";
	return decryptchar(sessionStorage.pwdsk,salt);
}
function getconfkey(salt)
{
    if(!sessionStorage.confusion_key) return "";
	return decryptchar(sessionStorage.confusion_key,salt);
}
function delpwdstore()
{
  sessionStorage.clear();
}