# Password Manager  
[![Build Status](https://travis-ci.org/zeruniverse/Password-Manager.svg)](https://travis-ci.org/zeruniverse/Password-Manager)  
  
##Version
v5.0  
##DEMO  
[pas.jeffery.cc](http://pas.jeffery.cc)  
This demo is for test ONLY! Do NOT put your real password there.  
    
##Mechanism
Update:    
+ PBKDF2 with SHA512 and iteration 500 is used to generate password signiture.   
+ Confusion algorithm is applied to your account passwords. It means your password is safe even if AES is hacked.  
+ More on this [issue](https://github.com/zeruniverse/Password-Manager/issues/2)  

This password manager can generate and store random strong passwords for users. Passwords are generated on users' browsers and then encrypted using AES256.  
SHA512 algorithm is used for user identification check  
Secret key (related to password) will not be uploaded to server.  
![mechanism](https://cloud.githubusercontent.com/assets/4648756/9157185/e0e6fa6a-3ea8-11e5-8379-284a4e7e1ca0.jpg)  
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
## TODO  
+ Add extensions support (2-factor verification, CAPTCHA, etc.)  
+ Add recovery function  
+ Implement *change password* for 5.0 version.

##About Upgrade (Not completed yet. Do not deploy 5.0 on your server for now...)  
This version is NOT compatible with any previous versions. To switch to new version:  
+ Open your old password manager and take a screenshot of all your accounts and passwords.  
+ Clear all your tables in database (The table structure has not been changed)  
+ Replace the old password manager files with the new version on your server  
+ Register a new account for password manager  
+ Create all your accounts back (Please note it's recommended to change passwords for all of your accounts when you upgrade)  

##About recovery  
For your passwords safety, your login password to password manager won't be included in the recovery file. You still need your login password to decrypt the recovery file.  
+ The purpose of the recovery file is to protect your password in case of data loss. NOT IN CASE THAT YOU FORGET YOUR PASSWORD (No one can get your passwords without your login password!)  