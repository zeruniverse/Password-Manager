# Password Manager  
[![Build Status](https://travis-ci.org/zeruniverse/Password-Manager.svg)](https://travis-ci.org/zeruniverse/Password-Manager)  
  
##Version
v5.0  
  
##DEMO  
[pas.jeffery.cc](http://pas.jeffery.cc)  
This demo is for test ONLY! Do NOT put your real password there.  
    
##Mechanism 
This password manager can generate and store random strong passwords for users. Passwords are generated on users' browsers and then encrypted using AES256.  
PBKDF2 with SHA512 is used for user identification check. Raw password will be mapped to a pseudo password before applying AES256.  
Secret key (related to password) will not be uploaded to server.  
  
##Details   
###Key Generation    
+ Secret_Key = PBKDF2(Login Password, Iteration: 500)  
+ Login Signiture = PBKDF2(Secret_Key, Iteration: 500)  
+ Confusion_Key = PBKDF2(SHA512(Login Password), Iteration: 100)   
    
###Password From User Screen to Server  
+ POST SHA512(Login Signiture + stamp sent from server) to server as authentication.    
+ User input account and corresponding password into web browser.    
+ Web browser calculate the mapping alphabet which is related to SHA512(account) and Confusion_Key.  
+ Using the mapping alphabet to map the raw password into a confusion password.  
+ Using AES256 to encrypt confusion password, as well as account and using Secret_Key as secret key.  
+ POST AES256 encrypted account and password to server.   
+ Server encrypt AES256 encrypted password again (password_1) and save encrypted account and password_1 into database.     
    
###Safety
+ If the hacker don't have the access to your web browser, he can only get SHA512(Login Signiture + stamp sent from server) in the net. Assume he can extract Login Signiture from the above information.      
+ In chrome, it cost 1.44s to generate Login Signiture. So it's hard to enumerate login password    
+ If the hacker only enumerate Secret_Key, he still needs to run 500 iterations   
+ If the hacker got Secret_Key, he can't calculate Confusion_Key, so he can't map the pseudo password to the real password. But he can get your account name at this time.   
+ If the hacker got Secret_Key and one of your real password. Since the mapping ALPHABET is different account by account (it's related to account name), he can't get the mapping for other accounts.  
+ If the hacker got access to your login password or web browser.....SO ONLY OPEN PASSWORD MANAGER IN TRUSTED DEVICES AND USE STRONG LOGIN PASSWORD!    
    
![mechanism](https://cloud.githubusercontent.com/assets/4648756/9157185/e0e6fa6a-3ea8-11e5-8379-284a4e7e1ca0.jpg) [Please note the new feature PBKDF2 and confusion algorithm is not shown in the graph]  
       
## How to Use
+ Install PHP, MySQL and WebServer(IIS, Apache or Nginx) in your server.  
+ Create a database in your MySQL server for this password manager.  
+ Import ``initial.sql`` into that database.  
+ Download ``src`` folder in this repository, modify ``src/function/config.php`` with your configuration (database and etc.)  
+ Upload files in modified ``src`` folder to your web server.  
+ Though your login password won't be transmitted in the network, you still need to enable SSL if possible to avoid MIMA. Malicious JavaScript stealing password might be injected into the page if HTTP is used.  
+ Visit www.your_domain_name.com  
+ Click Signup to register an account. (If you don't want others to sign up, just delete signup.php and reg.php after you are done)  
+ You are done!    
+ Remember your password to login. No one can recover your data if you lose that!  
  
## Copyright  
Jeffery Zhao  
License: MIT (A copy of license is attached in src folder)   
The copyright for Crypto-JS and Bootstrap are reserved by their authors.  

##About Upgrade  
This version is NOT compatible with any previous versions. To switch to new version:  
+ Open your old password manager and take a screenshot of all your accounts and passwords.  
+ Clear all your tables in database (The table structure has not been changed)  
+ Replace the old password manager files with the new version on your server  
+ Register a new account for password manager  
+ Create all your accounts back (Please note it's recommended to change passwords for all of your accounts when you upgrade)  

##About Recovery  
For your passwords safety, your login password to password manager won't be included in the recovery file. You still need your login password to decrypt the recovery file.  
+ The purpose of the recovery file is to protect your password in case of data loss. NOT IN CASE THAT YOU FORGET YOUR PASSWORD (No one can get your passwords without your login password!)  