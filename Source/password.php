<?php 
function encrypt($data, $key)
{
	$key	=	md5($key);
	$char='';
	$str='';
    $x		=	0;
    $len	=	strlen($data);
    $l		=	strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}
function safe($str)
{
	$len=strlen($str);
	$re='';
	for($i=0;$i<$len;$i++){
	$c=substr($str,$i,1);
	if(ord('0')<=ord($c) && ord('9')>=ord($c))$re=$re.$c;
	if(ord('a')<=ord($c) && ord('z')>=ord($c))$re=$re.$c;
	if(ord('A')<=ord($c) && ord('Z')>=ord($c))$re=$re.$c;
	}
	return $re;
}
function decrypt($data, $key)
{
	$key = md5($key);
    $x = 0;
	$char='';
	$str='';
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l) 
        {
        	$x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}?>
<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("sqllink.php");
	$link=sqllink()

if(!$link) {
    session_destroy();header("Location: ./");die();
}

$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
if($usr==""||$pw=="")  {session_destroy();header("Location: ./");die();}
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr' AND `pwd`='$pw'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {session_destroy();header("Location: ./");die();}
setcookie("pwdrecord".$usr,$pw, time()+5160000);
?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>ZerUniverse</title>
<script type="text/javascript" src="setcookies.js"></script>
<script type="text/javascript">
var secretkey="";
function quitpwd(usr)
{
	if(usr=="") {delpwdcookies(); window.location.href="./logout.php";} else {delpwdcookies();document.cookie="pwdrecord"+usr+"=;expires="+(new Date(0)).toGMTString(); window.location.href="./logout.php";}
}

function showtable()
{
	if($("#secretkey").val()=='') {alert("can't be empty！");return ;}
	$("#secretkey").attr("readonly",true);
	$("#keybtn").attr("onClick","changesk();");
	$("#keybtn").attr("value","Input Secret Key");
	secretkey=$("#secretkey").val();
	$("#pwdtable").show();
	setpwdcookies(secretkey);
	
}
function changesk()
{
	delpwdcookies();
	location.reload(false);
	
}
</script>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script type="text/javascript" src="aes.js"></script>



<script type="text/javascript" src="password.js"></script>
</head>
<body>
<!-- Begin Wrapper -->
<div style="padding:50px;">
	
	<!-- Begin Content -->
	<div>
	<h1 class="title">Password Manager</h1>
	<div class="line"></div>
	<div>Input secret key：<form><input id="secretkey" type="password" /><input type="button" id="keybtn" onClick="showtable();" value="OK"/>&nbsp;|&nbsp;<input type="button" onClick="quitpwd('');" value="logout"/><input type="button" onClick="quitpwd('<?php echo $usr;?>');" value="Logout and forget this device"/></div>
    <div class="line"></div>
    <div id="pwdtable" style="display:none">
    <table>
    <tr><th>Title</th><th>Password</th><th>Generate a new</th><th>Delete</th></tr>
    <?php
		$sql="SELECT * FROM `password` WHERE `usr`='$usr'";
		$rett=mysql_query($sql, $link);
		while ($i = mysql_fetch_array($rett)){ 
			$index=$i['index'];
			$name=$i['name'];
			$kss=decrypt($i['pwd'],$i['pubkey']);
			echo "<tr><td>".$name.'</td><td><span id="'.$index.'"><a href="javascript: clicktoshow(\''.$kss.'\',\''.$index.'\')">Click to see</a></span></td><td><a href="javascript: refreshpw(\''.$index.'\',\''.$name.'\',\''.$kss.'\')">Click to generate a new password</a></td><td><a href="javascript: delepw(\''.$index.'\',\''.$name.'\')">click to delete</a></td></tr>';
		}
mysql_free_result($rett);
mysql_close($link);
?></table> <div class="line"></div><div class="toggle">
        <h2 class="trigger">+ Add a new password</h2>
        <div class="togglebox">
          <div>
            <form>
             Title:<input id="newiteminput" type="text" />
             <input type="button" id="newbtn"  value="Add"/>
             </form>
             </div>
             </div></div></div>
    <!-- Begin Footer -->
    <div id="footer">
  	&copy;Jeffery Zhao; 2014. Alpha<br /><br />
    </div>
    <!-- End Footer -->
    
    
	</div>
	<!-- End Content -->

</div>
<!-- End Wrapper -->
<div class="clear"></div>


</body>
</html>
<script type="text/javascript">
$(document).ready(function(){ 
	var gett=getpwdcookies();
　　if(gett!="")
{
	$("#secretkey").attr("readonly",true);
	$("#secretkey").attr("value",gett);
	$("#keybtn").attr("onClick","changesk();");
	$("#keybtn").attr("value","Reinput secret key");
	secretkey=gett;
	$("#pwdtable").show();
}
$("#newbtn").click(function(){ 
	var newpwd;
	var sk=secretkey;
	if($("#newiteminput").val()=="") {alert("can't be empty"); return;}
	$("#newbtn").attr("disabled",true);
	$("#newiteminput").attr("readonly",true);
	newpwd=encryptchar(getpwd(),sk);
	var name=$("#newiteminput").val();
	$.post("insert.php",{csfds:'sdf', name:name,newpwd:newpwd},function(msg){ 
    if(msg==1) {alert("add "+$("#newiteminput").val()+"successfully！");setpwdcookies(secretkey);location.reload(true)} else alert("add "+$("#newiteminput").val()+" fail！");
    $("#newiteminput").attr("readonly",false);
	$("#newbtn").attr("disabled",false);});
	}); 
}); 
function clicktoshow(kss,id){ 
		if(kss=="") return;
        var thekey=decryptchar(kss,secretkey);
		if(thekey!="") $("#"+id).html(thekey); else $("#"+id).html("Secret key not matched");
} 
function refreshpw(index,name,oldpw){
	var newpwd;
	var sk=secretkey;
	if(confirm("Confirm generate a new password for "+name+"?"))
	{
		oldpw=decryptchar(oldpw,sk);
		if(oldpw=="" && !confirm("Secret key not matched, server can't record your old password. Please do this at your own risk")) return;
		newpwd=encryptchar(getpwd(),secretkey);
		$.post("change.php",{csfds:'sdf', newpwd:newpwd,name:name,index:index,oldpw:oldpw},function(msg){ 
         if(msg==1) {alert("generate "+name+" successfully！");setpwdcookies(secretkey);location.reload(true)} else alert("generate "+name+" failed！");
		 }); 
}}
function delepw(index,name)
{
	if(confirm("Confirm delete "+name+"?"))
	{
		$.post("delete.php",{csfds:'sdf', name:name,index:index},function(msg){ 
         if(msg==1) {alert("Delete"+name+"successfully！");setpwdcookies(secretkey);location.reload(true)} else alert("Delete "+name+" fail！");
	 }); 
	 }
}
</script>