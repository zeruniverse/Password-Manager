<?php

require_once dirname(__FILE__).'/config.php';

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
    // Verification disabled. This function should not be called.
    if (!$EMAIL_VERIFICATION_ENABLED) {
        return false;
    }

    // add verification code to body
    $body = 'Your Password-Manager email verification code is :'.strval($verification_code).'<br /><br />';
    // tell user his password is already known by third-party.
    $body = $body.'<strong><span style="color:Red">You receive this email every time you login '.
            'from a new device. If you did not try to login, please change your login password '.
            'immediately. Your password is already known by some attackers.</span><strong>';

    // add do not reply message
    $body = $body.'<br /><hr /><strong>DO NOT REPLY</strong><br />';
    // add email origin message
    $body = $body.'<span style="color:Red">Please only trust email from '.$SENDGRID_FROM_ADDRESS.'</span>';

    $url = 'https://api.sendgrid.com/';
    $user = $SENDGRID_USER_NAME;
    $pass = $SENDGRID_PASSWORD;

    $params = [
        'api_user'  => $user,
        'api_key'   => $pass,
        'to'        => $address,
        'subject'   => 'Password-Manager Verification: '.strval($verification_code),
        'html'      => $body,
        'from'      => $SENDGRID_FROM_ADDRESS,
    ];

    $request = $url.'api/mail.send.json';

    // Generate curl request
    $session = curl_init($request);
    // Tell curl to use HTTP POST
    curl_setopt($session, CURLOPT_POST, true);
    // Tell curl that this is the body of the POST
    curl_setopt($session, CURLOPT_POSTFIELDS, $params);
    // Tell curl not to return headers, but do return the response
    curl_setopt($session, CURLOPT_HEADER, false);
    // Tell PHP not to use SSLv3 (instead opting for TLS)
    curl_setopt($session, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1_2');
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

    // obtain response
    $response = curl_exec($session);
    curl_close($session);
    if ((json_decode($response)->{'message'}) == 'success') {
        return true;
    } else {
        error_log('Failed to send email! '.$response, 0);

        return false;
    }
}
