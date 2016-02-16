# Password Manager  
[![Build Status](https://travis-ci.org/zeruniverse/Password-Manager.svg)](https://travis-ci.org/zeruniverse/Password-Manager)
[![Codacy Badge](https://api.codacy.com/project/badge/grade/b5d954be72144355aa258748cfd05bca)](https://www.codacy.com/app/zzy8200/Password-Manager)
![License](https://img.shields.io/github/license/zeruniverse/Password-Manager.svg)
![Environment](https://img.shields.io/badge/PHP-5.2+-blue.svg)
![Environment](https://img.shields.io/badge/MySQL-required-ff69b4.svg)     
**'master' branch is a dev-branch, please download stable version from [Release](https://github.com/zeruniverse/Password-Manager/releases) in order to use.**      
  
##Version
v6.0  
  
##DEMO  
[pas.jeffery.cc](http://pas.jeffery.cc)  
This demo is for test ONLY! Do NOT put your real password there.  
    
##Mechanism 
This password manager can generate and store random strong passwords for users. Passwords are generated on users' browsers and then encrypted using AES256.  
PBKDF2 with SHA512 is used for user identification check. Raw password will be mapped to a pseudo password with a key related to PIN before applying AES256. The mapping algorithm is alphabet and position based.    
Any PIN related information will not be uploaded to server.  
  
##Details   
###Key Generation    
+ Password_0 = REDUCED_INFO(Login Password)     
+ Secret_key_0 = PBKDF2(Password_0, Iteration: 500)     
+ PIN = Login Password + Secret_key_0        
+ Login Signature = PBKDF2(Secret_key_0, Iteration: 500)  
+ Confusion_Key = PBKDF2(SHA512(PIN), Iteration: 500)   
    
###Password From User Screen to Server  
+ POST SHA512(Login Signature + stamp sent from server) to server as authentication.    
+ User input account and corresponding password into web browser.    
+ Web browser calculate the mapping alphabet which is related to SHA512(account) and Confusion_Key.  
+ Using the mapping alphabet to map the raw password into a confusion password. Same characters might map to different characters. This prevents the attacker to get the pattern of your password.    
+ Using AES256 to encrypt confusion password, as well as account and using Secret_Key as secret key.  
+ POST AES256 encrypted account and password to server.   
+ Server encrypt AES256 encrypted password again (password_1) and save encrypted account and password_1 into database.     
    
###Safety
+ If the hacker doesn't have the access to your web browser, he can only get SHA512(Login Signature + stamp sent from server) in the net. Assume he can extract Login Signature from the above information (which is already super difficult).      
+ In chrome, it cost 2s to generate Login Signature. So it's hard to enumerate login password    
+ If the hacker gets login password, he still need PIN to map the pseudo password to the real password. But he can get your account name at this time. (Since whatever PIN the attacker inputs, the passwords on screen will look like true passwords. It's hard to verify whether the PIN is right.)   
+ If the hacker gets login password and knows one of your real password. Since the mapping ALPHABET is different account by account (it's related to account name), he can't get the mapping for other accounts. The PIN will be hashed with PBKDF2, iteration 500 before mapping. If he decides to enumerate PIN, every try costs 2s in Chrome.   
+ If the hacker gets access to your login password and PIN, or web browser.....SO ONLY OPEN PASSWORD MANAGER IN TRUSTED DEVICES AND USE STRONG LOGIN PASSWORD!    
  
###Performance  
+ The Login phase cost a lot of time because all keys need to be generated then. If the password is correct, it costs 4 seconds to login in Chrome (Intel i5) and 7 seconds on iPhone 6. If the password is incorrect, it takes 3 seconds for Chrome to know it's incorrect and show an alert.     
+ Add a new account or click to show passwords won't take long since all keys are generated. On iPhone 6, they take up to 1 second.    
+ Delete an account or refresh with a random password will finish instantly. (Trick: We don't care mapping at all when refresh. The password will be random anyway)   
+ Change login password or pin takes long time -> TIME = Login_Time + Click_To_Show_Time * Account_Number    
+ Recover backup files takes long time (similar to [change login password])     
+ *Only change login password and do recovery on a good computer to save time!*      

###About PIN    
+ PIN uses the information in login password which is not involved in server-side authentication        
+ Any PIN related information for the above part will not be uploaded to server.     
+ Using wrong password might be able to login, but will see incorrect passwords.
+ Change PIN actually changes the pseudo-passwords.      
+ Incorrect PIN won't cause any error. But the user sees completely different passwords on screen. It's very unlikely that you input a wrong password by mistake which generates the same Login_Signature as the correct password.           
    
<img width="1114" alt="signup login" src="https://cloud.githubusercontent.com/assets/4648756/11234264/e07af92a-8d7a-11e5-967b-bff833c30e34.png">
         
## How to Use
+ Install PHP, MySQL and WebServer(IIS, Apache or Nginx) in your server.  
+ Create a database in your MySQL server for this password manager.  
+ Import ``initial.sql`` into that database.  
+ Download ``src`` folder in this repository **from [newest Release](https://github.com/zeruniverse/Password-Manager/releases/latest)**, modify ``src/function/config.php`` with your configuration (database and etc.)  
+ Upload files in modified ``src`` folder to your web server.  
+ Though your login password won't be transmitted in the network, you still need to enable SSL if possible to avoid MIMA. Malicious JavaScript stealing password might be injected into the page if HTTP is used.  
+ Visit www.your_domain_name.com  
+ Click Signup to register an account. (If you don't want others to sign up, just delete signup.php and reg.php after you are done)  
+ You are done!    
+ Remember your password to login. No one can recover your data if you lose that!  
+ If your data is lost (e.g. your server is stolen), you can go back to this repo and download your version of password manager (you can find version number in your backup file). You don't need config file to recover your password. All salts are included in the backup file. You can find recovery button on `index.html` after deploying the new password manager.    
  
##About Upgrade  
New version usually comes with algorithm updates and will NOT be compatible with any previous versions. To switch to new version:    
###Old version < 5.11      
+ Open your old password manager and take a screenshot of all your accounts and passwords. (If your version is later than 5.0, you can use backup function and then go to recovery page to recover it. You'll get all your accounts and passwords on screen there.)  
+ Clear all your tables in database (The table structure has not been changed, you don't need to drop them)  
+ Replace the old password manager files with the new version on your server (don't forget to modify `config.php`)  
+ Register a new account for password manager  
+ Create all your accounts back (Please note it's recommended to change passwords for all of your accounts when you upgrade)     
  
###Old version >= 5.11    
+ Login to your old password manager. Backup and save the backup file.    
+ Logout and go to the recovery page.     
+ Input all contents in backup file to the recovery box and click [RECOVER IT!], after the recovery, a new button [Export Raw Data] will be on your screen.     
+ Click [Export Raw Data] and save the raw_pass.txt file.    
+ Clear your database and deploy new password manager to your server (don't forget to modify `config.php`)      
+ Create an account in new password manager.     
+ Login and find the [Import accounts] section. Copy all contents in the raw_pass.txt and paste them into the box. Click [Submit].
+ All your password should be on your new password manager now.    
+ If some error occurs, you can clear your database and redo the previous steps. This may take long time if you have many accounts. Find a good computer with good Internet access to do it!       
      
##About Recovery  
For your passwords safety, your login password to password manager won't be included in the recovery file. You still need your login password (and PIN if applicable) to decrypt the recovery file. The backup file is indepandent to config file. You don't need to backup your `config.php`         
+ The purpose of the recovery file is to protect your password in case of data loss. NOT IN CASE THAT YOU FORGET YOUR PASSWORD or PIN (No one can get your passwords without your login password!)  
   
##Import From Other Password Managers
+ Export your passwords from your password manager as csv file.  
+ Make sure there is a header line, the password and account name column must be named "password" and "name" respectively, order is not important. All other columns are being ignored.    
+ Open the import dialog, paste the csv data and select csv as import type.    
    
##Backup
Though the probability is low, you can't deny that you may lose your passwords in various cases. So **please backup your passwords regularly**. If you are the owner of the server hosting the password manager, you may simply back up the database. Otherwise, you can use the backup
function implemented in the password manager. This will trigger a download of a JSON file. You can keep this file in cloud storage services such as Google Drive and Dropbox. If the password manager you use is damaged, you can go here and download the same version of the password manager
you used. By using its recovery function, your JSON file and your login password, you can recover all your passwords. All passwords in JSON file is encrypted and your login password which is used to decrypt won't be in the JSON file. So it's safe to keep the JSON file in cloud storage
services.    
      
##Extentions  
You can easily add E-mail verification, Google authentication... in your version of password manager. Put your implementation inside `check.php`, which is used for login authentication.   
     
##TODO  
###Search account (Assigned)
Implement search function in the password manager. (Assigned to @BenjaminHae)     
###URL and tags (Assigned)
Add tags and login URL to password manager.  (Assigned to @BenjaminHae)   
###Implement PIN to make password manager more convenient
For trust devices, instead of inputing user name/password every time to login, one can just input simply PIN. With username/password 'remembered (encrypted)' in local storage.    
The PIN is used to decrypt the encrypted login password in the local storage. Since PIN is usually simple, we should do some tricks to avoid brute force. This means the decryption
of the passwords in local storage should also involve the web server and let the web server limit the max allowed tries. This function is similar to how you unlock your iPhone.      
We should have a function called untrust this device.      
We should give users option to switch to other accounts.     
If the remembered password is incorrect (password changed after the last login), let the user login again.      
###Implement history track
List current sessions/past x days login history.     
History includes login IP, browser type, OS type, time and maybe which password is seen.   
###Implement timeout logout
Users might forget to logout the password manager. We should automatically log them out if there isn't any operation during a period of time (or simply log them out in x minutes).  
  
##Contribution
Any help to improve this project is greatly appreciated!     
     
###For New Idea
Submit an [issue](https://github.com/zeruniverse/Password-Manager/issues), or submit a pull request that adds your new idea in [TODO](https://github.com/zeruniverse/Password-Manager#TODO)    
###Contribute Code
Please help solve unassigned [TODO](https://github.com/zeruniverse/Password-Manager#TODO). Before starting coding, submit a pull request and mark that part as assigned and write your GitHub ID.       
I'll try to respond to issues and pull requests within 24 hours.    
If you contribute code to this project, your name will show up [here](https://github.com/zeruniverse/Password-Manager/graphs/contributors).      
###Other Helps
Please help distribute this project and let more people know if you feel it helpful :)       
  
**All contributors must agree that your code/ideas are going be distributed under MIT license. And a copy of MIT license should be included in the copy of this software that you distribute.**     
      
##BUG Report
If you find a BUG, please submit an [issue](https://github.com/zeruniverse/Password-Manager/issues).     
     
##Warranty
We (all developers/contributors) understand that your passwords are valuable, so we tried our best to make sure this software protects your passwords well. The safety of this software is heavily based on the safety of AES-256
algorithm, which is widely used for encryption and proven to be solid. However, we can't give any warranty for this free software as is stated in the license. Who knows if FBI secretly keeps some algorithm that solves AES in
linear time complexity? If you have a REALLY REALLY REALLY important account, only keeping that in your mind will make it 100% safe.      
     
##Copyright  
Jeffery Zhao  
License: MIT     
The copyright for Crypto-JS and Bootstrap are reserved by their authors.  