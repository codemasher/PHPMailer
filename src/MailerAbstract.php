<?php
/**
 * Class MailerAbstract
 *
 * @filesource   MailerAbstract.php
 * @created      07.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use Closure, ErrorException;
use PHPMailer\PHPMailer\Language\LanguageTrait;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};

use function extension_loaded, function_exists, in_array, preg_match, preg_replace, sprintf, strpos, strrev, strtolower, trim;

abstract class MailerAbstract implements PHPMailerInterface, LoggerAwareInterface{
	use LoggerAwareTrait, LanguageTrait;

	/**
	 * The socket for the server connection.
	 *
	 * @var resource|null
	 */
	protected $socket = null;

	/**
	 * line endings
	 *
	 * Maintain backward compatibility with legacy Linux command line mailers
	 *
	 * @var string
	 */
	protected $LE = PHP_EOL;

	/**
	 * The MIME Content-type of the message.
	 *
	 * @var string
	 */
	protected $contentType = self::CONTENT_TYPE_PLAINTEXT;

	/**
	 * The message encoding.
	 *
	 * @var string
	 */
	protected $encoding = self::ENCODING_8BIT;

	/**
	 * An ID to be used in the Message-ID header
	 *
	 * @var string|null
	 */
	protected $messageID = null;

	/**
	 * The message Date to be used in the Date header
	 *
	 * @var string|null
	 */
	protected $messageDate = null;

	/**
	 * Email priority
	 *
	 * @var int|null
	 */
	protected $priority = null;

	/**
	 * The From email address for the message.
	 *
	 * @var string
	 */
	protected $from = 'root@localhost';

	/**
	 * The From name of the message.
	 *
	 * @var string
	 */
	protected $fromName = 'Root User';

	/**
	 * The envelope sender of the message
	 *
	 * @var string|null
	 */
	protected $sender = null;

	/**
	 * The email address that a reading confirmation should be sent to, also known as read receipt
	 *
	 * @var string|null
	 */
	protected $confirmReadingTo = null;

	/**
	 * The subject of the message
	 *
	 * @var string
	 */
	protected $subject = '';

	/**
	 * The HTML or plain text message body
	 *
	 * @var string
	 */
	protected $body = '';

	/**
	 * The alternative plain-text message body
	 *
	 * @var string
	 */
	protected $altBody = '';

	/**
	 * An iCal message part body
	 *
	 * @var string
	 */
	protected $iCal = '';

	/**
	 * Callback action function
	 *
	 * @var \Closure|null
	 */
	protected $sendCallback = null;

	/**
	 * Determines whether the stream_* functions are available
	 *
	 * @var bool|null
	 */
	protected $streamOK = null;

	/**
	 * @var \PHPMailer\PHPMailer\PHPMailerOptions
	 */
	protected $options;

	/**
	 * MailerAbstract constructor
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailerOptions|null $options
	 * @param \Psr\Log\LoggerInterface|null              $logger
	 *
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function __construct(PHPMailerOptions $options = null, LoggerInterface $logger = null){
		$this->setOptions($options ?? new PHPMailerOptions);
		$this->setLogger($logger ?? new NullLogger);
		$this->setLanguage($this->options->lang);

		// check for missing extensions first (may occur if not installed via composer)
		foreach(['filter', 'intl', 'mbstring', 'openssl'] as $ext){
			if(!extension_loaded($ext)){
				throw new PHPMailerException(sprintf($this->lang->string('extension_missing'), $ext)); // @codeCoverageIgnore
			}
		}

		// This is enabled by default since PHP 5.0, but some providers disable it
		if($this->streamOK === null){
			$this->streamOK = function_exists('stream_socket_client');
		}

	}

	/**
	 * Sets the options instance
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailerOptions $options
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setOptions(PHPMailerOptions $options):PHPMailerInterface{
		$this->options = $options;

		return $this;
	}

	/**
	 * Sets the callback action function
	 *
	 * The function that handles the result of the send email action is called for each email sent.
	 *
	 * Parameters:
	 *   bool    $result        result of the send action
	 *   array   $to            email addresses of the recipients
	 *   array   $cc            cc email addresses
	 *   array   $bcc           bcc email addresses
	 *   string  $subject       the subject
	 *   string  $body          the email body
	 *   string  $from          email address of sender
	 *   string  $extra         extra information of possible use
	 *                          "smtp_transaction_id' => last smtp transaction id
	 *
	 * @param \Closure $callback
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setSendCallback(Closure $callback):PHPMailerInterface{
		$this->sendCallback = $callback;

		return $this;
	}

	/**
	 * Return the current line break format string
	 *
	 * @return string
	 */
	public function getLE():string{
		return $this->LE;
	}

	/**
	 * Sets the MIME Content-type of the message
	 *
	 * @param string $mime
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setContentType(string $mime):PHPMailerInterface{
		$mime = strtolower(trim($mime));

		// purposefully not only checking for false as the slash also may not appear as first or last character
		if(!strpos($mime, '/') || !strpos(strrev($mime), '/')){
			throw new PHPMailerException(sprintf($this->lang->string('invalid_mimetype'), $mime));
		}

		// @todo: validate mime type?
		$this->contentType = $mime;

		return $this;
	}

	/**
	 * Sets the message encoding.
	 *
	 * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
	 *
	 * @see \PHPMailer\PHPMailer\PHPMailerInterface::ENCODINGS
	 *
	 * @param string $encoding
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setEncoding(string $encoding):PHPMailerInterface{
		$encoding = strtolower(trim($encoding));

		if(!in_array($encoding, $this::ENCODINGS, true)){
			throw new PHPMailerException(sprintf($this->lang->string('encoding'), $encoding));
		}

		$this->encoding = $encoding;

		return $this;
	}

	/**
	 * Sets an ID to be used in the Message-ID header
	 *
	 * If empty, a unique id will be generated.
	 * You can set your own, but it must be in the format "<id@domain>",
	 * as defined in RFC5322 section 3.6.4 or it will be ignored.
	 *
	 * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
	 *
	 * @param string $messageID
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setMessageID(string $messageID):PHPMailerInterface{
		$messageID = trim($messageID);

		// allow clearing message ID
		if(empty($messageID)){
			$this->messageID = null;

			return $this;
		}

		// @todo: proper validation
		if(!preg_match('/^<([^@<>]+)@([^@<>]+)>$/', $messageID)){
			throw new PHPMailerException(sprintf($this->lang->string('invalid_message_id'), $messageID));
		}

		$this->messageID = $messageID;

		return $this;
	}

	/**
	 * Sets the message Date to be used in the Date header
	 *
	 * If empty, the current date will be added.
	 *
	 * @param string $messageDate
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setMessageDate(string $messageDate):PHPMailerInterface{
		$this->messageDate = $messageDate; // @todo: validate

		return $this;
	}

	/**
	 * Sets the wmail priority
	 *
	 * Options: 0 (default/none), 1 = High, 3 = Normal, 5 = low.
	 * When 0, the header is not set at all.
	 *
	 * @param int $priority
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setPriority(int $priority):PHPMailerInterface{
		$this->priority = $priority > 0 && $priority <= 5 ? $priority : null;

		return $this;
	}

	/**
	 * Sets the from and fromName properties, optionally also the sender
	 *
	 * @param string $address
	 * @param string $name
	 * @param bool   $autoSetSender Whether to also set the Sender address, defaults to true
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setFrom(string $address, string $name = null, bool $autoSetSender = true):PHPMailerInterface{
		$address = $this->cleanAndValidateAddress($address);

		if($address === null){
			throw new PHPMailerException(sprintf($this->lang->string('invalid_address'), 'From', $address));
		}

		$this->from     = $address;
		$this->fromName = trim(preg_replace('/[\r\n]+/', '', $name ?? '')); //Strip breaks and trim

		if($autoSetSender){
			$this->setSender($address);
		}

		return $this;
	}

	/**
	 * Sets the envelope sender of the message
	 *
	 * This will usually be turned into a Return-Path header by the receiver,
	 * and is the address that bounces will be sent to.
	 * If not empty, will be passed via `-f` to sendmail or as the 'MAIL FROM' value over SMTP.
	 *
	 * @param string $sender
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setSender(string $sender):PHPMailerInterface{
		$sender = trim($sender);

		// allow clearing address
		if(empty($sender)){
			$this->sender = null;

			return $this;
		}

		$sender = $this->cleanAndValidateAddress($sender);

		if($sender === null){
			throw new PHPMailerException(sprintf($this->lang->string('invalid_address'), 'sender', $sender));
		}

		$this->sender = $sender;

		return $this;
	}

	/**
	 * Sets the email address that a reading confirmation should be sent to, also known as read receipt
	 *
	 * @param string $confirmReadingTo
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setConfirmReadingTo(string $confirmReadingTo):PHPMailerInterface{
		$confirmReadingTo = trim($confirmReadingTo);

		// allow clearing address
		if(empty($confirmReadingTo)){
			$this->confirmReadingTo = null;

			return $this;
		}

		$confirmReadingTo = $this->cleanAndValidateAddress($confirmReadingTo);

		if($confirmReadingTo === null){
			throw new PHPMailerException(sprintf($this->lang->string('invalid_address'), 'confirmReadingTo', $confirmReadingTo));
		}

		$this->confirmReadingTo = $confirmReadingTo;

		return $this;
	}

	/**
	 * Sets the subject of the message
	 *
	 * @param string $subject
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setSubject(string $subject):PHPMailerInterface{
		$this->subject = trim($subject);

		return $this;
	}

	/**
	 * Sets the HTML or plain text (main) message body
	 *
	 * @param string      $content
	 * @param string|null $contentType
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setMessageBody(string $content, string $contentType = null):PHPMailerInterface{

		if($contentType){
			$this->setContentType($contentType);
		}

		$this->body = $content;

		return $this;
	}

	/**
	 * Sets the alternative plain-text message body
	 *
	 * This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora.
	 * Clients that can read HTML will view the normal Body.
	 *
	 * @param string $altBody
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setAltBody(string $altBody):PHPMailerInterface{
		$this->altBody = $altBody;

		return $this;
	}

	/**
	 * Sets an iCal message part body
	 *
	 * Only supported in simple alt or alt_inline message types
	 * To generate iCal event structures, use classes like EasyPeasyICS or iCalcreator.
	 *
	 * @see http://sprain.ch/blog/downloads/php-class-easypeasyics-create-ical-files-with-php/
	 * @see http://kigkonsult.se/iCalcreator/
	 *
	 * @param string $iCal
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setIcal(string $iCal):PHPMailerInterface{
		$this->iCal = $iCal;

		return $this;
	}

	/**
	 * @param string $address
	 *
	 * @return string|null
	 */
	protected function cleanAndValidateAddress(string $address):?string{
		$address = trim($address); // @todo: clean other stuff? egulias/email-validator

		if(!validateAddress($address, $this->options->validator)){
			// if we fail on the first try, check if punycode works
			$address = punyencodeAddress($address, $this->options->charSet);

			if(!validateAddress($address, $this->options->validator)){
				return null;
			}
		}

		return strtolower($address);
	}

	/**
	 * @param int    $severity
	 * @param string $msg
	 * @param string $file
	 * @param int    $line
	 *
	 * @return void
	 * @throws \ErrorException
	 */
	protected function errorHandler(int $severity, string $msg, string $file, int $line):void{
		throw new ErrorException($msg, 0, $severity, $file, $line);
	}

}
