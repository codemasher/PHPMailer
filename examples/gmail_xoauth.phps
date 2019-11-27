<?php
/**
 * This example shows how to send via Google's Gmail servers using XOAUTH2 authentication.
 */

//Import PHPMailer classes into the global namespace
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\SMTPMailer;

require_once __DIR__.'/common.php';

// Alias the League Google OAuth2 provider class

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//Set the hostname of the mail server
$options->smtp_host = 'smtp.gmail.com';
//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$options->smtp_port = 587;
//Set the encryption system to use - ssl (deprecated) or tls
$options->smtp_encryption = SMTPMailer::ENCRYPTION_STARTTLS;
//Whether to use SMTP authentication
$options->smtp_auth = true;
//Set AuthType to use XOAUTH2
$options->smtp_authtype = 'XOAUTH2';
$options->smtp_username = 'me@gmail.com';
$options->smtp_password = '<GMAIL_OAUTH2_TOKEN>';

//Create a new PHPMailer instance
$mail = new SMTPMailer($options);

//Set who the message is to be sent from
//For gmail, this generally needs to be the same as the user you logged in as
$mail->setFrom('me@gmail.com', 'First Last');

//Set who the message is to be sent to
$mail->addTO('someone@gmail.com', 'John Doe');

//Set the subject line
$mail->setSubject('PHPMailer GMail XOAUTH2 SMTP test');

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->messageFromHTML(file_get_contents('contentsutf8.html'), __DIR__);

//Replace the plain text body with one created manually
$mail->setAltBody('This is a plain-text message body');

//Attach an image file
$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if(!$mail->send()){
	echo "Mailer Error: ";
}
else{
	echo "Message sent!";
}
