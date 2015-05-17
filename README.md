# Password Manager
##Version
v3.0  
##Specification
This password manager can generate random strong passwords for users. The password is generated on user's browser and then encrypt using AES256.  
SHA512 algorithm is used for user identification check  
Then the browser will upload the encrypted password to server so server can never know what the password is. The decrypted process also executed by the browser and the user need a secret key to decrypt the password.  
Secret key (related to password) will not be uploaded to server.  
## Notes
Programmed with PHP and JavaScript 
Please modify all PHP files under **Source/function** according your server configuration before use  
## Copyright
Jeffery Zhao, May 2015
## Licence
MIT
