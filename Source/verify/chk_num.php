<?php
session_start(); 
$code = trim($_POST['code']); 
if($code==$_SESSION["helloweba_num"]){ 
   echo '1'; 
} else echo '0'; 
?>