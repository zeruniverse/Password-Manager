<?php

require_once dirname(__FILE__).'/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__FILE__).'/PHPMailer/Exception.php';
require dirname(__FILE__).'/PHPMailer/PHPMailer.php';
require dirname(__FILE__).'/PHPMailer/SMTP.php';

/*  You can implement your own send_email API here with the method you want to send out verification email.
    You can also edit the code here to include your customized email body.

    If you choose to customize, please don't change the function name / function arg / function return:
    Function name: send_email
    Args:
        $address: Which email address this email should be sent TO.
        $verification_code: what is the verification code to be sent.
    Returns:
        Status (bool): true - email is sent successfully. false - failed to send out emails.
*/

function send_email($address, $verification_code)
{
    global $EMAIL_VERIFICATION_ENABLED, $GMAIL_ADDRESS, $GMAIL_PASSWORD;
    // Verification disabled. This function should not be called.

    if (!$EMAIL_VERIFICATION_ENABLED) {
        return false;
    }

    // add verification code to body
    $body = 'Your Password-Manager email verification code is: '.strval($verification_code).'<br /><br />';
    // tell user his password is already known by third-party.
    $body = $body.'<strong><span style="color:Red">You receive this email every time you login '.
            'from a new device. If you did not try to login, please change your login password '.
            'immediately. Your password is already known by some attackers.</span><strong>';

    // add do not reply message
    $body = $body.'<br /><hr /><strong>DO NOT REPLY</strong><br />';
    // add email origin message
    $body = $body.'<span style="color:Red">Please only trust email from '.$GMAIL_ADDRESS.'</span>';


    $mail = new PHPMailer();
    // configure an SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $GMAIL_ADDRESS;
    $mail->Password = $GMAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom($GMAIL_ADDRESS, 'Password-Manager Verification');
    $mail->addAddress($address, $address);
    $mail->Subject = strval($verification_code).' is your Password-Manager verification code';
    // Set HTML
    $mail->isHTML(TRUE);
    $mail->Body = $body;
    $mail->AltBody = 'Please use code '.strval($verification_code).' to login your Password-Manager.';

    // send the message
    if(!$mail->send()){
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    } else {
        return true;
    }
}