<?php
/**
 * This example shows how to extend PHPMailer to simplify your coding.
 * If PHPMailer doesn't do something the way you want it to, or your code
 * contains too much boilerplate, don't edit the library files,
 * create a subclass instead and customise that.
 * That way all your changes will be retained when PHPMailer is updated.
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailerException;
use PHPMailer\PHPMailer\SMTPMailer;

require '../vendor/autoload.php';

/**
 * Use PHPMailer as a base class and extend it
 */
class myPHPMailer extends SMTPMailer{

	/**
	 * myPHPMailer constructor.
	 *
	 * @param string $body A default HTML message body
	 */
	public function __construct($body = ''){
		//Don't forget to do this or other things may not be set correctly!
		parent::__construct();
		//Set a default 'From' address
		$this->setFrom('joe@example.com', 'Joe User');
		//Equivalent to setting `Host`, `Port` and `SMTPSecure` all at once
		$this->host = 'tls://smtp.example.com:587';
		//Set an HTML and plain-text body, import relative image references
		$this->messageFromHTML($body, './images/');
		//Show debug output
		$this->loglevel = 2;
	}

	//Extend the send function
	public function send():bool{
		$this->Subject = '[Yay for me!] '.$this->Subject;
		$r             = parent::send();
		echo "I sent a message with subject ".$this->Subject;

		return $r;
	}
}

//Now creating and sending a message becomes simpler when you use this class in your app code
try{
	//Instantiate your new class, making use of the new `$body` parameter
	$mail = new myPHPMailer('<strong>This is the message body</strong>');
	// Now you only need to set things that are different from the defaults you defined
	$mail->addTO('jane@example.com', 'Jane User');
	$mail->Subject = 'Here is the subject';
	$mail->addAttachment(__FILE__, 'myPHPMailer.php');
	$mail->send(); //no need to check for errors - the exception handler will do it
}
catch(PHPMailerException $e){
	//Note that this is catching the PHPMailerException class, not the global \Exception type!
	echo "Caught a ".get_class($e).": ".$e->getMessage();
}
