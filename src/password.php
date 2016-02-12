<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("function/sqllink.php");
require_once("function/basic.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();header("Location: ./");die();}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();header("Location: ./");die();}

//CHECK AGAIN, TO AVOID PASSWORD CHANGE IN ANOTHER BROWSER
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();header("Location: ./");die();}
echoheader();
?>
<style type="text/css">
@font-face {
	font-family: 'passwordshow';
	src:url('pw.ttf');
}
</style>
<script type="text/javascript" src="setlocalstorage.js"></script>
<script type="text/javascript">
var secretkey;
var accountarray=new Array();
function quitpwd()
{
    delpwdstore(); window.location.href="./logout.php";
}
</script>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>
<script type="text/javascript" src="pbkdf2.js"></script>
<script type="text/javascript" src="password.js"></script>
<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Password-Manager</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav" id="nav_links">
            <li id="nav-passwords" class="active"><a href="#pw">Passwords</a></li>
            <li id="nav-add"><a href="" data-toggle="modal" data-target="#add">Add Entry</a></li>
            <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Profile<span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="javascript: alert('You will need your CURRENT login password<?php if($ENABLE_PIN) echo ' and PIN';?> to unlock the backup file even if you change login password<?php if($ENABLE_PIN) echo ' or PIN';?> later. Write your CURRENT login password<?php if($ENABLE_PIN) echo ' and PIN';?> down or remember to generate a new backup file after each time you change the login password<?php if($ENABLE_PIN) echo ' or PIN';?>.');window.location.href='backup.php'">Back Up</a></li>
              <li><a href="" data-toggle="modal" data-target="#import">Import</a></li>
              <li><a href="" data-toggle="modal" data-target="#changepwd">Change Password</a></li>
            </ul>
            </li>
          </ul>
          <div class="navbar-right">
            <p class="navbar-btn"><a href="" onClick="quitpwd();" class="btn btn-info"><strong>Log Out</strong></a></p>
          </div>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
    
