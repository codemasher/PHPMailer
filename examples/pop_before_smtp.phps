<?php
/**
 * This example shows how to use POP-before-SMTP for authentication.
 * POP-before-SMTP is a very old technology that is hardly used any more.
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailerException;
use PHPMailer\PHPMailer\SMTPMailer;
use PHPMailer\PHPMailer\POP3;

require_once __DIR__.'/common.php';

//Authenticate via POP3.
//After this you should be allowed to submit messages over SMTP for a few minutes.
//Only applies if your host supports POP-before-SMTP.
$pop = (new POP3)->authorise('pop3.example.com', 110, 30, 'username', 'password', 1);

//Set the hostname of the mail server
$options->smtp_host = 'mail.example.com';
//Set the SMTP port number - likely to be 25, 465 or 587
$options->smtp_port = 25;
//Whether to use SMTP authentication
$options->smtp_auth = false;

//Create a new PHPMailer instance
$mail = new SMTPMailer($options);

try{
	//Set who the message is to be sent from
	$mail->setFrom('from@example.com', 'First Last');
	//Set an alternative reply-to address
	$mail->addReplyTo('replyto@example.com', 'First Last');
	//Set who the message is to be sent to
	$mail->addTO('whoto@example.com', 'John Doe');
	//Set the subject line
	$mail->Subject = 'PHPMailer POP-before-SMTP test';
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//and convert the HTML into a basic plain-text alternative body
	$mail->messageFromHTML(file_get_contents('contents.html'), __DIR__);
	//Replace the plain text body with one created manually
	$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	$mail->addAttachment('images/phpmailer_mini.png');
	//send the message
	//Note that we don't need check the response from this because it will throw an exception if it has trouble
	$mail->send();
	echo 'Message sent!';
}
catch(PHPMailerException $e){
	echo $e->errorMessage(); //Pretty error messages from PHPMailer
}
catch(\Exception $e){
	echo $e->getMessage(); //Boring error messages from anything else!
}
