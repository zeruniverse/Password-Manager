<?php
require_once('function/basic.php');
echoheader(2);
?>
<div class="container theme-showcase">
      <div class="page-header">
        <h1>Password Manager</h1>
	  </div>
	  <p>注册协议:<br /></p>
      <div class="alert alert-info alert-dismissable">
    密码管理器可以帮助您为每一个账号生成一个不同的强密码并管理这些密码。您只需要记住您登陆密码管理器的密码即可在这里获取全部密码。<br />
    0.定义：本协议是您（用户）与zzeyu.com间就本产品达成的具有法律效力的约定。如果您继续注册，即表明您完全同意以下任何一条协议，并愿意为一切因您违反本协议造成的后果承担全部责任。<br />
    1.您理解我们只在服务器中储存您的密码签名，签名无法反向计算出密码，因此如果您丢失密码，我们无法为您恢复。<br />
    2.您理解并同意一旦您忘记密码，您的全部数据都将丢失，任何人都无法恢复，我们对用户丢失密码造成的一切损失免责。<br />
    3.您理解并同意，您在本站托管的密码安全性取决于您设置的密码强度，因您使用弱密码造成的损失本站免责（我们推荐您将密码设置为11位以上）。<br />
    4.您理解并同意，本项目的加密解密完全在您的浏览器中进行，因您电脑中有病毒窥屏，或者浏览器恶意篡改代码造成的密码被盗，本站不负任何责任。<br />
    5.基于第4条，我们强烈建议您只在您确认安全的电脑中登陆这个站点，或者使用您的智能手机登陆。<br />
    6.基于第1，2条，我们强烈建议您在纸质媒介中记录您的登陆密码并妥善保管。<br />
    7.这是一个免费项目，基于此：<br />
    <ul>
    <li>用户理解并同意网站可能因各种原因短时间无法使用，zzeyu.com不必对此承担责任。</li>
    <li>用户理解并同意，当本站遭到攻击时，虽然您的数据被加密储存于服务器，攻击者无法破译您的数据，但其仍可能破坏您的数据，用户有责任定期备份自己的账户数据。</li>
    <li>用户禁止以任何方式通过此项目盈利，否则将承担一切后果</li>
   </ul>
    8.违规惩罚<br />
    <ul>
    <li>违规者应承担一切违规后果，用户同意本站对用户的违规行为免责。</li>
    <li>本站保留界定违规行为的权利与本协议中各项规定的解释权。</li>
    <li>本站保留针对违规行为起诉用户的权利</li>
  </ul>
    </div>
    <div class="alert alert-success alert-dismissable">
    Password manager helps you generate different strong passwords for each of your accounts. You only need to remember your password to login the password manager.<br />
    <br />
    Term of Service<br />
    1.You understand we only keep the signature of your password in the server, which can't be used to calculate your password. This is to say, no one can get access to your data without your password.<br />
    2.Based on 1, no one can recover your data once you lost your password. And you agree that we're not responsible for loss caused by this issue (you forget your password).<br />
    3.Based on 1 and 2, we strongly recommend you to write your password down on a paper and keep it in your purse.<br />
    4.Though the encryption and decryption of your password is on your browser, malwares/virus installed in your computer can still cause password leak (e.g. they record your screen)<br />
    5.Based on 4, we strongly recommend you only login this website on devices you trust.<br />
    6.This is a free service, and we don't allow unauthorized commercial use.<br />
    7.We aren't responsible for temporally unavailable of this service (e.g. power outlet)<br />
    8.Users are fully responsible for loss caused by violating this TOS.<br />
    </div>
    <div style="text-align:center"> <button disabled=true id="rrr" onclick="window.location.href='regc.php?reg=ok'">我已阅读并同意上述协议[Agree TOS and Sign Up](20)</button></div>
<script>
timeread=20;
setInterval("xxxxx()",1000);
function xxxxx()
{
	if(timeread>0)
	{timeread=timeread-1;
	$('#rrr').html("我已阅读并同意上述协议[Agree TOS and Sign Up]("+timeread+")");
	} else {$('#rrr').html("我已阅读并同意上述协议[Agree TOS and Sign Up]"); $('#rrr').attr('disabled',false);}
}
</script>
</div>
<?php echofooter();?>