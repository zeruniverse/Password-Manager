<?php
require_once('function/basic.php');
require_once('function/config.php');
echoheader();
?>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
<div class="container theme-showcase">
    <div class="page-header">
        <h1>Password Manager</h1>
	</div>
	<p>New User:<br /></p>
    <p>Only numbers and letters are allowed for username</p>
	<form>
      <p>User Name: <input type="text" name="user" id="user" /></p><br />
      <p>Password: <input type="password" name="pwd" id="pwd" /></p><br />
      <p>Input Password Again: <input type="password" name="pwd1" id="pwd1" /></p><br />
      <p <?php if(!$ENABLE_PIN) echo 'style="display:none"';?>>Optional PIN: <input type="text" name="upin" id="upin" /><a href="javascript: $('#pinhint').show();">What's this?</a></p><br />
      <div id="pinhint" style="display:none; border-style:solid; border-width:1px; border-color:blue; padding:5px; margin:5px;">PIN gives you additional protection when attackers get your login password. PIN is used to map pseudo-password to real password. For example, the decrypted pseudo-password is 123 (decryption key is based on your login password), then if your PIN is 456, the pseudo-password maps to 789. If the PIN is 789, the pseudo-password maps to abc. If your PIN is 456, you'll see 789 in your screen and it's your real password. But if the attacker input 789 as PIN, he'll see abc in his screen. PIN related information will not go to Internet, this means you can login using any PIN, but the password you see will be totally different.<br /><br />You can input any characters (including non-english characters) as your PIN. If you input nothing, an empty string will be your PIN.<br /><br /><p style="color:red">Please always login with a same PIN, or you'll get incorrect previous generated passwords.</p> <br /></div>      
      <p>Email:<input type="text" name="email" id="email" /></p><br />
    </form>
    <input type="button" class="btn btn-md btn-success" id="chk"  value="Submit" />
<script type="text/javascript">
var JSsalt='<?php echo $GLOBAL_SALT_1;?>';
    function isEmail(aEmail) {
        var bValidate = RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(aEmail);
        if (bValidate) {
            return true;
        }
        else return false;
    }
    $(function(){ 
      $("#chk").click(function(){ 
        if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){alert("The second password you input doesn't match the first one. Or your password is too weak (length should be at least 7)"); return;}
        if (!isEmail($("#email").val())){alert("This is not a valid Email"); return;}
        if ($("#user").val().length<5) {alert("Length of username should be at least 5!"); return;}
        $("#chk").attr("disabled", true);
        $("#chk").attr("value", "Wait");
        function process(){
        var login_sig=String(pbkdf2_enc($("#pwd").val(),JSsalt,500));
        login_sig=String(pbkdf2_enc(login_sig,JSsalt,500));
        $.post("reg.php",{email:$("#email").val(), pwd:login_sig,  user: $("#user").val()},function(msg){ 
		if(msg==0){
			 	alert("User name already occupied, please choose another user name.");
				$("#chk").attr("value", "Submit");
				$("#chk").attr("disabled", false);
		}else
		if(msg==1){
			 	alert("This E-mail has already been used.");
				$("#chk").attr("value", "Submit");
				$("#chk").attr("disabled", false);
		}else
		if(msg==9){
			 	alert("Successfully signup, now please sign in!");
			 	window.location.href="index.php";
		}else{
                alert("There're some errors, please retry");
				$("#chk").attr("value", "Submit");
				$("#chk").attr("disabled", false);
		}
		 
        }); 
        }
        setTimeout(process,50);
    }); 
}); 
</script>
</div>
<?php echofooter();?>
