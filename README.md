# Password Manager
##Version
v3.5  
##Specification
This password manager can generate random strong passwords for users. The password is generated on user's browser and then encrypted using AES256.  
SHA512 algorithm is used for user identification check  
Then the browser will upload the encrypted password to server so server can never know what the password is. The decryption is also processed by the browser and a secret key (related to login password) is needed to decrypt the password.  
Secret key (related to password) will not be uploaded to server.  
## Notes
Programmed with PHP and JavaScript 
Please modify all PHP files under **Source/function** according your server configuration before use  
Demo: http://pw.zzeyu.com  
## Copyright
Jeffery Zhao, May 2015
## Licence
MIT
