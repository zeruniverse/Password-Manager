<?php
if ($_GET['reg']!='ok') {header("Location: signup.php");die();}
require_once('function/basic.php');
echoheader(2);
?>
<script type="text/javascript" src="sha512.js"></script>
<div class="container theme-showcase">
      <div class="page-header">
        <h1>Password Manager</h1>
	  </div>
	  <p>New User:<br /></p>
      <p>Only numbers and letters are allowed for username</p>
	  <form>
      <p>User Name: <input type="text" name="user" id="user" /></p><br />
      <p>Password: <input type="password" name="pwd" id="pwd" /></p><br />
      <p>Password Again: <input type="password" name="pwd1" id="pwd1" /></p><br />
      <p>Email:<input type="text" name="email" id="email" /></p><br />
      <p style="color:red">Please note: we'll verify your email the first time you login!</p></br>
      </form>
      <input type="button" class="btn btn-md btn-success" id="chk"  value="Submit" />
      <script type="text/javascript">
      function isEmail(aEmail) {
            var bValidate = RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(aEmail);
            if (bValidate) {
                return true;
            }
            else
                return false;
        }
  $(function(){ 
    $("#chk").click(function(){ 
        if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){alert("The second password you input doesn't match the first one. Or your password is too weak (length should be at least 7)"); return;}
        if (!isEmail($("#email").val())){alert("This is not a valid Email"); return;}
        if ($("#user").val().length<5) {alert("Length of username should be at least 5!"); return;}
        $("#chk").attr("disabled", true);
        $("#chk").attr("value", "Wait");
        var salt="lksandoiniNOIJONDS98U9328URF0s9d0s-f1023430r9f0-0f1029403ie0fcj0sdbidbv+_+13=24=23f-=gONOINCOIND3RJF9UHOSIJIOijnksdnkvp";
        $.post("reg.php",{csfds:'sdf', email:$("#email").val(), pwd:String(CryptoJS.SHA512("password manager:"+$("#pwd").val()+"by jeffery"+salt)),  user: $("#user").val()},function(msg){ 
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
    }); 
}); 
</script>
</div>
<?php echofooter();?>