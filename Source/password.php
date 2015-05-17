<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("sqllink.php");
require_once("function/basic.php");
require_once("function/encryption.php");
$link=sqllink();
if(!$link) {session_destroy();header("Location: ./");die();}
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
$id = $_SESSION['userid'];
if($usr==""||$pw=="" || $id=="")  {session_destroy();header("Location: ./");die();}
$sql="SELECT * FROM `pwdusrrecord` WHERE `username`= ? AND `password`= ? AND `id`= ?";
$res=sqlexec($sql,array($usr,$pw,$id),$link);
$record= $res->fetch(PDO::FETCH_ASSOC);
if($record==FALSE) {session_destroy();header("Location: ./");die();}

setcookie("pwdrecord".$usr,$pw, time()+5160000);
echoheader(2);
?>
<style type="text/css">
@font-face {
	font-family: 'passwordshow';
	src:url('pw.ttf');
}
</style>
<script type="text/javascript" src="setcookies.js"></script>
<script type="text/javascript">
var secretkey;
var accountarray=new Array();
function quitpwd(usr)
{
	if(usr=="") {delpwdstore(); window.location.href="./logout.php";} else {delpwdstore();document.cookie="pwdrecord"+usr+"=;expires="+(new Date(0)).toGMTString(); window.location.href="./logout.php";}
}
</script>
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>


<script type="text/javascript" src="password.js"></script>
<div class="container theme-showcase">
      <div class="page-header">
        <h1>Password Manager</h1>
	  </div>
      <span style="display:none; font-family:passwordshow">abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ</span>
	<div><input class="btn btn-sm btn-default" type="button" onClick="window.location.href='backup.php'" value="Back Up My Data"/>&nbsp;|&nbsp;<input class="btn btn-sm btn-info" type="button" onClick="quitpwd('');" value="Log Out"/>&nbsp;<input class="btn btn-sm btn-danger" type="button" onClick="quitpwd('<?php echo $usr;?>');" value="Log Out and Untrust this Device"/></form></div>
    <hr />
    <div id="waitsign">PLEASE WAIT WHILE WE ARE DECRYPTING YOUR PASSWORD...</div>
    <div id="pwdtable" style="display:none">
    <table class="table">
    <tr><th>Account</th><th>Password</th><th>Generate New Password</th><th>Delete this Password</th></tr>
    <?php
        $sql="SELECT * FROM `password` WHERE `userid`= ?";
        $res=sqlexec($sql,array($id),$link);
		while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
        $index=$i['index'];
		$name=$i['name'];
		$kss=decrypt($i['pwd'],$i['pubkey']);
		echo "<tr><td><span class='accountname' dataid=".$index.">".$name.'</span></td><td><span passid="'.$index.'" enpassword="'.$kss.'"id="'.$index.'"><a href="javascript: clicktoshow(\''.$kss.'\',\''.$index.'\')">Click to see</a></span></td><td><a href="javascript: refreshpw(\''.$index.'\')">Click to change</a></td><td><a href="javascript: delepw(\''.$index.'\')">Click to delete</a></td></tr>';
		}
    ?>
   </table> 
	<hr />
    <div class="jumbotron">
      	<p><h2>Add a new account</h2></p>
        <form>
             <p>Account (Item):<input id="newiteminput" type="text" /></p>
             <p>Password:<input id="newiteminputpw" type="text" placeholder="Leave blank to generate one"/></p>
             <p><input type="button" class="btn btn-sm btn-primary" id="newbtn"  value="Submit"/></p>
        </form>
      </div>
    <div class="jumbotron">
      	<p><h2>Change Password (Danger Area)</h2></p>
        <form>
             <p>Old Password:<input id="oldpassword" type="password" /></p>
             <p>New Password:<input id="pwd" type="password" /></p>
             <p>New Password Again:<input id="pwd1" type="password" /></p>
             <p><input type="button" class="btn btn-sm btn-primary" id="changepw"  value="Submit"/></p>
        </form>
      </div>

