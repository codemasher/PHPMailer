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

	/**
	 * The path to the sendmail/qmail program.
	 *
	 * @var string
	 */
	protected $sendmail;

	/**
	 * SendmailMailer constructor.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailerOptions|null $options
	 * @param \Psr\Log\LoggerInterface|null              $logger
	 */
	public function __construct(PHPMailerOptions $options = null, LoggerInterface $logger = null){
		parent::__construct($options, $logger);

		$ini_sendmail_path = ini_get('sendmail_path');

		$this->sendmail = stripos($ini_sendmail_path, 'sendmail') === false
			? $this->options->sendmail_path
			: $ini_sendmail_path;
	}

	/**
	 * @return bool
	 */
	protected function postSend():bool{
		return $this->sendmailSend($this->mimeHeader, $this->mimeBody);
	}

	/**
	 * @return string
	 */
	protected function format():string{
		// CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
		return !empty($this->sender) && isShellSafe($this->sender)
			? '%s -oi -f%s -t'
			: '%s -oi -t';
	}

	/**
	 * Send mail using the $Sendmail program.
	 *
	 * @param string $header The message headers
	 * @param string $body   The message body
	 *
	 * @return bool
	 * @throws PHPMailerException
	 */
	protected function sendmailSend(string $header, string $body):bool{
		$header   = rtrim($header, "\r\n ").$this->LE.$this->LE;
		$sendmail = sprintf($this->format(), escapeshellcmd($this->sendmail), $this->sender);

		if($this->options->singleTo){

			foreach($this->singleToArray as $toAddr){
				$mail = @popen($sendmail, 'w');

				if(!$mail){
					throw new PHPMailerException(sprintf($this->lang->string('execute'), $this->sendmail));
				}

				fwrite($mail, 'To: '.$toAddr."\n");
				fwrite($mail, $header);
				fwrite($mail, $body);
				$result = pclose($mail);

				$this->doCallback(($result === 0), [$toAddr], $this->cc, $this->bcc, $this->subject, $body, $this->from, []);

				if($result !== 0){
					throw new PHPMailerException(sprintf($this->lang->string('execute'), $this->sendmail));
				}
			}
		}
		else{
			$mail = @popen($sendmail, 'w');

			if(!$mail){
				throw new PHPMailerException(sprintf($this->lang->string('execute'), $this->sendmail));
			}

			fwrite($mail, $header);
			fwrite($mail, $body);
			$result = pclose($mail);

			$this->doCallback(($result === 0), $this->to, $this->cc, $this->bcc, $this->subject, $body, $this->from, []);

			if($result !== 0){
				throw new PHPMailerException(sprintf($this->lang->string('execute'), $this->sendmail));
			}
		}

		return true;
	}

}
