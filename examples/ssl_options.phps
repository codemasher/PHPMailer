<?php
/**
 * This example shows settings to use when sending over SMTP with TLS and custom connection options.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\SMTPMailer;

require_once __DIR__.'/common.php';

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//Set the hostname of the mail server
$options->smtp_host = 'smtp.example.com';
//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$options->smtp_port = 587;
//Username to use for SMTP authentication - use full email address for gmail
$options->smtp_username = 'username@example.com';
//Password to use for SMTP authentication
$options->smtp_password = 'yourpassword';
//Whether to use SMTP authentication
$options->smtp_auth = true;
//Set the encryption system to use - ssl (deprecated) or tls
$options->smtp_encryption = SMTPMailer::ENCRYPTION_STARTTLS;

//Custom connection options
//Note that these settings are INSECURE
$options->smtp_stream_context_options = [
	'ssl' => [
		'verify_peer'       => true,
		'verify_depth'      => 3,
		'allow_self_signed' => true,
		'peer_name'         => 'smtp.example.com',
		'cafile'            => '/etc/ssl/ca_cert.pem',
	],
];

//Create a new PHPMailer instance
$mail = new SMTPMailer($options);

//Set who the message is to be sent from
$mail->setFrom('from@example.com', 'First Last');

//Set who the message is to be sent to
$mail->addTO('whoto@example.com', 'John Doe');

//Set the subject line
$mail->setSubject('PHPMailer SMTP options test');

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->messageFromHTML(file_get_contents('contents.html'), __DIR__);

//Send the message, check for errors
if(!$mail->send()){
	echo 'Mailer Error: ';
}
else{
	echo 'Message sent!';
}
