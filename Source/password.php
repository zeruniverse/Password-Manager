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
require_once("../../functionzzeyu/sqllink.php");
require_once("../../functionzzeyu/basic.php");
$link=sqllink();
if(!$link) die();
$usr=$_SESSION['user'];
$pw=$_SESSION['pwd'];
if($usr==""||$pw=="")  {session_destroy();header("Location: ./");die();}
$sql="SELECT * FROM `pwdusrrecord` WHERE `usr`='$usr' AND `pwd`='$pw'";
$rett=mysql_query($sql,$link);
$num= mysql_num_rows($rett);
if($num==0) {session_destroy();header("Location: ./");die();}
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
var secretkey="";
function quitpwd(usr)
{
	if(usr=="") {delpwdcookies(); window.location.href="./logout.php";} else {delpwdcookies();document.cookie="pwdrecord"+usr+"=;expires="+(new Date(0)).toGMTString(); window.location.href="./logout.php";}
}

function showtable()
{
	if($("#secretkey").val()=='') {alert("不能为空！");return ;}
	$("#secretkey").attr("readonly",true);
	$("#keybtn").attr("onClick","changesk();");
	$("#keybtn").attr("value","重新输入私钥");
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
<script type="text/javascript" src="aes.js"></script>
<script type="text/javascript" src="sha512.js"></script>


<script type="text/javascript" src="password.js"></script>
<div class="container theme-showcase">
      <div class="page-header">
        <h1>密码管理器</h1>
	  </div>
      <span style="display:none; font-family:passwordshow">abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ</span>
	<div>请输入私钥：<form><input id="secretkey" type="password" />&nbsp;<input type="button" id="keybtn" class="btn btn-sm btn-success" onClick="showtable();" value="确认"/>&nbsp;|&nbsp;<input class="btn btn-sm btn-default" type="button" onClick="window.location.href='backup.php'" value="备份数据"/>&nbsp;|&nbsp;<input class="btn btn-sm btn-info" type="button" onClick="quitpwd('');" value="安全退出"/>&nbsp;<input class="btn btn-sm btn-danger" type="button" onClick="quitpwd('<?php echo $usr;?>');" value="退出并取消信任此设备"/></form></div>
    <hr />
    <div id="pwdtable" style="display:none">
    <table class="table">
    <tr><th>密码名称</th><th>密码</th><th>更新此密码</th><th>删除此密码</th></tr>
    <?php
		$sql="SELECT * FROM `password` WHERE `usr`='$usr'";
		$rett=mysql_query($sql, $link);
		while ($i = mysql_fetch_array($rett)){ //返回查询结果
			$index=$i['index'];
			$name=$i['name'];
			$kss=decrypt($i['pwd'],$i['pubkey']);
			echo "<tr><td>".$name.'</td><td><span id="'.$index.'"><a href="javascript: clicktoshow(\''.$kss.'\',\''.$index.'\')">点击查看密码</a></span></td><td><a href="javascript: refreshpw(\''.$index.'\',\''.$name.'\',\''.$kss.'\')">点击更新</a></td><td><a href="javascript: delepw(\''.$index.'\',\''.$name.'\')">点击删除</a></td></tr>';
		}
mysql_free_result($rett);
// 关闭连接
mysql_close($link);
?></table> 
	<hr />
    <div class="jumbotron">
      	<p><h2>新增一个项目</h2></p>
        <form>
             <p>输入项目名称：<input id="newiteminput" type="text" /></p>
             <p>输入对应密码：<input id="newiteminputpw" type="text" placeholder="留空则随机产生"/></p>
             <p><input type="button" class="btn btn-sm btn-primary" id="newbtn"  value="增加"/></p>
        </form>
      </div>

</div>
</div>
<script type="text/javascript">
$(document).ready(function(){ 
	var gett=getpwdcookies();
　　if(gett!="")
{
	$("#secretkey").attr("readonly",true);
	$("#secretkey").attr("value",gett);
	$("#keybtn").attr("onClick","changesk();");
	$("#keybtn").attr("value","重新输入私钥");
	secretkey=gett;
	$("#pwdtable").show();
}
$("#newbtn").click(function(){ 
	var newpwd;
	var sk=secretkey;
	if($("#newiteminput").val()=="") {alert("项目名不能为空！"); return;}
	$("#newbtn").attr("disabled",true);
	$("#newiteminput").attr("readonly",true);
	$("#newiteminputpw").attr("readonly",true);
	if($("#newiteminputpw").val()=='') newpwd=getpwd(); else newpwd=$("#newiteminputpw").val();
	newpwd=encryptchar(newpwd,sk);
	var name=$("#newiteminput").val();
	$.post("insert.php",{csfds:'sdf', name:name,newpwd:newpwd},function(msg){ 
    if(msg==1) {alert("新增"+$("#newiteminput").val()+"成功！");setpwdcookies(secretkey);location.reload(true)} else alert("新增"+$("#newiteminput").val()+"失败！");
    $("#newiteminput").attr("readonly",false);
	$("#newbtn").attr("disabled",false);
	
	$("#newiteminputpw").attr("readonly",false);});
	}); 
}); 
function clicktoshow(kss,id){ 
		if(kss=="") return;
        var thekey=decryptchar(kss,secretkey);
		if(thekey!="") $("#"+id).html('<span style="font-family:passwordshow">'+thekey+'</span>'); else $("#"+id).html("私钥不匹配");
} 
function refreshpw(index,name,oldpw){
	var newpwd;
	var sk=secretkey;
	if(confirm("您真的要产生一个"+name+"的新密码吗？(旧密码将本地解锁后上传服务器)"))
	{
		oldpw=decryptchar(oldpw,sk);
		if(oldpw=="" && !confirm("您现在的私钥与此密码不匹配，无法解锁密码。若强制更新，服务器将不保存您上次密码。")) return;
		newpwd=encryptchar(getpwd(),secretkey);
		$.post("change.php",{csfds:'sdf', newpwd:newpwd,name:name,index:index,oldpw:oldpw},function(msg){ 
         if(msg==1) {alert("产生"+name+"新密码成功！");setpwdcookies(secretkey);location.reload(true)} else alert("产生"+name+"新密码失败！");
		 }); 
}}
function delepw(index,name)
{
	if(confirm("您真的要删除"+name+"的密码吗？(该操作无法撤销)"))
	{
		$.post("delete.php",{csfds:'sdf', name:name,index:index},function(msg){ 
         if(msg==1) {alert("删除"+name+"密码成功！");setpwdcookies(secretkey);location.reload(true)} else alert("删除"+name+"密码失败！");
	 }); 
	 }
}
</script>
<?php echofooter();?>