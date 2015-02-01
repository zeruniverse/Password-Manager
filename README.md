# Password Manager
##Version
v1.8  
##Specification
This password manager can generate random strong passwords for users. The password is generated on user's browser and then encrypt using AES256.  
Then the browser will upload the encrypted password to server so server can never know what the password is. The decrypted process also executed by the browser and the user need a secret key to decrypt the password.  
Secret key will not be uploaded to server.  
## Notes
Programmed with PHP and JavaScript 
Please modify source/sqllink.php and source/mail/Sendmail.php according your server configuration before use  
## Copyright
Jeffery Zhao, Oct 20, 2014
## Licence
MIT