<div class="container theme-showcase">
      <div class="page-header" id="pw">
        <h1>Password Manager</h1>
	  </div>
    <!-- preload ttf -->
    <span style="display:none; font-family:passwordshow"><?php echo $DEFAULT_LETTER_USED; ?></span>
    <hr />
    <div id="waitsign">PLEASE WAIT WHILE WE ARE DECRYPTING YOUR PASSWORD...</div>
    <div id="pwdtable" style="display:none">
    <table class="table">
    <tr><th>Account</th><th>Password</th><th class="hidden-xs">Generate New Password</th><th class="hidden-xs">Delete this Password</th></tr>
    <?php
        $sql="SELECT * FROM `password` WHERE `userid`= ?";
        $res=sqlexec($sql,array($id),$link);
		while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
        $index=$i['index'];
		$name=$i['name'];
		$kss=decrypt($i['pwd'],$i['key']);
		echo "<tr><td><span class='accountname' dataid=".$index.">".$name.'</span></td><td><span passid="'.$index.'" enpassword="'.$kss.'"id="'.$index.'"><a href="javascript: clicktoshow(\''.$kss.'\',\''.$index.'\')">Click to see</a></span></td><td class="hidden-xs"><a href="javascript: refreshpw(\''.$index.'\')">Click to change</a></td><td class="hidden-xs"><a href="javascript: delepw(\''.$index.'\')">Click to delete</a></td></tr>';
		}
    ?>
   </table> 
	<hr />
    <div class="modal" tabindex="-1" role="dialog" id="add">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4>Add a new account</h4>
                </div>
                <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="newiteminput" class="control-label">Account (Item):</label>
                        <input class="form-control" id="newiteminput" type="text" />
                    </div>
                    <div class="form-group">
                        <label for="newiteminputpw" class="control-label">Password:</label>
                        <input class="form-control" id="newiteminputpw" type="text" placeholder="Leave blank to generate one"/>
                    </div>
                </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Dismiss</button>
                    <button type="button" class="btn btn-primary" id="newbtn">Add</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="import">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4>Import accounts</h4>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="importc" class="control-label">Copy all contents in your RAW backup file and paste them into the following box.</label>
                            <textarea class="form-control" id="importc"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Type of input&nbsp;&nbsp;</label>
                            <label class="radio-inline active"><input type="radio" name="importType" checked="checked">Backup</label>
                            <label class="radio-inline"><input type="radio" name="importType">CSV</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="importbtn" onClick="import_raw($('#importc').val());">Submit</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="changepwd">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4>Change Password<?php if($ENABLE_PIN) echo ' or PIN';?>(Danger Area)</h4>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="oldpassword" class="control-label">Old Password:</label>
                            <input id="oldpassword" class="form-control" type="password" />
                        </div>
                        <div class="form-group">
                            <label for="pwd" class="control-label">New Password:<small <?php if(!$ENABLE_PIN) echo 'style="display:none"';?>> (Input your old password if you only want to change PIN)</small></label>
                            <input id="pwd" class="form-control" type="password" />
                        </div>
                        <div class="form-group">
                            <label for="pwd1" class="control-label">New Password Again:</label>
                            <input id="pwd1" class="form-control" type="password" />
                        </div>
                        <div class="form-group" <?php if(!$ENABLE_PIN) echo 'style="display:none"';?>>
                            <label for="npin" class="control-label">New PIN:<small> (Input your old PIN here if you don't want to change PIN)</small></label>
                            <input id="npin" class="form-control" type="text" value="<?php if(!$ENABLE_PIN) echo $DEFAULT_PIN?>" />
                         </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Abort</button>
                    <button type="button" class="btn btn-primary" id="changepw">Save changes</button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<script type="text/javascript">
var ALPHABET='<?php echo $DEFAULT_LETTER_USED;?>';
var PWsalt='<?php echo $GLOBAL_SALT_2; ?>';
function sanitize_json(s){
    var t=s;
    t=t.replace(/\n/g,'')
    return t.replace(/\r/g,'');
}
function add_account(acc, pass, callback){
    var sk=secretkey;
    pass=gen_temp_pwd(getconfkey(PWsalt),PWsalt,String(CryptoJS.SHA512(acc)),ALPHABET,pass);
    pass=encryptchar(pass,sk);
    var name=encryptchar(acc,sk);
    $.post("insert.php",{name:name,newpwd:pass},callback);
}
function import_raw(json){
    json=JSON.parse(sanitize_json(json));
    if(json.status!="RAW_OK") {
        alert("INVALID RAW FILE");
        return;
    }
    $("#importbtn").attr("disabled",true);
    $("#importbtn").attr("value", "Processing...");
    $("#importc").attr("readOnly",true);
    function add_acc(acc,pass){
        if(acc==''||pass=='') {
            alert("one of account or password empty! will continue to process other accounts, check back after this finished"); return;
        }
        add_account(acc, pass, function(msg) { if(msg!=1) alert("Fail to add "+acc+", please try again manually later."); });
    }
    function process(){
        var aeskey=json.KEY;
        var x;
        for(x in json.data){
            add_acc(decryptchar(json.data[x][0],aeskey),decryptchar(json.data[x][1],aeskey));
        }
        location.reload(true);
    }
    setTimeout(process,50);
}
function import_csv(csv){
    $.getScript( 'js/jquery.csv.js', function() {
        accarray = $.csv.toObjects(csv);
        for (x in accarray) {
            acc = accarray[x]["name"];
            pass = accarray[x]["password"];
            if(acc==''||pass=='') {
                alert("one of account or password empty! will continue to process other accounts, check back after this finished"); continue;
            }
            add_account(accarray[x]["name"], accarray[x]["password"], function(msg) { if(msg!=1) alert("Fail to add "+acc+", please try again manually later."); });
        }
    });
}

$(document).ready(function(){
    function getskey(callback)
    {
        var secretkey0=getpwdstore('<?php echo $GLOBAL_SALT_2; ?>');
        callback(secretkey0);
    }
	function showtable(secretkey0)
    {
        secretkey=String(CryptoJS.SHA512(secretkey0+'<?php echo $GLOBAL_SALT_2; ?>'));
        if (secretkey0=="") quitpwd();
        else
        {
            var tempchar;
            $(".accountname").each(function(){               
            tempchar=decryptchar($(this).html(),secretkey);
            if (tempchar=="") tempchar="Oops, there's some errors!"
            $(this).html(tempchar)
            $(this).attr("class","namedone");
            accountarray[parseInt($(this).attr('dataid'))]=tempchar;
            });
            $("#waitsign").hide();
            $("#pwdtable").show();
        }
    }
    getskey(showtable);
$("#newbtn").click(function(){ 
	var newpwd;
	var sk=secretkey;
	if($("#newiteminput").val()=="") {alert("Account entry can't be empty!"); return;}
	$("#newbtn").attr("disabled",true);
	$("#newiteminput").attr("readonly",true);
	$("#newiteminputpw").attr("readonly",true);
    function process(){
        if($("#newiteminputpw").val()=='') newpwd=getpwd('<?php echo $DEFAULT_LETTER_USED; ?>',<?php echo $DEFAULT_LENGTH; ?>); else newpwd=$("#newiteminputpw").val();
        add_account($("#newiteminput").val(), newpwd,function(msg){ 
        if(msg==1) {alert("Add "+$("#newiteminput").val()+" successfully!");location.reload(true)} else alert("Fail to add "+$("#newiteminput").val()+", please try again.");
        $("#newiteminput").attr("readonly",false);
        $("#newbtn").attr("disabled",false);
        $("#newiteminputpw").attr("readonly",false);});
    }
    setTimeout(process,50);
	}); 
$("#changepw").click(function(){ 
    
    if(confirm("Your request will be processed on your browser, so it takes some time (up to #of_your_accounts * 10seconds). Do not close your window or some error might happen.\nPlease note we won't have neither your old password nor your new password. \nClick OK to confirm password change request."))
    {
        if (String(CryptoJS.SHA512($("#npin").val()))!=getpinsha()) if(!confirm("You are going to change your PIN, you must use your new PIN to login next time or you'll see incorrect passwords for your accounts! Confirm?")) return;
        if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){alert("The second password you input doesn't match the first one. Or your password is too weak (length should be at least 7)"); return;}
        $("#changepw").attr("disabled",true);
        $("#changepw").attr("value", "Processing...");
        function process(){
        var login_sig=String(pbkdf2_enc($("#oldpassword").val(),'<?php  echo $GLOBAL_SALT_1; ?>',500));
        if(secretkey!=String(CryptoJS.SHA512(login_sig+'<?php echo $GLOBAL_SALT_2; ?>'))) {alert("Incorrect Old Password!"); location.reload(); return;}
        var newpass=$("#pwd").val();
        login_sig=String(pbkdf2_enc(newpass,'<?php  echo    $GLOBAL_SALT_1; ?>',500));
        var newsecretkey=String(CryptoJS.SHA512(login_sig+'<?php echo $GLOBAL_SALT_2; ?>'));
        var postnewpass=pbkdf2_enc(login_sig,'<?php  echo    $GLOBAL_SALT_1; ?>',500);
        var newconfkey=pbkdf2_enc(String(CryptoJS.SHA512($("#npin").val()+newpass)),'<?php  echo $GLOBAL_SALT_1; ?>',500); 
        var x,raw_pass;
        var temps;
        var passarray=new Array();
        var accarray=new Array();
        for (x in accountarray)
        {
            accarray[x]=encryptchar(accountarray[x],newsecretkey);
            temps=$("[passid="+x+"]").attr("enpassword");
            raw_pass=decryptchar(temps,secretkey);
            if (raw_pass=="") {
                alert("FATAL ERROR WHEN TRYING TO DECRYPT ALL PASSWORDS");
                return;
            }
            raw_pass=get_orig_pwd(getconfkey(PWsalt),PWsalt,String(CryptoJS.SHA512(accountarray[x])),ALPHABET,raw_pass);
            raw_pass=gen_temp_pwd(newconfkey,PWsalt,String(CryptoJS.SHA512(accountarray[x])),ALPHABET,raw_pass);
            passarray[x]=encryptchar(raw_pass,newsecretkey);
        }
        $.post("changeuserpw.php",{newpass:postnewpass, passarray:JSON.stringify(passarray), accarray:JSON.stringify(accarray)},function(msg){ 
            if(msg==1) {alert("Change Password Successfully! Please login with your new password and PIN again.");quitpwd();} else {alert("Fail to change your password, please try again."); location.reload();}
        });
        }
        setTimeout(process,50);
	}
});
$('#add').on('shown.bs.modal', function () { $('#newiteminput').focus(); });
$('#import').on('shown.bs.modal', function () { $('#importc').focus(); });
$('#changepwd').on('shown.bs.modal', function () { $('#oldpassword').focus(); });
}); 