</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    function getskey(callback)
    {
        var secretkey0=getpwdstore();
        callback(secretkey0);
    }
	function showtable(secretkey0)
    {
        secretkey=secretkey0;
        if (secretkey=="") quitpwd("");
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
	if($("#newiteminputpw").val()=='') newpwd=getpwd(); else newpwd=$("#newiteminputpw").val();
	newpwd=encryptchar(newpwd,sk);
	var name=encryptchar($("#newiteminput").val(),sk);
	$.post("insert.php",{csfds:'sdf', name:name,newpwd:newpwd},function(msg){ 
    if(msg==1) {alert("Add "+$("#newiteminput").val()+" successfully!");setpwdstore(secretkey);location.reload(true)} else alert("Fail to add "+$("#newiteminput").val()+", please try again.");
    $("#newiteminput").attr("readonly",false);
	$("#newbtn").attr("disabled",false);
	
	$("#newiteminputpw").attr("readonly",false);});
	}); 
$("#changepw").click(function(){ 
    
    if(confirm("Your request will be processed on your browser, so it takes some time. Do not close your window or some error might happen.<br />Please note we won't have neither your old password nor your new password. <br />Click OK to confirm password change request."))
	{
        if ($("#pwd").val()!=$("#pwd1").val() || $("#pwd").val().length<7){alert("The second password you input doesn't match the first one. Or your password is too weak (length should be at least 7)"); return;}
        if(secretkey!='pw.zzeyu.com'+$("#oldpassword").val()+'zzeyu.com') {alert("Incorrect Old Password!"); return;}
        $("#changepw").attr("disabled",true);
        $("#changepw").attr("value", "Processing...");
        var newpass=$("#pwd").val();
        var newsecretkey='pw.zzeyu.com'+newpass+'zzeyu.com';
        var salt="lksandoiniNOIJONDS98U9328URF0s9d0s-f1023430r9f0-0f1029403ie0fcj0sdbidbv+_+13=24=23f-=gONOINCOIND3RJF9UHOSIJIOijnksdnkvp";
        var postnewpass=String(CryptoJS.SHA512("password manager:"+newpass+"by jeffery"+salt));
        var x;
        var temps;
        var passarray=new Array();
        var accarray=new Array();
        for (x in accountarray)
        {
            accarray[x]=encryptchar(accountarray[x],newsecretkey);
            temps=$("[passid="+x+"]").attr("enpassword");
            passarray[x]=encryptchar(decryptchar(temps,secretkey),newsecretkey);
        }
        $.post("changeuserpw.php",{csfds:'sdf', newpass:postnewpass, passarray:JSON.stringify(passarray), accarray:JSON.stringify(accarray)},function(msg){ 
            if(msg==1) {alert("Change Password Successfully! Please login again.");quitpwd('<?php echo $usr; ?>');} else alert("Fail to change your password, please try again.");
        });
	}
});  
}); 

function clicktoshow(kss,id){ 
		if(kss=="") return;
        var thekey=decryptchar(kss,secretkey);
		if(thekey!="") $("#"+id).html('<span style="font-family:passwordshow">'+thekey+'</span>'); else $("#"+id).html("Oops, some error occurs!");
} 
function refreshpw(index){
	var newpwd;
	var sk=secretkey;
    var name=accountarray[parseInt(index)];
	if(confirm("Do you really want to generate a new password for: "+name+"? (ATTENTION: this is irreversible, you'll lose your old password)"))
	{
		newpwd=encryptchar(getpwd(),secretkey);
		$.post("change.php",{csfds:'sdf', newpwd:newpwd,index:index},function(msg){ 
         if(msg==1) {alert("Password for "+name+" updated!");setpwdstore(secretkey);location.reload(true)} else alert("Fail to update password for "+name+", please try again.");
		 }); 
}}
function delepw(index)
{   
    var name=accountarray[parseInt(index)];
	if(confirm("Are you sure you want to delete password for "+name+"? (ATTENTION: this is irreversible)"))
	{
		$.post("delete.php",{csfds:'sdf', index:index},function(msg){ 
         if(msg==1) {alert("delete "+name+" successfully");setpwdstore(secretkey);location.reload(true)} else alert("Fail to delete "+name+", please try again.");
	 }); 
	 }
}
</script>
<?php echofooter();?>