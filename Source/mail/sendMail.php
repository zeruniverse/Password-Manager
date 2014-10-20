<?php
function encryptmail($data, $key)
{
	$key=$key+6;
	$key	=	md5($key);
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
/*function decrypt($data, $key)
{
	$key=$key+6;
	$key = md5($key);
    $x = 0;
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
}*/	
?>
<?php
require_once('class.phpmailer.php');
function sendmail($address,$subject,$content)
{
require_once("sqllink.php");
	
$link=sqllink();
if(!$link) return false;
$rrrrr=mysql_query("select * from banmail where address='$address'",$link);
if(mysql_num_rows($rrrrr)!=0) return false;
$key=(string)mt_rand (0,9999999);
$banpin=encryptmail($address, $key);
$mail= new PHPMailer();

$body= $content.'<br /><hr /><strong>DO NOT REPLY</strong><br /><span style="color:Blue">本邮件由系统自动发送</span><br /><span style="color:Red">This mail is automatically sent.</span><br /><span style="color:Blue">您之所以收到这封邮件是因为您填写了您的邮箱并申请发送</span><br /><span style="color:Red">You receive this mail because you input your mail address and apply some services</span><br /><span style="color:Blue">如果您没有这么做，您不必理会本邮件</span><br /><span style="color:Red">If this is a wrongly sent E-mail, please ignore it.</span><br /><span style="color:Blue">如果您持续收到这种错发的邮件，请<a href="http://zzeyu.com/banmailaddress.php?key='.$key.'&pin='.$banpin.'&address='.$address.'">点击这里</a>,系统将永久阻止向您发送邮件。</span><br /><span style="color:Red">If you continue receiving these unwanted mails, please <a href="http://zzeyu.com/banmailaddress.php?key='.$key.'&pin='.$banpin.'&address='.$address.'">Click Here</a>,our system will no longer send you mails. Thank you for support!<br /></span>';


$mail->IsSMTP();

$mail->Host       = "smtp.126.com";
$mail->SMTPDebug  = 0;

$mail->SMTPAuth   = true;

$mail->Username   = "";

$mail->Password   = "";

$mail->CharSet  = "utf-8";

$mail->Subject    = $subject;
//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";

$mail->SetFrom('', '');

$mail->MsgHTML($body);

$mail->AddAddress($address, $address);
if(!$mail->Send()) {
  return false;
} else {
  return true;
}}
?>