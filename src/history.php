<?php
session_start(); 
if(!isset($_SESSION["loginok"]) || $_SESSION['loginok']!=1) {session_destroy();header("Location: ./");die();}
require_once("function/sqllink.php");
require_once("function/basic.php");
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
<div class="container theme-showcase" style="margin-top:-30px;">
    <p id="placeholder">PLEASE WAIT...</p>
    <div id="maindiv" style="display:none">
    <div class="page-header">
        <h1>Trusted Devices</h1>
    </div>
    <table class="table">
    <tr><th>Device Type</th><th>Set Time</th><th>Untrust (Disable PIN)</th></tr>
    <?php
        $sql="SELECT `device`,UNIX_TIMESTAMP(`createtime`) AS `createtime`,`ua` FROM `pin` WHERE `userid`= ?";
        $res=sqlexec($sql,array($id),$link);
		while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
            $did=$i['device'];
            $ctime=(int)$i['createtime'];
            $ua=$i['ua'];
            echo "<tr><td class='uacell'>".$ua."</td><td class='timestampcell'>".gmdate('Y-m-d-H-i-s',$ctime).'[1aaaaaaa'."</td><td><a href='javascript: unsetpin(\"".$did."\")'>Untrust this device</a></td></tr>";
		}
    ?>
    </table>
    <div class="page-header">
        <h1>Login History</h1>
    </div>
    <p>Red entries indicate password error (i.e. error try)</p>
    <table class="table">
    <tr><th>Device Type</th><th>Login IP</th><th>Login Time</th></tr>
    <?php
        $sql="SELECT `ip`,`ua`,`outcome`,UNIX_TIMESTAMP(`time`) AS `time` FROM `history` WHERE `userid`= ? ORDER BY `id` DESC LIMIT 60";
        $res=sqlexec($sql,array($id),$link);
		while ($i = $res->fetch(PDO::FETCH_ASSOC)){ 
            $ip=$i['ip'];
            $ua=$i['ua'];
            $ctime=(int)$i['time'];
            if((int)$i['outcome']==0)
                $color=' style="color:red"';
            else
                $color='';
            echo "<tr".$color."><td class='uacell'>".$ua."</td><td>".$ip."<td class='timestampcell'>".gmdate('Y-m-d-H-i-s',$ctime).'[1aaaaaaa'."</td></tr>";
		}
    ?>
    </table>   
    </div>
</div>
<script type="text/javascript" src="ua-parser.min.js"></script>
<script type="text/javascript">
function timeConverter(utctime){
  var a = new Date();
  var p=utctime.split("[");
  var g=p[0].split('-');
  a.setUTCFullYear(g[0]);
  a.setUTCMonth(g[1]);
  a.setUTCDate(g[2])
  a.setUTCHours(g[3]);
  a.setUTCMinutes(g[4]);
  a.setUTCSeconds(g[5]);
  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; 
  var year = String(a.getFullYear());
  var month = months[a.getMonth()-1];
  var date = String(a.getDate());
  var hour = String(a.getHours());
  var min = String(a.getMinutes());
  var sec = String(a.getSeconds());
  if(hour.length==1) hour = '0'+hour;
  if(min.length==1) min = '0'+min;
  if(sec.length==1) sec = '0'+sec;
  var time = month + ' '+date + ', ' + year + ' ' + hour + ':' + min + ':' + sec ;
  return time;
}
$(document).ready(function(){
    var parser = new UAParser();
    var uastring;
    var nowtime;
    $( ".uacell" ).each(function() {
       uastring=$(this).html();
       parser.setUA(uastring);
       $(this).html(parser.getBrowser().name+' '+parser.getBrowser().version+'; '+parser.getOS().name+' '+parser.getOS().version+'; '+parser.getDevice().model+' '+parser.getCPU().architecture);
    });
    $( ".timestampcell" ).each(function(){
       nowtime=timeConverter($(this).html());
       $(this).html(nowtime);
    });
    $("#placeholder").hide();
    $("#maindiv").show();
});
function unsetpin(devicex)
{
    $.post("deletepin.php",{user:"<?php echo $usr;?>",device:devicex},function(msg){location.reload(true);});
}
</script>
<?php echofooter();?>