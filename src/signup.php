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
	<h3>New User</h3>
    <p>Only numbers and letters are allowed for username</p>
	<form style="max-width:300px;">
        <div class="form-group">
            <label for="user" class="control-label">User Name: </label>
            <input type="text" class="form-control" name="user" id="user" />
        </div>
        <div class="form-group">
            <label for="pwd" class="control-label">Password: </label>
            <input type="password" class="form-control"name="pwd" id="pwd" />
        </div>
        <div class="form-group">
            <label for="pwd1" class="control-label">Input Password Again: </label>
            <input type="password" class="form-control"name="pwd1" id="pwd1" />
        </div>
        <div class="form-group">
            <label for="email" class="control-label">Email:</label>
            <input type="text" class="form-control"name="email" id="email" />
        </div>
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
        var login_sig=String(pbkdf2_enc(reducedinfo($("#pwd").val(),'<?php echo $DEFAULT_LETTER_USED; ?>'),JSsalt,500));
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
