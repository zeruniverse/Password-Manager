<?php 
session_start();
if(isset($_SESSION["loginok"])&& $_SESSION['loginok']==1) {header("Location: ./password.php"); die();}
if(!isset($_SESSION['random_login_stamp'])) $_SESSION['random_login_stamp']=date("Ymdhis").mt_rand(10000,99999);
require_once('function/basic.php');
require_once('function/config.php');
require_once("function/sqllink.php");
if($DB_NAME=='') die('PLEASE CONFIG function/config.php before using this system!');
echoheader();
function usepin()
{
    if(!isset($_COOKIE["username"]) || !isset($_COOKIE["device"])) return False;
    $user=$_COOKIE["username"];
    $device=$_COOKIE["device"];
    if($user==""||$device=="")  return False;
    $link=sqllink();
    if(!$link) return False;
    $sql="SELECT id FROM `pwdusrrecord` WHERE `username`= ?";
    $res=sqlexec($sql,array($user),$link);
    $record= $res->fetch(PDO::FETCH_ASSOC);
    if($record==FALSE) return False;
    $id = $record['id'];
    $sql="DELETE FROM `pin` WHERE `errortimes` >= 3 OR UNIX_TIMESTAMP( NOW( ) ) - UNIX_TIMESTAMP(`createtime`) > ?";
    $res=sqlexec($sql,array($PIN_EXPIRE_TIME),$link);
    $sql="SELECT * FROM `pin` WHERE `userid`= ? AND `device`= ?";
    $res=sqlexec($sql,array($id,$device),$link);
    $record= $res->fetch(PDO::FETCH_ASSOC);
    if($record==FALSE) return False;
    return True;
}
?>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
<script type="text/javascript" src="setlocalstorage.js"></script>
<script type="text/javascript">
function isSupportFileApi() {
    if(window.File && window.FileList && window.FileReader && window.Blob) {
        return true;
    }
    return false;
}
if(!isSupportFileApi()||typeof(Storage) == "undefined") {
        window.location.href="./sorry_for_old_browser_update_hint.html";
    }
</script>
    <div class="container theme-showcase" style="margin-top:-30px;">
        <div class="page-header">
            <h1>Password Manager</h1>
        </div>
        <h3>Please Sign in</h3>
        <form style="max-width:300px;" id="loginform">
            <div class="form-group">
                <label for="user" class="control-label sr-only">User Name: </label>
                <input type="text" class="form-control" placeholder="User Name" name="user" id="user" />
                <label for="pwd" class="control-label sr-only">Password: </label>
                <input type="password" class="form-control" placeholder="Password" name="pwd" id="pwd" />
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-lg btn-success btn-block" id="chk"  value="Login" />
            </div>
        </form>
        <span id="nouser" class="errorhint"  style="display:none; color:Red">We don't have this user on file<br /></span>
        <span id="pwderr" class="errorhint"  style="display:none; color:Red">Wrong Password<br /></span>
        <span id="blockip" class="errorhint"  style="display:none; color:Red">Your IP has been blocked due to malicious activity<br /></span>
        <span id="accountban" class="errorhint"  style="display:none; color:Red">Your account has been protected due to continuous attack. Try again in <?php echo $ACCOUNT_BAN_TIME;?> seconds<br /></span>
        <span id="othererror" class="errorhint"  style="display:none; color:Red">Oops, our server run into some problems. Please refresh this page and try again.<br /></span>
        <hr />
        <button class="btn btn-sm btn-default" type="button" onClick="window.location.href='signup.php';" >Sign Up</button>&nbsp; <button class="btn btn-sm btn-warning" type="button" onClick="window.location.href='recovery.php';" >Password Recovery</button>
    <hr />
    <div>Version <?php echo $VERSION;?> (<a href="https://github.com/zeruniverse/Password-Manager/releases">DOWNLOAD</a>)</div>
    <div class="modal" tabindex="-1" role="dialog" id="usepin">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4>Use PIN to login</h4>
                </div>
                <div class="modal-body">
                    <form id="pinloginform">
                        <div class="form-group">
                            <label for="pin" class="control-label">PIN:</label>
                            <input id="pin" class="form-control" type="password" />
                            <label class="small" style="display:block; clear:both;">You see this window because you or someone set an PIN in this device and choose it as default login method. To switch account or disable PIN, please press the red button below.</label>
                            <label class="small" style="display:block; clear:both; color:red">Close this window or press the white button below only let you use username/password to login this time. PIN will still be chosen as default method in future.</label>
                        </div>
                </div>
                <div class="modal-footer">
                    <p style="display:none" id="pinerrorhint">PIN ERROR, try again.</p>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Use username/password</button>
                    <button type="button" onClick="delpinstore();deleteCookie('username');" class="btn btn-danger" data-dismiss="modal">Disable PIN</button>
                    <input type="submit" style="display:inline" class="btn btn-primary" id="pinlogin" value="Login" /></form>
                </div>
            </div>
        </div>
    </div>
    </div>
