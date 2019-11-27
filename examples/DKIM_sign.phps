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

require_once __DIR__.'/common.php';

// This should be the same as the domain of your From address
$options->DKIM_domain      = 'example.com';
// Set this to your own selector
$options->DKIM_selector    = 'phpmailer';
// Path to your private key (see: DKIM_gen_keys.phps)
$options->DKIM_key         = 'dkim_private.pem';
// Put your private key's passphrase in here if it has one
$options->DKIM_passphrase  = 'secret123';
// The identity you're signing as - usually your From address
$options->DKIM_identity    = 'from@example.com';
// Optionally you can add extra headers for signing to meet special requirements
$options->DKIM_headers     = ['List-Unsubscribe', 'List-Help'];
// Suppress listing signed header fields in signature, defaults to true for debugging purpose
$options->DKIM_copyHeaders = false;
// enable signing
$options->DKIM_sign        = true;

// Usual setup
$mail = new MailMailer($options);
$mail->setFrom('from@example.com', 'First Last');
$mail->addTO('whoto@example.com', 'John Doe');
$mail->setSubject('PHPMailer mail() test');
$mail->messageFromHTML(file_get_contents('contents.html'), __DIR__);

// When you send, the DKIM settings will be used to sign the message
if(!$mail->send()){
	echo "Mailer Error: ";
}
else{
	echo "Message sent!";
}
