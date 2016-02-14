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
        <input type="button" class="btn btn-md btn-success" id="chk"  value="Login" /></form>
        <span id="nouser" class="errorhint"  style="display:none; color:Red">We don't have this user on file<br /></span>
        <span id="pwderr" class="errorhint"  style="display:none; color:Red">Wrong Password<br /></span>
        <span id="othererror" class="errorhint"  style="display:none; color:Red">Oops, our server run into some problems. Please refresh this page and try again.<br /></span>
        <hr />
        <button class="btn btn-sm btn-default" type="button" onClick="window.location.href='signup.php';" >Sign Up</button>&nbsp; <button class="btn btn-sm btn-warning" type="button" onClick="window.location.href='recovery.php';" >Password Recovery</button>
    <hr />
    <div>Version <?php echo $VERSION;?></div>
    </div>
<script type="text/javascript">
var JSsalt='<?php echo $GLOBAL_SALT_1;?>';
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
		var login_sig=String(pbkdf2_enc(reducedinfo(pwd,'<?php echo $DEFAULT_LETTER_USED; ?>'),JSsalt,500));
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
                confkey=pbkdf2_enc(String(CryptoJS.SHA512(pwd+secretkey)),JSsalt,500);
                setpwdstore(secretkey,confkey,'<?php echo $GLOBAL_SALT_2; ?>');                
                window.location.href="./password.php";
		}
        });
        }
        setTimeout(process,50);
    }); 
}); 
</script>
<?php echofooter();?>
