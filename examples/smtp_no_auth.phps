<?php
/**
 * This example shows making an SMTP connection without using authentication.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\SMTPMailer;

require_once __DIR__.'/common.php';

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//Set the hostname of the mail server
$options->smtp_host = 'mail.example.com';
//Set the SMTP port number - likely to be 25, 465 or 587
$options->smtp_port = 25;
//Create a new PHPMailer instance
$mail = new SMTPMailer($options);
//We don't need to set this as it's the default value
//$mail->SMTPAuth = false;
//Set who the message is to be sent from
$mail->setFrom('from@example.com', 'First Last');
//Set an alternative reply-to address
$mail->addReplyTo('replyto@example.com', 'First Last');
//Set who the message is to be sent to
$mail->addTO('whoto@example.com', 'John Doe');
//Set the subject line
$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->messageFromHTML(file_get_contents('contents.html'), __DIR__);
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if(!$mail->send()){
	echo "Mailer Error: ";
}
else{
	echo "Message sent!";
}
