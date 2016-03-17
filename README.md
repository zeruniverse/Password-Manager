# Password Manager  
[![Build Status](https://travis-ci.org/zeruniverse/Password-Manager.svg)](https://travis-ci.org/zeruniverse/Password-Manager)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/b5d954be72144355aa258748cfd05bca)](https://www.codacy.com/app/zzy8200/Password-Manager)
![License](https://img.shields.io/github/license/zeruniverse/Password-Manager.svg)
![Environment](https://img.shields.io/badge/PHP-5.2+-blue.svg)
![Environment](https://img.shields.io/badge/MySQL-required-ff69b4.svg)     
**'master' branch is a dev-branch, please download stable version from [SourceForge](https://sourceforge.net/projects/phppasswordmanager/files/latest/download) or [Release](https://github.com/zeruniverse/Password-Manager/releases) in order to use.**      
  
##Version
v9.08  
From v9.0, this project becomes mature. I've implemented all features planned when I started this project.       
There will still be infrequent bug fixes and improvements though.  
Supports are available [here](https://github.com/zeruniverse/Password-Manager/issues) for versions greater than v9.0   
Older versions will not be supported anymore. Please upgrade to versions later than v9.0    
  
##DEMO  
[phppasswordmanager.sourceforge.net](http://phppasswordmanager.sourceforge.net/)  
This demo is for test ONLY! Do NOT put your real password there.  
You can access the database for this demo [here](https://mysql-p.sourceforge.net), with login username **p2663268ro** and password **12345678**   
  
Additional test demo available here: [pas.jeffery.cc](http://pas.jeffery.cc/)  
This demo is for test purpose and might be unstable.     
    
##Mechanism 
This password manager can generate and store random strong passwords for users. Passwords are generated on users' browsers and then encrypted using AES256 (mode CBC).  
PBKDF2 with SHA512 is used for user identification check. Raw password will be mapped to a pseudo password with a key related to Password_1 before applying AES256. The mapping algorithm is alphabet and position based.    
Some part of information in Password_1 won't involve in calculations for identity check, So password_1 can't be obtained by enumerating password_0 (used for authentication).  
   
## How to Use
+ Install PHP, MySQL (MariaDB) and WebServer(IIS, Apache or Nginx) in your server. Make sure PDO is supported
+ Create a database in your MySQL server for this password manager
+ Import ``initial.sql`` into that database
+ Download ``src`` folder in this repository **from [newest Release](https://github.com/zeruniverse/Password-Manager/releases/latest)**, modify ``src/function/config.php`` with your configuration (database and etc.)
+ Upload files in modified ``src`` folder to your web server
+ Though your login password won't be transmitted in the network, you still need to enable SSL if possible to avoid MIMA. Malicious JavaScript stealing password might be injected into the page if HTTP is used
+ Visit www.your_domain_name.com
+ Click Signup to register an account. (If you don't want others to sign up, just delete signup.php and reg.php after you are done)
+ You are done!
+ Remember your password to login. No one can recover your data if you lose that!
+ If your data is lost (e.g. your server is stolen), you can go back to this repo and download your version of password manager (you can find version number in your backup file). You don't need config file to recover your password. All salts are included in the backup file. You can find recovery button on `index.html` after deploying the new password manager.
   
##Import/Export From/To Other Password Managers
###Import
+ Export your passwords from your password manager as csv file.
+ Make sure there is a header line, the password and account name column must be named "password" and "name" respectively, order is not important. If you have other fields, you should customize your fields after login (in setting). All other columns not listed in fields in your setting are being ignored.
+ Open the import dialog, paste the csv data and select csv as import type.
###Export
+ Login to your password manager, click [settings -> Export CSV]
+ Exported CSV are in UTF-8 encoding
+ Delete the CSV after you finish data transferring. Keeping this raw CSV file is dangerous.
   
##About Upgrade  
New version usually comes with algorithm updates and will NOT be compatible with any previous versions. To switch to new version:    
###Old version <= 9.0      
+ Open your old password manager and take a screenshot of all your accounts and passwords. (If your version is later than 5.0, you can use backup function and then go to recovery page to recover it. You'll get all your accounts and passwords on screen there.)
+ Drop your tables in database and import `initial.sql`
+ Replace the old password manager files with the new version on your server (don't forget to modify `config.php`)
+ Register a new account for password manager
+ Create all your accounts back 
  
###Old version > 9.0
+ Login to your old password manager. Go to [settings -> Export CSV]
+ The CSV is in raw format, open it and check if there's any error (For special character (non-English), you may find they dispaly weird in softwares like Office. That's because the CSV generated is in UTF-8 encoding. You can check its correctness by opening it in plain-text editor. When you import the csv, it shall work normally in password manager)
+ Drop your tables in database and import `initial.sql`
+ Deploy new password manager to your server (don't forget to modify `config.php`)
+ Create an account in new password manager.
+ Login and find the [Import accounts] section. Select the CSV file you just exported. Click [Submit].
+ All your password should be on your new password manager now.
+ If some error occurs, you can clear your database and redo the previous steps. This may take long time if you have many accounts. Find a good computer with good Internet access to do it!
    
##Backup and Recovery 
Though the probability is low, you can't deny that you may lose your passwords in various cases. So **please backup your passwords regularly**. You can use [settings -> Back Up] function implemented in the password manager. This will trigger a download of a strongly encrypted txt file. You can keep this file in cloud storage services such as Google Drive and Dropbox. If the password manager you use is damaged, you can go here and download the same version of the password manager you used. By using its recovery function, your txt file and your login password, you can recover all your passwords. Then export as raw and in your new password manager with the same version, import from backup. All passwords in txt file is encrypted and your login password which is used to decrypt won't be in the txt file. So it's safe to keep the txt file in cloud storage services.    
  
For your passwords safety, your login password to password manager won't be included in the recovery file. You still need your login password to decrypt the recovery file. The backup file is indepandent to config file. You don't need to backup your `config.php`         
+ The purpose of the recovery file is to protect your password in case of data loss. NOT IN CASE THAT YOU FORGET YOUR PASSWORD (No one can get your passwords without your login password!)
+ To recover your passwords, go to the homepage of the password manager and click `Password Recovery`.  
  
##Details   
###Key Generation    
+ Password_0 = REDUCED_INFO(Login Password)
+ Secret_key_0 = PBKDF2(Password_0, Iteration: 500)
+ Password_1 = Login Password + Secret_key_0
+ Login Signature = PBKDF2(Secret_key_0, Iteration: 500)
+ Confusion_Key = PBKDF2(SHA512(Password_1), Iteration: 500)
    
###Password From User Screen to Server  
+ POST SHA512(Login Signature + stamp sent from server) to server as authentication.
+ User input account and corresponding password into web browser.
+ Web browser calculate the mapping alphabet which is related to SHA512(account) and Confusion_Key.
+ Using the mapping alphabet to map the raw password into a confusion password. Same characters might map to different characters. This prevents the attacker to get the pattern of your password.
+ Using AES256 to encrypt confusion mapped password, as well as account and using Secret_Key as secret key.
+ POST AES256 encrypted account and password to server.
+ Server encrypt AES256 encrypted password again (S_password) and save encrypted account and S_password into database.
    
###Safety
+ If the hacker doesn't have the access to your web browser, he can only get SHA512(Login Signature + stamp sent from server) in the net. Assume he can extract Login Signature from the above information (which is already super difficult).
+ In chrome, it cost 2s to generate Login Signature from password_0. So it's hard to enumerate password_0
+ If the hacker gets password_0, he still needs password_1 to map the pseudo password to the real password. But he can get your account name at this time. (Password_1 contains more information than password_0 thus whatever extra information the attacker inputs, the passwords on screen will look like true passwords. It's hard to verify whether the Password_1 is correct.)
+ If the hacker gets login password and knows one of your real password. Since the mapping ALPHABET is different account by account (it's related to account name), he can't get the mapping for other accounts. The Password_1 will be hashed with PBKDF2, iteration 500 before mapping. If he decides to enumerate Password_1, every try costs 2s in Chrome.
+ If the hacker gets access to your login password, or web browser.....SO ONLY OPEN PASSWORD MANAGER IN TRUSTED DEVICES AND USE STRONG LOGIN PASSWORD!
  
###PIN Safety
+ PIN is a new feature introduced in v8.0, any string with length greater than 4 can be your PIN
+ Web browser needs fewer iterations of PBKDF2 to generate login_sig from PIN, thus, the speed of login can be improved
+ In your trusted devices, you can set a PIN after login and use PIN instead of username/password to login next time
+ Encrypt/Decrypt PIN-related information involves both client and server, so neither sides can make use of PIN alone, the mechanism is shown below:
    + User input PIN
    + Web browser generates a random salt of length 100 and put it in localstorage
    + web browser send SHA512(PIN+salt) as pin_sig to server
    + server receives pin_sig and generates a server_key with length 29, send server_key to web browser
    + web browser encrypt secretkey and conf_key with (PIN+server_key), server_key won't be stored in browser
    + When login, after inputing PIN, web browser send SHA512(PIN+salt) to server
    + server receives pin_sig, if it's correct, send back server_key, else, increase the error_try times by 1
    + if error_try > 3, server delete this PIN record, the user needs to input username/password.
    + After receiving server_key, web browser is able to decrypt secretkey and conf_key
    + By using PBKDF2 with 500 iterations, web browser gets login_sig and it uses username in cookie and this login_sig to login.
+ If attacker gets access to your web browser, he needs server_key to decrypt secretkey. Though PIN is much easier to enumerate, he only has 4 chances to guess. Or server_key will be permanantly deleted
+ If attacker gets access to server, since he doesn't have encrypted secretkey and conf_key, he has nothing to decrypt
    
###Performance  
+ The Login phase cost a lot of time because all keys need to be generated then. If the password is correct, it costs 4 seconds to login in Chrome (Intel i5) and 7 seconds on iPhone 6. If the password is incorrect, it takes 3 seconds for Chrome to know it's incorrect and show an alert.
+ Add a new account or click to show passwords won't take long since all keys are generated. On iPhone 6, they take up to 1 second.
+ Delete an account or refresh with a random password will finish instantly. (Trick: We don't care mapping at all when refresh. The password will be random anyway)
+ Change login password takes long time -> TIME = Login_Time + Click_To_Show_Time * Account_Number
+ Recover backup files takes long time (similar to [change login password])
+ *Only change login password and do recovery on a good computer to save time!*

###About Password_1    
+ Password_1 contains all information in your login password, part of which is not involved in server-side authentication.
+ Any information for the above part (I(Password_1) - I(Password_0)) will not be uploaded to server.
+ Using wrong login password might be able to login (as long as Password_0 induced is the same with the correct one), but will see incorrect passwords (very different from the correct ones). But the design of password_0 calculation ensures that incorrect login passwords by mistake won't log you in (For two passwords that can both log in, they'll differ at least for 4 characters). So only attackers that enumerate your login passwords might run into this case.
+ Change the extra information in Password_1 actually changes the pseudo-passwords.
+ Except that the user sees completely different passwords on screen, inputing wrong login password that happens to generate the correct password_0 won't cause any errors. And since all passwords uses alphabet-based confusion, it's hard to tell whether the password is correct directly.
    
<img width="1098" alt="mechanism" src="https://cloud.githubusercontent.com/assets/4648756/13795540/b0dfde78-eabe-11e5-8407-e5904dad59d2.png">         
##Extentions  
You can easily add E-mail verification, Google authentication... in your version of password manager. Put your implementation inside `check.php`, which is used for login authentication.   
  
##Copyright  
Jeffery Zhao  
License: MIT     
The copyright for Crypto-JS and Bootstrap are reserved by their authors.  