<script type="text/javascript">
var JSsalt='<?php echo $GLOBAL_SALT_1;?>';
var PWSalt='<?php echo $GLOBAL_SALT_2; ?>';
$("#usepin").on("hidden.bs.modal", function () {
    $("#user").focus();
});
$(function(){
    if(getcookie('device')!="")
    {
        if(1==<?php if(usepin()) echo 1; else echo 0;?>) {
            $("#usepin").modal("show");
            $("#pin").focus();
        }
        else{
            delpinstore();
            $("#user").focus();
        }
    } else $("#user").focus();
    $("#pinloginform").on('submit',function(e){
        var pin;
        e.preventDefault();
        $("#pinerrorhint").hide();
        $("#pinlogin").attr("disabled", true);
        $("#pinlogin").val("Wait");
        pin=$("#pin").val();
        $.post("getpinpk.php",{user:getcookie('username'),device:getcookie('device'),sig:String(CryptoJS.SHA512(String(CryptoJS.SHA512(pin+localStorage.pinsalt))+"<?php echo $_SESSION['random_login_stamp']; ?>"))},function(msg){
            if(msg == '0') {$("#usepin").modal("hide");delpinstore();$("#user").focus();return;}
            if(msg == '1') {$("#pin").val('');$("#pinerrorhint").show();$("#pinlogin").attr("disabled", false);$("#pinlogin").val("Login"); return;}
            pwdsk=decryptchar(localStorage.en_login_sec,pin+msg);
            confkey=decryptchar(localStorage.en_login_conf,pin+msg)
            $.post("check.php",{pwd:String(CryptoJS.SHA512(String(CryptoJS.SHA512(pbkdf2_enc(pwdsk,JSsalt,500)+getcookie('username')))+"<?php echo $_SESSION['random_login_stamp']; ?>")),  user: getcookie('username')},function(msg){
                if(msg!=9) {$("#usepin").modal("hide");delpinstore();$("#user").focus();return;}
                setpwdstore(pwdsk,confkey,'<?php echo $GLOBAL_SALT_2; ?>');                
                window.location.href="./password.php";
            });
        });
    });
    $("#loginform").on('submit',function(e){ 
        e.preventDefault();
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
        $.post("check.php",{pwd:String(CryptoJS.SHA512(String(CryptoJS.SHA512(login_sig+user))+"<?php echo $_SESSION['random_login_stamp']; ?>")),  user: user},function(msg){ 
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
		}else
		if(msg==7){
			 	$("#blockip").show();
		}else
		if(msg==8){
			 	$("#accountban").show();
                $("#chk").attr("value", "Login");
				$("#chk").attr("disabled", false);
		}else{
                confkey=pbkdf2_enc(String(CryptoJS.SHA512(pwd+secretkey)),JSsalt,500);
                setCookie("username",user);
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