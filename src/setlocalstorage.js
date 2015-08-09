function setpwdstore(sk,salt)
{
	sessionStorage.pwdsk=encryptchar(sk,salt);
}
function getpwdstore(salt)
{
	if(!sessionStorage.pwdsk) return "";
	return decryptchar(sessionStorage.pwdsk,salt);
}
function delpwdstore()
{
  sessionStorage.clear();
}