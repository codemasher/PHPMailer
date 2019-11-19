<?php
/**
 * Class SendmailMailer
 *
 * @filesource   SendmailMailer.php
 * @created      14.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use Psr\Log\LoggerInterface;

use function escapeshellcmd, fwrite, ini_get, pclose, popen, sprintf, stripos;

class SendmailMailer extends PHPMailer{

	protected $Mailer = self::MAILER_SENDMAIL;

	/**
	 * The path to the sendmail program.
	 *
	 * @var string
	 */
	public $Sendmail;

	public function __construct(LoggerInterface $logger = null){
		parent::__construct($logger);

		$ini_sendmail_path = ini_get('sendmail_path');

		$this->Sendmail = stripos($ini_sendmail_path, 'sendmail') === false
			? '/usr/sbin/sendmail'
			: $ini_sendmail_path;
	}


/*	qmail may extend this class

	public function setMailerQmail():PHPMailer{ // @todo: optional $path
		$ini_sendmail_path = \ini_get('sendmail_path');

		$this->Sendmail = \stripos($ini_sendmail_path, 'qmail') === false
			? '/var/qmail/bin/qmail-inject'
			: $ini_sendmail_path;

		$this->Mailer = $this::MAILER_QMAIL;

		return $this;
	}*/

	public function postSend():bool{
		return $this->sendmailSend($this->MIMEHeader, $this->MIMEBody);
	}

	/**
	 * Send mail using the $Sendmail program.
	 *
	 * @param string $header The message headers
	 * @param string $body   The message body
	 *
	 * @return bool
	 * @throws PHPMailerException
	 *
	 * @see    PHPMailer::$Sendmail
	 *
	 */
	protected function sendmailSend(string $header, string $body):bool{
		$header = rtrim($header, "\r\n ").$this->LE.$this->LE;
		// CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
		if(!empty($this->Sender) && isShellSafe($this->Sender)){
			$sendmailFmt = $this->Mailer === 'qmail' ? '%s -f%s' : '%s -oi -f%s -t';
		}
		else{
			$sendmailFmt = $this->Mailer === 'qmail' ? '%s' : '%s -oi -t';
		}

		$sendmail = sprintf($sendmailFmt, escapeshellcmd($this->Sendmail), $this->Sender);

		if($this->SingleTo){

			foreach($this->SingleToArray as $toAddr){
				$mail = @popen($sendmail, 'w');

				if(!$mail){
					throw new PHPMailerException($this->lang('execute').$this->Sendmail);
				}

				fwrite($mail, 'To: '.$toAddr."\n");
				fwrite($mail, $header);
				fwrite($mail, $body);
				$result = pclose($mail);

				$this->doCallback(($result === 0), [$toAddr], $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);

				if($result !== 0){
					throw new PHPMailerException($this->lang('execute').$this->Sendmail);
				}
			}
		}
		else{
			$mail = @popen($sendmail, 'w');

			if(!$mail){
				throw new PHPMailerException($this->lang('execute').$this->Sendmail);
			}

			fwrite($mail, $header);
			fwrite($mail, $body);
			$result = pclose($mail);

			$this->doCallback(($result === 0), $this->to, $this->cc, $this->bcc, $this->Subject, $body, $this->From, []);

			if($result !== 0){
				throw new PHPMailerException($this->lang('execute').$this->Sendmail);
			}
		}

		return true;
	}

}