function clicktoshow(kss,id){ 
        if(kss=="") return;
        var thekey=decryptchar(kss,secretkey);
        var name=accountarray[parseInt(id)];
        if (thekey==""){
            $("#"+id).html("Oops, some error occurs!");
            return;
        }
        thekey = get_orig_pwd(getconfkey(PWsalt),PWsalt,String(CryptoJS.SHA512(name)),ALPHABET,thekey);
        $("#"+id).html('<span style="font-family:passwordshow"">'+thekey+'</span><a title="Hide" class="pwdOptionButton hidden-xs" href="javascript: clicktohide(\''+kss+'\',\''+id+'\')"><span class="glyphicon glyphicon-eye-close"></span></a><a title="Options" class="pwdOptionButton visible-xs" href="javascript: showOptions(\''+id+'\')"><span class="glyphicon glyphicon-wrench"></span></a><span class="pwdButtons" style="display:none;"><a title="Hide" href="javascript: clicktohide(\''+kss+'\',\''+id+'\')"><span class="glyphicon glyphicon-eye-close"></span></a><a title="Regenerate" href="javascript: refreshpw(\''+id+'\')"><span class="glyphicon glyphicon-refresh"></span></a><a title="Delete" href="javascript: delepw(\''+id+'\')"><span class="glyphicon glyphicon-trash"></span></a></span>');
} 
function clicktohide(kss,id){
    $("#"+id).html('<a href="javascript: clicktoshow(\''+kss+'\',\''+id+'\')">Click to see</a>'); 
}
function showOptions(id) {
    $("#"+id+" .pwdButtons").show();
    $("#"+id+" .pwdOptionButton").remove();
}
function refreshpw(index){
	var newpwd;
	var sk=secretkey;
    var name=accountarray[parseInt(index)];
	if(confirm("Do you really want to generate a new password for: "+name+"? (ATTENTION: this is irreversible, you'll lose your old password)"))
	{
		newpwd=encryptchar(getpwd('<?php echo $DEFAULT_LETTER_USED; ?>',<?php echo $DEFAULT_LENGTH; ?>),secretkey);
		$.post("change.php",{newpwd:newpwd,index:index},function(msg){ 
         if(msg==1) {alert("Password for "+name+" updated!");location.reload(true)} else alert("Fail to update password for "+name+", please try again.");
		 }); 
}}
function delepw(index)
{   
    var name=accountarray[parseInt(index)];
	if(confirm("Are you sure you want to delete password for "+name+"? (ATTENTION: this is irreversible)"))
	{
		$.post("delete.php",{index:index},function(msg){ 
         if(msg==1) {alert("delete "+name+" successfully");location.reload(true);} else alert("Fail to delete "+name+", please try again.");
	 }); 
	 }
}
</script>
<?php echofooter();?>
