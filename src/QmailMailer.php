<?php
/**
 * Class QmailMailer
 *
 * @filesource   QmailMailer.php
 * @created      19.11.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use Psr\Log\LoggerInterface;

use function ini_get, stripos;

class QmailMailer extends SendmailMailer{

	/**
	 * QmailMailer constructor.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailerOptions|null $options
	 * @param \Psr\Log\LoggerInterface|null              $logger
	 */
	public function __construct(PHPMailerOptions $options = null, LoggerInterface $logger = null){
		parent::__construct($options, $logger);

		$ini_sendmail_path = ini_get('sendmail_path');

		$this->sendmail = stripos($ini_sendmail_path, 'qmail') === false
			? $this->options->qmail_path
			: $ini_sendmail_path;
	}

	/**
	 * @return string
	 */
	protected function format():string{
		// CVE-2016-10033, CVE-2016-10045: Don't pass -f if characters will be escaped.
		return !empty($this->Sender) && isShellSafe($this->Sender)
			? '%s -f%s'
			: '%s';
	}

}
