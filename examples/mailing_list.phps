<?php
/**
 * This example shows how to send a message to a whole list of recipients efficiently.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\SMTPMailer;

error_reporting(E_STRICT | E_ALL);

date_default_timezone_set('Etc/UTC');

require_once __DIR__.'/common.php';

$options->smtp_host      = 'smtp.example.com';
$options->smtp_port      = 25;
$options->smtp_username  = 'yourname@example.com';
$options->smtp_password  = 'yourpassword';
$options->smtp_auth      = true;
$options->smtp_keepalive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead

$mail = new SMTPMailer($options);

$body = file_get_contents('contents.html');

$mail->setFrom('list@example.com', 'List manager');
$mail->addReplyTo('list@example.com', 'List manager');

$mail->Subject = "PHPMailer Simple database mailing list test";

//Same body for all messages, so set this before the sending loop
//If you generate a different body for each recipient (e.g. you're using a templating system),
//set it inside the loop
$mail->messageFromHTML($body);
//messageFromHTML also sets AltBody, but if you want a custom one, set it afterwards
$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';

//Connect to the database and select the recipients from your mailing list that have not yet been sent to
//You'll need to alter this to match your database
$mysql = mysqli_connect('localhost', 'username', 'password');
mysqli_select_db($mysql, 'mydb');
$result = mysqli_query($mysql, 'SELECT full_name, email, photo FROM mailinglist WHERE sent = FALSE');

foreach($result as $row){
	$mail->addTO($row['email'], $row['full_name']);
	if(!empty($row['photo'])){
		$mail->addStringAttachment($row['photo'], 'YourPhoto.jpg'); //Assumes the image data is stored in the DB
	}

	if(!$mail->send()){
		echo "Mailer Error (".str_replace("@", "&#64;", $row["email"]).') <br />';
		break; //Abandon sending
	}
	else{
		echo "Message sent to :".$row['full_name'].' ('.str_replace("@", "&#64;", $row['email']).')<br />';
		//Mark it as sent in the DB
		mysqli_query(
			$mysql,
			"UPDATE mailinglist SET sent = TRUE WHERE email = '".
			mysqli_real_escape_string($mysql, $row['email'])."'"
		);
	}
	// Clear all addresses and attachments for next loop
	$mail->clearTOs();
	$mail->clearAttachments();
}
