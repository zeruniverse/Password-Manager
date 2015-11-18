<?php 
session_start();
if(isset($_SESSION["loginok"])&& $_SESSION['loginok']==1) {header("Location: ./password.php"); die();}
date_default_timezone_set('America/Los_Angeles');
$_SESSION['random_login_stamp']=date("Ymdhis").mt_rand(10000,99999);
require_once('function/basic.php');
require_once('function/config.php');
echoheader();
?>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
<script type="text/javascript" src="setlocalstorage.js"></script>
    <div class="container theme-showcase">
        <div class="page-header">
            <h1>Password Manager</h1>
        </div>
        <p>Please Sign in:<br /></p>
        <form>
            <p>User Name: <input type="text" name="user" id="user" /></p><br />
            <p>Password: <input type="password" name="pwd" id="pwd" /></p><br />
            <p>Optional PIN: <input type="text" name="upin" id="upin" /><a href="javascript: showpinhint();">What's this?</a></p><br />
            <div id="pinhint" style="display:none; border-style:solid; border-width:1px; border-color:blue; padding:5px; margin:5px;">PIN gives you additional protection when attackers get your login password. PIN is used to map pseudo-password to real password. For example, the decrypted pseudo-password is 123 (decryption key is based on your login password), then if your PIN is 456, the pseudo-password maps to 789. If the PIN is 789, the pseudo-password maps to abc. If your PIN is 456, you'll see 789 in your screen and it's your real password. But if the attacker input 789 as PIN, he'll see abc in his screen. PIN related information will not go to Internet, this means you can login using any PIN, but the password you see will be totally different.<br /><br />You can input any characters (including non-english characters) as your PIN. If you input nothing, an empty string will be your PIN.<br /><br /><p style="color:red">Please always login with a same PIN, or you'll get incorrect previous generated passwords.</p> <br /></div>
        <input type="button" class="btn btn-md btn-success" id="chk"  value="Login" /></form>
        <span id="nouser" class="errorhint"  style="display:none; color:Red">We don't have this user on file<br /></span>
        <span id="pwderr" class="errorhint"  style="display:none; color:Red">Wrong Password<br /></span>
        <span id="othererror" class="errorhint"  style="display:none; color:Red">Oops, our server get into some problems. Please refresh this page and try again.<br /></span>
        <hr />
        <button class="btn btn-sm btn-default" type="button" onClick="window.location.href='signup.php';" >Sign Up</button>&nbsp; <button class="btn btn-sm btn-warning" type="button" onClick="window.location.href='recovery.php';" >Password Recovery</button>
    <hr />
    <div>Version 5.0 Updated on Nov. 16, 2015 (Current Algorithm: AES-256 + SHA512 + PBKDF2 + CONFUSION + ALPHABET MAPPING)</div>
    </div>
<script type="text/javascript">
var JSsalt='<?php echo $GLOBAL_SALT_1;?>';
function showpinhint(){
    $("#pinhint").show();
}
$(function(){ 
    $("#chk").click(function(){ 
        $("#pinhint").hide();
        $("#chk").attr("disabled", true);
		$("#chk").attr("value", "Wait");
        function process(){
        var user = $("#user").val(); 
		var pwd = $("#pwd").val();
        
        var secretkey='';
        var confkey='';
        var login_sig=String(pbkdf2_enc(pwd,JSsalt,500));
        secretkey=login_sig;
        login_sig=pbkdf2_enc(login_sig,JSsalt,500);    
        
        $.post("check.php",{pwd:String(CryptoJS.SHA512(login_sig+"<?php echo $_SESSION['random_login_stamp']; ?>")),  user: user},function(msg){ 
        $(".errorhint").hide();
		if(msg==0){
			 	$("#nouser").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
		}else
		if(msg==1){
			 	$("#pwderr").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
		}else
		if(msg==4){
			 	$("#othererror").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
		}else{
                confkey=pbkdf2_enc(String(CryptoJS.SHA512($("#upin").val()+pwd)),JSsalt,500);
                setpwdstore(secretkey,confkey,'<?php echo $GLOBAL_SALT_2; ?>');                
                setpinsha($("#upin").val());
                window.location.href="./password.php";
		}
        });
        }
        setTimeout(process,50);
    }); 
}); 
</script>
<?php echofooter();?>
