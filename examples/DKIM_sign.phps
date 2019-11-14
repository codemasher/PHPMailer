<?php
/**
 * This example shows sending a DKIM-signed message with PHPMailer.
 * More info about DKIM can be found here: http://www.dkim.org/info/dkim-faq.html
 * There's more to using DKIM than just this code - check out this article:
 *
 * @see https://yomotherboard.com/how-to-setup-email-server-dkim-keys/
 * See also the DKIM_gen_keys example code in the examples folder,
 * which shows how to make a key pair from PHP.
 */

// Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\MailMailer;

require '../vendor/autoload.php';

// Usual setup
$mail = new MailMailer;
$mail->setFrom('from@example.com', 'First Last');
$mail->addTO('whoto@example.com', 'John Doe');
$mail->Subject = 'PHPMailer mail() test';
$mail->messageFromHTML(file_get_contents('contents.html'), __DIR__);

$mail->setDKIMCredentials(
	'example.com',      // This should be the same as the domain of your From address
	'phpmailer',        // Set this to your own selector
	'dkim_private.pem', // Path to your private key (see: DKIM_gen_keys.phps)
	'secret123',        // Put your private key's passphrase in here if it has one
	$mail->From,        // The identity you're signing as - usually your From address
	['List-Unsubscribe', 'List-Help'], // Optionally you can add extra headers for signing to meet special requirements
	false               // Suppress listing signed header fields in signature, defaults to true for debugging purpose
);

// When you send, the DKIM settings will be used to sign the message
if(!$mail->send()){
	echo "Mailer Error: ".$mail->ErrorInfo;
}
else{
	echo "Message sent!";
}
