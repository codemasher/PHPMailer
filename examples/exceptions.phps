<?php
/**
 * This example shows how to make use of PHPMailer's exceptions for error handling.
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailerException;
use PHPMailer\PHPMailer\MailMailer;

require_once __DIR__.'/common.php';

//Create a new PHPMailer instance
$mail = new MailMailer($options);
try{
	//Set who the message is to be sent from
	$mail->setFrom('from@example.com', 'First Last');
	//Set an alternative reply-to address
	$mail->addReplyTo('replyto@example.com', 'First Last');
	//Set who the message is to be sent to
	$mail->addTO('whoto@example.com', 'John Doe');
	//Set the subject line
	$mail->Subject = 'PHPMailer Exceptions test';
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
catch(\Exception $e){ //The leading slash means the Global PHP Exception class will be caught
	echo $e->getMessage(); //Boring error messages from anything else!
}
