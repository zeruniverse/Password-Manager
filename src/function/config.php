<?php
//Your database host, e.g. localhost
$DB_HOST='';

//The database name for password_manager
$DB_NAME='';

//Database Username
$DB_USER='';

//Database Password
$DB_PASSWORD='';


//OPTIONAL
//SALT For Encryption
//Don't change this section after you start using password-manager. Or you can't decrypt your password!
//DO NOT MAKE THEM SAME
//salt 1 used to create sha512 of password (password signiture)
$GLOBAL_SALT_1='$ajoio090__---3fsjv0=+\\()ns';
//Only use salt 2 in client side
$GLOBAL_SALT_2='jkj1D`skbfiu*Ad981380)()A9298rd~asi';
//Only use salt 3 in server side
$GLOBAL_SALT_3='*&Kjnskjnaucibiqb9298hv9sHIUWNiu';

//Generating Password (those variables will be written into JavaScript)
//the default password length to be generated
$DEFAULT_LENGTH=13;
//Use those letters to generate random password
//DO NOT USE LETTERS LIKE &,>,< HERE (letters have special meaning in HTML)
$DEFAULT_LETTER_USED='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789*=+~';

//YOU ARE DONE.
//DON'T FORGET TO UPLOAD THE INITIAL TABLES!
//If you want to use email verification or CAPTCHA, please modify check.php
?>