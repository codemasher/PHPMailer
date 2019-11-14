<?php
/**
 * Class MailMailer
 *
 * @filesource   MailMailer.php
 * @created      14.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use function count, implode, ini_get, ini_set, mail, sprintf;

use const PHP_OS_FAMILY;

class MailMailer extends PHPMailer{

	protected $Mailer = self::MAILER_MAIL;

	public function __construct(){
		parent::__construct();

		// RFC-compliant line endings with mail() on Windows
		if(PHP_OS_FAMILY === 'Windows'){
			$this->LE = "\r\n";
		}

	}

	public function postSend():bool{
		return $this->mailSend($this->MIMEHeader, $this->MIMEBody);
	}

	/**
	 * Send mail using the PHP mail() function.
	 *
	 * @see    http://www.php.net/manual/en/book.mail.php
	 *
	 * @param string $header The message headers
	 * @param string $body   The message body
	 *
	 * @throws PHPMailerException
	 *
	 * @return bool
	 */
	protected function mailSend(string $header, string $body):bool{
		$toArr = [];

		foreach($this->to as $toaddr){
			$toArr[] = $this->addrFormat($toaddr);
		}

		$to = implode(', ', $toArr);

		$params = null;
		//This sets the SMTP envelope sender which gets turned into a return-path header by the receiver
		if(!empty($this->Sender) && validateAddress($this->Sender, $this->validator)){
			//A space after `-f` is optional, but there is a long history of its presence
			//causing problems, so we don't use one
			//Exim docs: http://www.exim.org/exim-html-current/doc/html/spec_html/ch-the_exim_command_line.html
			//Sendmail docs: http://www.sendmail.org/~ca/email/man/sendmail.html
			//Qmail docs: http://www.qmail.org/man/man8/qmail-inject.html
			//Example problem: https://www.drupal.org/node/1057954
			// CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
			if(isShellSafe($this->Sender)){
				$params = sprintf('-f%s', $this->Sender);
			}
		}

		if(!empty($this->Sender) && validateAddress($this->Sender, $this->validator)){
			$old_from = ini_get('sendmail_from');
			ini_set('sendmail_from', $this->Sender);
		}

		$result = false;

		if($this->SingleTo && count($toArr) > 1){
			foreach($toArr as $toAddr){
				$result = $this->mailPassthru($toAddr, $this->Subject, $body, $header, $params);
				$this->doCallback($result, [$toAddr], $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
			}
		}
		else{
			$result = $this->mailPassthru($to, $this->Subject, $body, $header, $params);
			$this->doCallback($result, $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);
		}

		if(isset($old_from)){
			ini_set('sendmail_from', $old_from);
		}

		if(!$result){
			throw new PHPMailerException($this->lang('instantiate'), $this::STOP_CRITICAL);
		}

		return true;
	}

	/**
	 * Call mail() in a safe_mode-aware fashion.
	 * Also, unless sendmail_path points to sendmail (or something that
	 * claims to be sendmail), don't pass params (not a perfect fix,
	 * but it will do).
	 *
	 * @param string      $to      To
	 * @param string      $subject Subject
	 * @param string      $body    Message Body
	 * @param string      $header  Additional Header(s)
	 * @param string|null $params  Params
	 *
	 * @return bool
	 */
	protected function mailPassthru(string $to, string $subject, string $body, string $header, string $params = null):bool{
		//Check overloading of mail function to avoid double-encoding
		$subject = ini_get('mbstring.func_overload') & 1
			? secureHeader($subject)
			: $this->encodeHeader(secureHeader($subject));

		//Calling mail() with null params breaks
		return !$this->UseSendmailOptions || $params === null
			? mail($to, $subject, $body, $header)
			: mail($to, $subject, $body, $header, $params);
	}

}
