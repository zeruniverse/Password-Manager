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
//DON'T USE '\', IT CAN CAUSE PROBLEMS FOR BACKUP
//DO NOT MAKE THEM SAME
//salt 1 used to create sha512 of password (password signiture)
$GLOBAL_SALT_1='iunin19dnu9ismcj9IUNuia,cne9e389]{}{}[]*@key';
//Only use salt 2 in client side
$GLOBAL_SALT_2='ncew8d7*(e8fyh2inc osd2)wefcsBIUsdfq2as;dqw[;[]]';
//Only use salt 3 in server side
$GLOBAL_SALT_3='*&Kjnskjnaucibiqb9298hv9sHIUWNiukJNIusfbic897*(^)';

//Generating Password (those variables will be written into JavaScript)
//the default password length to be generated
$DEFAULT_LENGTH=13;
//Use those letters to generate random password
//DO NOT USE LETTERS LIKE &,>,< HERE (letters have special meaning in HTML)
//MAKE SURE EACH LETTER ONLY APPEAR ONCE!!!
$DEFAULT_LETTER_USED='RSTUVWXYZabcdefgABCDEFGHIJKLMNOPQhijklmnopqrstuvwxyz0123456789*=+~-';

//YOU ARE DONE.
//DON'T FORGET TO UPLOAD THE INITIAL TABLES!
//If you want to use email verification or CAPTCHA, please modify check.php
?>