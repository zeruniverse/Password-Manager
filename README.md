# Password Manager  
[![Build Status](https://travis-ci.org/zeruniverse/Password-Manager.svg)](https://travis-ci.org/zeruniverse/Password-Manager)  
  
##Version
v3.6  
##DEMO  
[pas.jeffery.cc](http://pas.jeffery.cc)  
This demo is for test ONLY! Do NOT put your real password there.  
    
##Mechanism
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
+ Add extensions (2-factor verification, CAPTCHA, etc.)  
+ Add recovery function  
+ Change SHA512 to PBKDF2  