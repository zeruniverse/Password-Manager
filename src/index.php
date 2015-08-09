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
        <span id="othererror" class="errorhint"  style="display:none; color:Red">Oops, our server get into some problems. Please refresh this page and try again.<br /></span>
        <hr />
        <button class="btn btn-sm btn-default" type="button" onClick="window.location.href='signup.php';" >Sign Up</button>&nbsp; <button class="btn btn-sm btn-warning" type="button" onClick="window.location.href='passcalc.php';" >Password Recovery</button>
    <hr />
    <div>Version 3.6 Updated on Aug 08, 2015 (Current Algorithm: AES-256 + SHA512)</div>
    </div>
<script type="text/javascript">
  $(function(){ 
    $("#chk").click(function(){ 
        var user = $("#user").val(); 
		var pwd = $("#pwd").val(); 
		var emailcode= $("#emailcode").val(); 
		var vericode = $("#code_num").val();
        var salt='<?php echo $GLOBAL_SALT_1;?>';
		$("#chk").attr("disabled", true);
		$("#chk").attr("value", "Wait");
        $.post("check.php",{pwd:String(CryptoJS.SHA512(String(CryptoJS.SHA512(pwd+salt))+"<?php echo $_SESSION['random_login_stamp']; ?>")),  user: user},function(msg){ 
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
                secretkey=pwd;
                setpwdstore(secretkey,'<?php echo $GLOBAL_SALT_2; ?>');                
			 	window.location.href="./password.php";
		}
		 
        }); 
    }); 
}); 
</script>
<?php echofooter();?>
