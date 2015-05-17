<?php session_start(); if(isset($_SESSION["loginok"])&& $_SESSION['loginok']==1) header("Location: ./password.php");
require_once('function/basic.php');
echoheader(2);
?>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="password.js"></script>
<script type="text/javascript" src="setcookies.js"></script>
<div class="container theme-showcase">
      <div class="page-header">
        <h1>Password Manager</h1>
	  </div>
	  <p>Please Sign in:<br /></p>
	  <form>
      <p>User Name: <input type="text" name="user" id="user" /></p><br />
      <p>Password: <input type="password" name="pwd" id="pwd" /></p><br />
      <div id="emailcheck" style="display:none"><p>E-mail Verification code:<input type="text" name="emailcode" id="emailcode" />(If you want us to resend email, please leave this box blank and click Login)</p><br /></div>
      <div id="vericode" style="display:none" ><p>CAPTCHA:<input type="text" class="input" id="code_num" name="code_num" maxlength="4" /><img src="verify/code_num.php" width="60" height="20" id="getcode_num" style="display:inline" title="Change One">(Click the image to refresh)</p><br /></div>
    
    <input type="button" class="btn btn-md btn-success" id="chk"  value="Login" /></form>
    <span id="firstlogin" class="ppla" style="display:none; color:Red">Wrong E-mail Verification code, new code has been sent out.<br /></span>
    <span id="verierr" class="ppla" style="display:none; color:Red">Wrong CAPTCHA, please try again<br /></span>
    <span id="nouser" class="ppla"  style="display:none; color:Red">We don't have this user on file<br /></span>
    <span id="pwderr" class="ppla"  style="display:none; color:Red">Wrong Password<br /></span>
    <span id="othererror" class="ppla"  style="display:none; color:Red">Oops, our server get into some problems. Please refresh this page and try again.<br /></span>
    <hr />
    <button class="btn btn-sm btn-default" type="button" onClick="window.location.href='signup.php';" >Sign Up</button>&nbsp; <button class="btn btn-sm btn-warning" type="button" onClick="window.location.href='passcalc.php';" >Password Calc</button>
    <hr />
    <div>Version 3.5 Updated on May 11, 2015 (Current Algorithm: AES-256 + SHA512)</div>
</div>
<script type="text/javascript">
  $(function(){ 
    $("#getcode_num").click(function(){ 
        $(this).attr("src",'verify/code_num.php?' + Math.random());
    }); 
    $("#chk").click(function(){ 
        var user = $("#user").val(); 
		var pwd = $("#pwd").val(); 
		var emailcode= $("#emailcode").val(); 
		var vericode = $("#code_num").val();
        var salt="lksandoiniNOIJONDS98U9328URF0s9d0s-f1023430r9f0-0f1029403ie0fcj0sdbidbv+_+13=24=23f-=gONOINCOIND3RJF9UHOSIJIOijnksdnkvp";
		$("#chk").attr("disabled", true);
		$("#chk").attr("value", "Wait");
        $.post("check.php",{csfds:'sdf', emailcode:emailcode, pwd:String(CryptoJS.SHA512("password manager:"+pwd+"by jeffery"+salt)),  user: user, vericode:vericode},function(msg){ 
        $(".ppla").hide();
		if(msg==0){
			 	$("#nouser").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","verify/code_num.php?"+ Math.random());
		}else
		if(msg==1){
			 	$("#pwderr").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","verify/code_num.php?"+ Math.random());
		}else
		if(msg==2){
			 	$("#firstlogin").show();
				$("#emailcheck").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","verify/code_num.php?"+ Math.random());
		}else
		if(msg==3){
			 	$("#verierr").show();
				$("#vericode").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","verify/code_num.php?"+ Math.random());
		}else
		if(msg==4){
			 	$("#othererror").show();
				$("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
				$("#getcode_num").attr("src","verify/code_num.php?"+ Math.random());
		}else{
                secretkey='pw.zzeyu.com'+pwd+'zzeyu.com';
                setpwdstore(secretkey);                
			 	window.location.href="password.php";
		}
		 
        }); 
    }); 
}); 
</script>
<?php echofooter();?>