<?php
/**
 * This uses the SMTP class alone to check that a connection can be made to an SMTP server,
 * authenticate, then disconnect
 */

//Import the PHPMailer SMTP class into the global namespace
use PHPMailer\PHPMailer\PHPMailerException;
use PHPMailer\PHPMailer\SMTPMailer;

require_once __DIR__.'/common.php';

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//Create a new SMTP instance
$smtp = new SMTPMailer($options);

try{
	//Connect to an SMTP server
	if(!$smtp->connect('mail.example.com', 25)){
		throw new PHPMailerException('Connect failed');
	}
	//Say hello
	if(!$smtp->hello(gethostname())){
		throw new PHPMailerException('EHLO failed');
	}
	//Get the list of ESMTP services the server offers
	$e = $smtp->getServerExtList();
	//If server can do TLS encryption, use it
	if(is_array($e) && array_key_exists('STARTTLS', $e)){
		$tlsok = $smtp->startTLS();
		if(!$tlsok){
			throw new PHPMailerException('Failed to start encryption');
		}
		//Repeat EHLO after STARTTLS
		if(!$smtp->hello(gethostname())){
			throw new PHPMailerException('EHLO (2) failed');
		}
		//Get new capabilities list, which will usually now include AUTH if it didn't before
		$e = $smtp->getServerExtList();
	}
	//If server supports authentication, do it (even if no encryption)
	if(is_array($e) && array_key_exists('AUTH', $e)){
		if($smtp->authenticate('username', 'password')){
			echo "Connected ok!";
		}
		else{
			throw new PHPMailerException('Authentication failed');
		}
	}
}
catch(PHPMailerException $e){
	echo 'SMTP error: '.$e->getMessage(), "\n";
}
//Whatever happened, close the connection.
$smtp->quit(true);
