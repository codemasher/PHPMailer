<?php
/**
 * PHPMailer - PHP email creation and transport class.
 * PHP Version 5.5.
 *
 * @see       https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 *
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2012 - 2017 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace PHPMailer\PHPMailer;

use function addcslashes, array_filter, array_key_exists, array_merge, base64_decode, base64_encode, call_user_func,
	call_user_func_array, chunk_split, count, dirname, explode, file_get_contents, file_put_contents, floor, function_exists,
	gethostname, hash, html_entity_decode, implode, in_array, is_callable, is_file, mb_strlen, mb_substr, openssl_error_string,
	openssl_pkcs7_sign, pack, php_uname, preg_match, preg_match_all, preg_quote, preg_replace, quoted_printable_encode,
	rawurldecode, realpath, rtrim, serialize, sprintf, str_replace, strip_tags, strlen, strpos, strrpos, strtolower, substr,
	sys_get_temp_dir, tempnam, time, trim, unlink;

use const ENT_QUOTES, PKCS7_DETACHED, PATHINFO_BASENAME;

/**
 * PHPMailer - PHP email creation and transport class.
 *
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author  Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author  Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author  Brent R. Matzelle (original founder)
 */
abstract class PHPMailer extends MailerAbstract implements PHPMailerInterface{

	/**
	 * Email priority.
	 * Options: null (default), 1 = High, 3 = Normal, 5 = low.
	 * When null, the header is not set at all.
	 *
	 * @var int
	 */
	public $Priority;

	/**
	 * The character set of the message.
	 *
	 * @var string
	 */
	public $CharSet = self::CHARSET_ISO88591;

	/**
	 * The MIME Content-type of the message.
	 *
	 * @var string
	 */
	public $ContentType = self::CONTENT_TYPE_PLAINTEXT;

	/**
	 * The message encoding.
	 * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
	 *
	 * @var string
	 */
	public $Encoding = self::ENCODING_8BIT;

	/**
	 * Holds the most recent mailer error message.
	 *
	 * @var string
	 */
	public $ErrorInfo = '';

	/**
	 * The From email address for the message.
	 *
	 * @var string
	 */
	public $From = 'root@localhost';

	/**
	 * The From name of the message.
	 *
	 * @var string
	 */
	public $FromName = 'Root User';

	/**
	 * The envelope sender of the message.
	 * This will usually be turned into a Return-Path header by the receiver,
	 * and is the address that bounces will be sent to.
	 * If not empty, will be passed via `-f` to sendmail or as the 'MAIL FROM' value over SMTP.
	 *
	 * @var string
	 */
	public $Sender = '';

	/**
	 * The Subject of the message.
	 *
	 * @var string
	 */
	public $Subject = '';

	/**
	 * An HTML or plain text message body.
	 *
	 * @var string
	 */
	public $Body = '';

	/**
	 * The plain-text message body.
	 * This body can be read by mail clients that do not have HTML email
	 * capability such as mutt & Eudora.
	 * Clients that can read HTML will view the normal Body.
	 *
	 * @var string
	 */
	public $AltBody = '';

	/**
	 * An iCal message part body.
	 * Only supported in simple alt or alt_inline message types
	 * To generate iCal event structures, use classes like EasyPeasyICS or iCalcreator.
	 *
	 * @see http://sprain.ch/blog/downloads/php-class-easypeasyics-create-ical-files-with-php/
	 * @see http://kigkonsult.se/iCalcreator/
	 *
	 * @var string
	 */
	public $Ical = '';

	/**
	 * The complete compiled MIME message body.
	 *
	 * @var string
	 */
	protected $MIMEBody = '';

	/**
	 * The complete compiled MIME message headers.
	 *
	 * @var string
	 */
	protected $MIMEHeader = '';

	/**
	 * Extra headers that createHeader() doesn't fold in.
	 *
	 * @var string
	 */
	protected $mailHeader = '';

	/**
	 * Word-wrap the message body to this number of chars.
	 * Set to 0 to not wrap. A useful value here is 78, for RFC2822 section 2.1.1 compliance.
	 *
	 * @see static::STD_LINE_LENGTH
	 *
	 * @var int
	 */
	public $WordWrap = 0;

	/**
	 * Which method to use to send mail.
	 * Options: "mail", "sendmail", or "smtp".
	 *
	 * @var string
	 */
	protected $Mailer = self::MAILER_MAIL;

	/**
	 * Whether mail() uses a fully sendmail-compatible MTA.
	 * One which supports sendmail's "-oi -f" options.
	 *
	 * @var bool
	 */
	public $UseSendmailOptions = true;

	/**
	 * The email address that a reading confirmation should be sent to, also known as read receipt.
	 *
	 * @var string
	 */
	public $ConfirmReadingTo = '';

	/**
	 * The hostname to use in the Message-ID header and as default HELO string.
	 * If empty, PHPMailer attempts to find one with, in order,
	 * $_SERVER['SERVER_NAME'], gethostname(), php_uname('n'), or the value
	 * 'localhost.localdomain'.
	 *
	 * @var string
	 */
	public $Hostname = '';

	/**
	 * An ID to be used in the Message-ID header.
	 * If empty, a unique id will be generated.
	 * You can set your own, but it must be in the format "<id@domain>",
	 * as defined in RFC5322 section 3.6.4 or it will be ignored.
	 *
	 * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
	 *
	 * @var string
	 */
	public $MessageID = '';

	/**
	 * The message Date to be used in the Date header.
	 * If empty, the current date will be added.
	 *
	 * @var string
	 */
	public $MessageDate = '';

	/**
	 * The SMTP HELO of the message.
	 * Default is $Hostname. If $Hostname is empty, PHPMailer attempts to find
	 * one with the same method described above for $Hostname.
	 *
	 * @see PHPMailer::$Hostname
	 *
	 * @var string
	 */
	public $Helo = '';

	/**
	 * What kind of encryption to use on the SMTP connection.
	 * Options: '', self::ENCRYPTION_STARTTLS, or self::ENCRYPTION_SMTPS.
	 *
	 * @var string
	 */
	public $SMTPSecure = '';

	/**
	 * Whether to enable TLS encryption automatically if a server supports it,
	 * even if `SMTPSecure` is not set to 'tls'.
	 * Be aware that in PHP >= 5.6 this requires that the server's certificates are valid.
	 *
	 * @var bool
	 */
	public $SMTPAutoTLS = true;

	/**
	 * Whether to use SMTP authentication.
	 * Uses the Username and Password properties.
	 *
	 * @see PHPMailer::$username
	 * @see PHPMailer::$password
	 *
	 * @var bool
	 */
	public $SMTPAuth = false;

	/**
	 * Options array passed to stream_context_create when connecting via SMTP.
	 *
	 * @var array
	 */
	public $SMTPOptions = [];

	/**
	 * SMTP auth type.
	 * Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified.
	 *
	 * @var string
	 */
	public $AuthType = '';

	/**
	 * An instance of the PHPMailer OAuth class.
	 *
	 * @var \PHPMailer\PHPMailer\PHPMailerOAuthInterface
	 */
	protected $oauth;

	/**
	 * Comma separated list of DSN notifications
	 * 'NEVER' under no circumstances a DSN must be returned to the sender.
	 *         If you use NEVER all other notifications will be ignored.
	 * 'SUCCESS' will notify you when your mail has arrived at its destination.
	 * 'FAILURE' will arrive if an error occurred during delivery.
	 * 'DELAY'   will notify you if there is an unusual delay in delivery, but the actual
	 *           delivery's outcome (success or failure) is not yet decided.
	 *
	 * @see https://tools.ietf.org/html/rfc3461 See section 4.1 for more information about NOTIFY
	 */
	public $dsn = '';

	/**
	 * Whether to keep SMTP connection open after each message.
	 * If this is set to true then to close the connection
	 * requires an explicit call to closeSMTP().
	 *
	 * @var bool
	 */
	public $SMTPKeepAlive = false;

	/**
	 * Whether to split multiple to addresses into multiple messages
	 * or send them all in one message.
	 * Only supported in `mail` and `sendmail` transports, not in SMTP.
	 *
	 * @var bool
	 */
	public $SingleTo = false;

	/**
	 * Storage for addresses when SingleTo is enabled.
	 *
	 * @var array
	 */
	protected $SingleToArray = [];

	/**
	 * Whether to allow sending messages with an empty body.
	 *
	 * @var bool
	 */
	public $AllowEmpty = false;

	/**
	 * DKIM signing domain name.
	 *
	 * @example 'example.com'
	 *
	 * @var string
	 */
	protected $DKIM_domain;

	/**
	 * DKIM selector.
	 *
	 * @var string
	 */
	protected $DKIM_selector;

	/**
	 * DKIM private key file path or key string.
	 *
	 * @var string
	 */
	protected $DKIM_key;

	/**
	 * DKIM passphrase.
	 * Used if your key is encrypted.
	 *
	 * @var string
	 */
	protected $DKIM_passphrase;

	/**
	 * DKIM Identity.
	 * Usually the email address used as the source of the email.
	 *
	 * @var string
	 */
	protected $DKIM_identity;

	/**
	 * DKIM Extra signing headers.
	 *
	 * @example ['List-Unsubscribe', 'List-Help']
	 *
	 * @var array
	 */
	protected $DKIM_headers = [];

	/**
	 * DKIM Copy header field values for diagnostic use.
	 *
	 * @var bool
	 */
	protected $DKIM_copyHeaders = true;

	/**
	 * @var bool
	 */
	protected $DKIMCredentials = false;

	/**
	 * @var bool
	 */
	public $DKIMSign = false;

	/**
	 * Callback Action function name.
	 *
	 * The function that handles the result of the send email action.
	 * It is called out by send() for each email sent.
	 *
	 * Value can be any php callable: http://www.php.net/is_callable
	 *
	 * Parameters:
	 *   bool $result        result of the send action
	 *   array   $to            email addresses of the recipients
	 *   array   $cc            cc email addresses
	 *   array   $bcc           bcc email addresses
	 *   string  $subject       the subject
	 *   string  $body          the email body
	 *   string  $from          email address of sender
	 *   string  $extra         extra information of possible use
	 *                          "smtp_transaction_id' => last smtp transaction id
	 *
	 * @var string
	 */
	public $action_function = '';

	/**
	 * What to put in the X-Mailer header.
	 * Options: An empty string for PHPMailer default, whitespace for none, or a string to use.
	 *
	 * @var string
	 */
	public $XMailer = '';

	/**
	 * Which validator to use by default when validating email addresses.
	 * May be a callable to inject your own validator, but there are several built-in validators.
	 * The default validator uses PHP's FILTER_VALIDATE_EMAIL filter_var option.
	 *
	 * @see PHPMailer::validateAddress()
	 *
	 * @var string|callable
	 */
	public $validator = 'php';

	/**
	 * The array of 'to' names and addresses.
	 *
	 * @var array
	 */
	protected $to = [];

	/**
	 * The array of 'cc' names and addresses.
	 *
	 * @var array
	 */
	protected $cc = [];

	/**
	 * The array of 'bcc' names and addresses.
	 *
	 * @var array
	 */
	protected $bcc = [];

	/**
	 * The array of reply-to names and addresses.
	 *
	 * @var array
	 */
	protected $ReplyTo = [];

	/**
	 * An array of all kinds of addresses.
	 * Includes all of $to, $cc, $bcc.
	 *
	 * @see PHPMailer::$to
	 * @see PHPMailer::$cc
	 * @see PHPMailer::$bcc
	 *
	 * @var array
	 */
	protected $all_recipients = [];

	/**
	 * An array of names and addresses queued for validation.
	 * In send(), valid and non duplicate entries are moved to $all_recipients
	 * and one of $to, $cc, or $bcc.
	 * This array is used only for addresses with IDN.
	 *
	 * @see PHPMailer::$to
	 * @see PHPMailer::$cc
	 * @see PHPMailer::$bcc
	 * @see PHPMailer::$all_recipients
	 *
	 * @var array
	 */
	protected $RecipientsQueue = [];

	/**
	 * An array of reply-to names and addresses queued for validation.
	 * In send(), valid and non duplicate entries are moved to $ReplyTo.
	 * This array is used only for addresses with IDN.
	 *
	 * @see PHPMailer::$ReplyTo
	 *
	 * @var array
	 */
	protected $ReplyToQueue = [];

	/**
	 * The array of attachments.
	 *
	 * @var \PHPMailer\PHPMailer\Attachment[]
	 */
	protected $attachments = [];

	/**
	 * The array of custom headers.
	 *
	 * @var array
	 */
	protected $CustomHeader = [];

	/**
	 * The most recent Message-ID (including angular brackets).
	 *
	 * @var string
	 */
	protected $lastMessageID = '';

	/**
	 * The message's MIME type.
	 *
	 * @var string
	 */
	protected $message_type = '';

	/**
	 * The number of errors encountered.
	 *
	 * @var int
	 */
	protected $error_count = 0;

	/**
	 * The S/MIME certificate file path.
	 *
	 * @var string
	 */
	protected $sign_cert_file = '';

	/**
	 * The S/MIME key file path.
	 *
	 * @var string
	 */
	protected $sign_key_file = '';

	/**
	 * The optional S/MIME extra certificates ("CA Chain") file path.
	 *
	 * @var string
	 */
	protected $sign_extracerts_file = '';

	/**
	 * The S/MIME password for the key.
	 * Used only if the key is encrypted.
	 *
	 * @var string
	 */
	protected $sign_key_pass = '';

	/**
	 * Determines whether sign credentials are set
	 *
	 * @see setSignCredentials()
	 *
	 * @var bool
	 */
	protected $signCredentials = false;

	/**
	 * Wheter or not to sign a message
	 *
	 * @var bool
	 */
	public $sign = false;

	/**
	 * Return the current mailer type
	 *
	 * @return string
	 */
	public function getMailer():string{
		return $this->Mailer;
	}

	/**
	 * Get the OAuth instance.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerOAuthInterface
	 */
	public function getOAuth():PHPMailerOAuthInterface{
		return $this->oauth;
	}

	/**
	 * Set an OAuth instance.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailerOAuthInterface
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function setOAuth(PHPMailerOAuthInterface $oauth):PHPMailer{
		$this->oauth = $oauth;

		return $this;
	}

	/**
	 * Add a "To" address.
	 *
	 * @param string      $address The email address to send to
	 * @param string|null $name
	 *
	 * @return bool true on success, false if address already used or invalid in some way
	 */
	public function addTO(string $address, string $name = null):bool{
		return $this->addOrEnqueueAnAddress('to', $address, $name);
	}

	/**
	 * Allows for public read access to 'to' property.
	 * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
	 *
	 * @return array
	 */
	public function getTOs():array{
		return $this->to;
	}

	/**
	 * Clear all To recipients.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearTOs():PHPMailer{

		foreach($this->to as $to){
			unset($this->all_recipients[strtolower($to[0])]);
		}

		$this->to = [];
		$this->clearQueuedAddresses('to');

		return $this;
	}

	/**
	 * Add a "CC" address.
	 *
	 * @param string      $address The email address to send to
	 * @param string|null $name
	 *
	 * @return bool true on success, false if address already used or invalid in some way
	 */
	public function addCC(string $address, string $name = null):bool{
		return $this->addOrEnqueueAnAddress('cc', $address, $name);
	}

	/**
	 * Allows for public read access to 'cc' property.
	 * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
	 *
	 * @return array
	 */
	public function getCCs():array{
		return $this->cc;
	}

	/**
	 * Clear all CC recipients.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearCCs():PHPMailer{

		foreach($this->cc as $cc){
			unset($this->all_recipients[strtolower($cc[0])]);
		}

		$this->cc = [];
		$this->clearQueuedAddresses('cc');

		return $this;
	}

	/**
	 * Add a "BCC" address.
	 *
	 * @param string      $address The email address to send to
	 * @param string|null $name
	 *
	 * @return bool true on success, false if address already used or invalid in some way
	 */
	public function addBCC(string $address, string $name = null):bool{
		return $this->addOrEnqueueAnAddress('bcc', $address, $name);
	}

	/**
	 * Allows for public read access to 'bcc' property.
	 * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
	 *
	 * @return array
	 */
	public function getBCCs():array{
		return $this->bcc;
	}

	/**
	 * Clear all BCC recipients.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearBCCs():PHPMailer{

		foreach($this->bcc as $bcc){
			unset($this->all_recipients[strtolower($bcc[0])]);
		}

		$this->bcc = [];
		$this->clearQueuedAddresses('bcc');

		return $this;
	}

	/**
	 * Add a "Reply-To" address.
	 *
	 * @param string      $address The email address to reply to
	 * @param string|null $name
	 *
	 * @return bool true on success, false if address already used or invalid in some way
	 */
	public function addReplyTo(string $address, string $name = null):bool{
		return $this->addOrEnqueueAnAddress('Reply-To', $address, $name);
	}

	/**
	 * Allows for public read access to 'ReplyTo' property.
	 * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
	 *
	 * @return array
	 */
	public function getReplyTos():array{
		return $this->ReplyTo;
	}

	/**
	 * Clear all ReplyTo recipients.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearReplyTos():PHPMailer{
		$this->ReplyTo      = [];
		$this->ReplyToQueue = [];

		return $this;
	}

	/**
	 * Allows for public read access to 'all_recipients' property.
	 * Before the send() call, queued addresses (i.e. with IDN) are not yet included.
	 *
	 * @return array
	 */
	public function getAllRecipients():array{
		return $this->all_recipients;
	}

	/**
	 * Clear all recipient types.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearAllRecipients():PHPMailer{
		$this->to              = [];
		$this->cc              = [];
		$this->bcc             = [];
		$this->all_recipients  = [];
		$this->RecipientsQueue = [];

		return $this;
	}

	/**
	 * Clear queued addresses of given kind.
	 *
	 * @param string $kind 'to', 'cc', or 'bcc'
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearQueuedAddresses(string $kind):PHPMailer{

		$this->RecipientsQueue = array_filter(
			$this->RecipientsQueue,
			function($params) use ($kind){
				return $params[0] !== $kind;
			}
		);

		return $this;
	}

	/**
	 * Set the From and FromName properties.
	 *
	 * @param string $address
	 * @param string $name
	 * @param bool   $auto Whether to also set the Sender address, defaults to true
	 *
	 * @return bool
	 */
	public function setFrom(string $address, string $name = null, bool $auto = true):bool{
		$address = trim($address);
		$name    = trim(preg_replace('/[\r\n]+/', '', $name ?? '')); //Strip breaks and trim

		// Don't validate now addresses with IDN. Will be done in send().
		$pos = strrpos($address, '@');
		if( // @todo: clarify
			$pos === false
			|| (!has8bitChars(substr($address, ++$pos)) || !idnSupported())
			   && !validateAddress($address, $this->validator)
		){
			$error_message = sprintf('%s (From): %s', $this->lang('invalid_address'), $address);
			$this->setError($error_message);
			$this->edebug($error_message);

			return false;
		}

		$this->From     = $address;
		$this->FromName = $name;

		if($auto){
			if(empty($this->Sender)){
				$this->Sender = $address;
			}
		}

		return true;
	}

	/**
	 * Add an address to one of the recipient arrays or to the ReplyTo array. Because PHPMailer
	 * can't validate addresses with an IDN without knowing the PHPMailer::$CharSet (that can still
	 * be modified after calling this function), addition of such addresses is delayed until send().
	 * Addresses that have been added already return false, but do not throw exceptions.
	 *
	 * @param string      $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
	 * @param string      $address The email address to send, resp. to reply to
	 * @param string|null $name
	 *
	 * @return bool true on success, false if address already used or invalid in some way
	 */
	protected function addOrEnqueueAnAddress(string $kind, string $address, string $name = null):bool{
		$address = trim($address);
		$name    = trim(preg_replace('/[\r\n]+/', '', $name ?? '')); //Strip breaks and trim
		$pos     = strrpos($address, '@');

		if($pos === false){
			// At-sign is missing.
			$error_message = sprintf('%s (%s): %s', $this->lang('invalid_address'), $kind, $address);

			$this->setError($error_message);
			$this->edebug($error_message);

			return false;
		}

		$params = [$kind, $address, $name];
		// Enqueue addresses with IDN until we know the PHPMailer::$CharSet.
		if(has8bitChars(substr($address, ++$pos)) && idnSupported()){

			if($kind !== 'Reply-To'){
				if(!array_key_exists($address, $this->RecipientsQueue)){
					$this->RecipientsQueue[$address] = $params;

					return true;
				}
			}
			else{
				if(!array_key_exists($address, $this->ReplyToQueue)){
					$this->ReplyToQueue[$address] = $params;

					return true;
				}
			}

			return false;
		}

		// Immediately add standard addresses without IDN.
		return call_user_func_array([$this, 'addAnAddress'], $params);
	}

	/**
	 * Add an address to one of the recipient arrays or to the ReplyTo array.
	 * Addresses that have been added already return false, but do not throw exceptions.
	 *
	 * @param string      $kind    One of 'to', 'cc', 'bcc', or 'ReplyTo'
	 * @param string      $address The email address to send, resp. to reply to
	 * @param string|null $name
	 *
	 * @return bool true on success, false if address already used or invalid in some way
	 */
	protected function addAnAddress(string $kind, string $address, string $name = null):bool{

		if(!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])){
			$error_message = sprintf('%s: %s', $this->lang('Invalid recipient kind'), $kind);

			$this->setError($error_message);
			$this->edebug($error_message);

			return false;
		}

		if(!validateAddress($address, $this->validator)){
			$error_message = sprintf('%s (%s): %s', $this->lang('invalid_address'), $kind, $address);

			$this->setError($error_message);
			$this->edebug($error_message);

			return false;
		}

		if($kind !== 'Reply-To'){
			if(!array_key_exists(strtolower($address), $this->all_recipients)){
				$this->{$kind}[]                            = [$address, $name ?? ''];
				$this->all_recipients[strtolower($address)] = true;

				return true;
			}
		}
		else{
			if(!array_key_exists(strtolower($address), $this->ReplyTo)){
				$this->ReplyTo[strtolower($address)] = [$address, $name ?? ''];

				return true;
			}
		}

		return false;
	}

	/**
	 * Return the array of attachments.
	 *
	 * @return \PHPMailer\PHPMailer\Attachment[]
	 */
	public function getAttachments():array{
		return $this->attachments;
	}

	/**
	 * Clear all filesystem, string, and binary attachments.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearAttachments():PHPMailer{
		$this->attachments = [];

		return $this;
	}

	/**
	 * Add an attachment from a path on the filesystem.
	 * Never use a user-supplied path to a file!
	 * Returns false if the file could not be found or read.
	 * Explicitly *does not* support passing URLs; PHPMailer is not an HTTP client.
	 * If you need to do that, fetch the resource yourself and pass it in via a local file or string.
	 *
	 * @param string $path        Path to the attachment
	 * @param string $name        Overrides the attachment name
	 * @param string $encoding    File encoding (see $Encoding)
	 * @param string $type        File extension (MIME) type
	 * @param string $disposition Disposition to use
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function addAttachment(
		string $path,
		string $name = null,
		string $encoding = self::ENCODING_BASE64,
		string $type = null,
		string $disposition = 'attachment'
	):PHPMailer{

		if(!in_array($encoding, $this::ENCODINGS, true)){
			throw new PHPMailerException($this->lang('encoding').$encoding);
		}

		if(!isPermittedPath($path) || !@is_file($path)){
			$msg = $this->lang('file_access').$path;

			$this->setError($msg);
			$this->edebug($msg);

			throw new PHPMailerException($msg);
		}

		// If a MIME type is not specified, try to work it out from the file name
		if(empty($type)){
			$type = filenameToType($path);
		}

		$filename = mb_pathinfo($path, PATHINFO_BASENAME);
		if(empty($name)){
			$name = $filename;
		}

		$a = new Attachment;

		$a->content            = $path;
		$a->filename           = $filename;
		$a->name               = $name;
		$a->encoding           = $encoding;
		$a->type               = $type;
		$a->isStringAttachment = false;
		$a->disposition        = $disposition;
		$a->cid                = $name;

		$this->attachments[] = $a;

		return $this;
	}

	/**
	 * Add a string or binary attachment (non-filesystem).
	 * This method can be used to attach ascii or binary data,
	 * such as a BLOB record from a database.
	 *
	 * @param string $string      String attachment data
	 * @param string $filename    Name of the attachment
	 * @param string $encoding    File encoding (see $Encoding)
	 * @param string $type        File extension (MIME) type
	 * @param string $disposition Disposition to use
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function addStringAttachment(
		string $string,
		string $filename,
		string $encoding = self::ENCODING_BASE64,
		string $type = '',
		string $disposition = 'attachment'
	):PHPMailer{

		if(!in_array($encoding, $this::ENCODINGS, true)){
			throw new PHPMailerException($this->lang('encoding').$encoding);
		}

		// If a MIME type is not specified, try to work it out from the file name
		if(empty($type)){
			$type = filenameToType($filename);
		}

		$a = new Attachment;

		$a->content            = $string;
		$a->filename           = $filename;
		$a->name               = mb_pathinfo($filename, PATHINFO_BASENAME);
		$a->encoding           = $encoding;
		$a->type               = $type;
		$a->isStringAttachment = true;
		$a->disposition        = $disposition;
		$a->cid                = 0;

		$this->attachments[] = $a;

		return $this;
	}

	/**
	 * Add an embedded (inline) attachment from a file.
	 * This can include images, sounds, and just about any other document type.
	 * These differ from 'regular' attachments in that they are intended to be
	 * displayed inline with the message, not just attached for download.
	 * This is used in HTML messages that embed the images
	 * the HTML refers to using the $cid value.
	 * Never use a user-supplied path to a file!
	 *
	 * @param string $path        Path to the attachment
	 * @param string $cid         Content ID of the attachment; Use this to reference
	 *                            the content when using an embedded image in HTML
	 * @param string $name        Overrides the attachment name
	 * @param string $encoding    File encoding (see $Encoding)
	 * @param string $type        File MIME type
	 * @param string $disposition Disposition to use
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function addEmbeddedImage(
		string $path,
		string $cid,
		string $name = '', // @todo: warning/error or don't accept empty name?
		string $encoding = self::ENCODING_BASE64,
		string $type = '',
		string $disposition = 'inline'
	):PHPMailer{

		if(!in_array($encoding, $this::ENCODINGS, true)){
			throw new PHPMailerException($this->lang('encoding').$encoding);
		}

		if(!isPermittedPath($path) || !@is_file($path)){
			$msg = $this->lang('file_access').$path;
			$this->edebug($msg);
			$this->setError($msg);

			throw new PHPMailerException($msg);
		}

		// If a MIME type is not specified, try to work it out from the file name
		if(empty($type)){
			$type = filenameToType($path);
		}

		$filename = mb_pathinfo($path, PATHINFO_BASENAME);
		if(empty($name)){
			$name = $filename;
		}

		$a = new Attachment;

		$a->content            = $path;
		$a->filename           = $filename;
		$a->name               = $name;
		$a->encoding           = $encoding;
		$a->type               = $type;
		$a->isStringAttachment = false;
		$a->disposition        = $disposition;
		$a->cid                = $cid;

		$this->attachments[] = $a;

		return $this;
	}

	/**
	 * Add an embedded stringified attachment.
	 * This can include images, sounds, and just about any other document type.
	 * If your filename doesn't contain an extension, be sure to set the $type to an appropriate MIME type.
	 *
	 * @param string $string      The attachment binary data
	 * @param string $cid         Content ID of the attachment; Use this to reference
	 *                            the content when using an embedded image in HTML
	 * @param string $name        A filename for the attachment. If this contains an extension,
	 *                            PHPMailer will attempt to set a MIME type for the attachment.
	 *                            For example 'file.jpg' would get an 'image/jpeg' MIME type.
	 * @param string $encoding    File encoding (see $Encoding), defaults to 'base64'
	 * @param string $type        MIME type - will be used in preference to any automatically derived type
	 * @param string $disposition Disposition to use
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function addStringEmbeddedImage(
		string $string,
		string $cid,
		string $name = '', // @todo: warning/error or don't accept empty name?
		string $encoding = self::ENCODING_BASE64,
		string $type = '',
		string $disposition = 'inline'
	):PHPMailer{

		if(!in_array($encoding, $this::ENCODINGS, true)){
			throw new PHPMailerException($this->lang('encoding').$encoding);
		}

		// If a MIME type is not specified, try to work it out from the name
		if(empty($type) && !empty($name)){
			$type = filenameToType($name);
		}

		$a = new Attachment;

		$a->content            = $string;
		$a->filename           = $name;
		$a->name               = $name;
		$a->encoding           = $encoding;
		$a->type               = $type;
		$a->isStringAttachment = true;
		$a->disposition        = $disposition;
		$a->cid                = $cid;

		$this->attachments[] = $a;

		return $this;
	}

	/**
	 * Add a custom header.
	 * $name value can be overloaded to contain
	 * both header name and value (name:value).
	 *
	 * @param string      $name  Custom header name
	 * @param string|null $value Header value
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function addCustomHeader(string $name, string $value = null):PHPMailer{

		$this->CustomHeader[] = $value === null
			? explode(':', $name, 2) // Value passed in as name:value
			: [$name, $value];

		return $this;
	}

	/**
	 * Returns all custom headers.
	 *
	 * @return array
	 */
	public function getCustomHeaders():array{
		return $this->CustomHeader;
	}

	/**
	 * Clear all custom headers.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearCustomHeaders():PHPMailer{
		$this->CustomHeader = [];

		return $this;
	}

	/**
	 * Set the public and private key files and password for S/MIME signing.
	 *
	 * @param string $cert_filename
	 * @param string $key_filename
	 * @param string $key_pass            Password for private key
	 * @param string $extracerts_filename Optional path to chain certificate
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setSignCredentials(
		string $cert_filename,
		string $key_filename,
		string $key_pass,
		string $extracerts_filename = null
	):PHPMailer{

		if(!fileCheck($cert_filename) || !isPermittedPath($cert_filename)){
			throw new PHPMailerException('invalid sign cert file: '.$cert_filename);
		}

		if(!fileCheck($key_filename) || !isPermittedPath($key_filename)){
			throw new PHPMailerException('invalid sign key file: '.$key_filename);
		}

		if(empty($key_pass)){
			throw new PHPMailerException('invalid sign key passphrase');
		}

		$this->sign_cert_file = $cert_filename;
		$this->sign_key_file  = $key_filename;
		$this->sign_key_pass  = $key_pass;

		if($extracerts_filename !== null){

			if(!fileCheck($extracerts_filename) || !isPermittedPath($extracerts_filename)){
				throw new PHPMailerException('invalid extra certs file: '.$extracerts_filename);
			}

			$this->sign_extracerts_file = $extracerts_filename;
		}

		$this->signCredentials = true;
		// enable signing as soon as we get credentials
		$this->sign = true;

		return $this;
	}

	/**
	 * Sets the credentials for DKIM authentication
	 *
	 * @param string      $domain
	 * @param string      $selector
	 * @param string      $key
	 * @param string|null $keyPassphrase
	 * @param string|null $identity
	 * @param array|null  $headers
	 * @param bool|null   $copyHeaders
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setDKIMCredentials(
		string $domain,
		string $selector,
		string $key,
		string $keyPassphrase = null,
		string $identity = null,
		array $headers = null,
		bool $copyHeaders = null
	):PHPMailer{

		foreach(['domain', 'selector', 'key'] as $arg){
			${$arg} = trim(${$arg});

			if(empty(${$arg})){
				throw new PHPMailerException($arg.' must not be empty');
			}

			$this->{'DKIM_'.$arg} = ${$arg};
		}

		if(fileCheck($key) && !isPermittedPath($key)){
			throw new PHPMailerException('invalid key file path: '.$key);
		}

		$this->DKIM_passphrase  = !empty($keyPassphrase) ? $keyPassphrase : null;
		$this->DKIM_identity    = $identity;
		$this->DKIM_headers     = $headers ?? [];
		$this->DKIM_copyHeaders = $copyHeaders ?? true;

		$this->DKIMCredentials = true;
		$this->DKIMSign        = true;

		return $this;
	}

	/**
	 * Return the Message-ID header of the last email.
	 * Technically this is the value from the last time the headers were created,
	 * but it's also the message ID of the last sent message except in
	 * pathological cases.
	 *
	 * @return string
	 */
	public function getLastMessageID():string{
		return $this->lastMessageID;
	}

	/**
	 * Returns the whole MIME message.
	 * Includes complete headers and body.
	 * Only valid post preSend().
	 *
	 * @return string
	 * @see PHPMailer::preSend()
	 *
	 */
	public function getSentMIMEMessage():string{
		return rtrim($this->MIMEHeader.$this->mailHeader, "\n\r").$this->LE.$this->LE.$this->MIMEBody;
	}

	/**
	 * Create a message and send it.
	 * Uses the sending method specified by $Mailer.
	 *
	 * @return bool false on error - See the ErrorInfo property for details of the error
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function send():bool{

		try{
			return $this
				->preSend()
				->postSend()
			;
		}
		catch(PHPMailerException $e){
			$this->mailHeader = '';
			$this->setError($e->getMessage());
			$this->edebug($e->getMessage());

			throw $e;
		}

	}

	/**
	 * Prepare a message for sending.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function preSend():PHPMailer{
		$this->error_count = 0; // Reset errors
		$this->mailHeader  = '';

		// Dequeue recipient and Reply-To addresses with IDN
		foreach(array_merge($this->RecipientsQueue, $this->ReplyToQueue) as $params){
			$params[1] = punyencodeAddress($params[1], $this->CharSet);
			call_user_func_array([$this, 'addAnAddress'], $params);
		}

		if(count($this->to) + count($this->cc) + count($this->bcc) < 1){
			throw new PHPMailerException($this->lang('provide_address'), $this::STOP_CRITICAL);
		}

		// Validate From, Sender, and ConfirmReadingTo addresses
		foreach(['From', 'Sender', 'ConfirmReadingTo'] as $type){
			$this->{$type} = trim($this->{$type});

			if(empty($this->{$type})){
				continue;
			}

			$this->{$type} = punyencodeAddress($this->{$type}, $this->CharSet);

			if(!validateAddress($this->{$type}, $this->validator)){
				$this->edebug(sprintf('%s (%s): %s', $this->lang('invalid_address'), $type, $this->{$type}));
				// clear the invalid address
				unset($this->{$type});
			}
		}

		// Set whether the message is multipart/alternative
		if(!empty($this->AltBody)){
			$this->ContentType = $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
		}

		$this->setMessageType();
		// Refuse to send an empty message unless we are specifically allowing it
		if(!$this->AllowEmpty && empty($this->Body)){
			throw new PHPMailerException($this->lang('empty_message'), $this::STOP_CRITICAL);
		}

		//Create unique IDs and preset boundaries
		$uniqueid = generateId();

		//Trim subject consistently
		$this->Subject = trim($this->Subject);
		// Create body before headers in case body makes changes to headers (e.g. altering transfer encoding)
		$this->MIMEHeader = '';
		$this->MIMEBody   = $this->createBody($uniqueid);

		if($this->isError()){
			throw new PHPMailerException($this->lang('empty_message'), $this::STOP_CRITICAL);
		}

		if($this->sign){
			$this->MIMEBody = $this->pkcs7Sign($this->MIMEBody);
		}

		// createBody may have added some headers, so retain them
		$this->MIMEHeader = $this->createHeader($uniqueid).$this->MIMEHeader;

		// To capture the complete message when using mail(), create
		// an extra header list which createHeader() doesn't fold in
		if($this->Mailer === 'mail'){
			$this->mailHeader .= count($this->to) > 0
				? $this->addrAppend('To', $this->to)
				: $this->headerLine('To', 'undisclosed-recipients:;');

			$this->mailHeader .= $this->headerLine('Subject', $this->encodeHeader(secureHeader($this->Subject)));
		}

		// Sign with DKIM if enabled
		if($this->DKIMSign && $this->DKIMCredentials){
			$header_dkim = $this->DKIM_Add(
				$this->MIMEHeader.$this->mailHeader,
				$this->encodeHeader(secureHeader($this->Subject)),
				$this->MIMEBody
			);

			$this->MIMEHeader = rtrim($this->MIMEHeader, "\r\n ").$this->LE.$this->normalizeBreaks($header_dkim).$this->LE;
		}

		return $this;
	}

	/**
	 * Set the message type.
	 * PHPMailer only supports some preset message types, not arbitrary MIME structures.
	 */
	protected function setMessageType():void{
		$type = [];

		if(!empty($this->AltBody)){
			$type[] = 'alt';
		}

		if($this->inlineImageExists()){
			$type[] = 'inline';
		}

		if($this->attachmentExists()){
			$type[] = 'attach';
		}

		$this->message_type = implode('_', $type);

		if(empty($this->message_type)){
			//The 'plain' message_type refers to the message having a single body element, not that it is plain-text
			$this->message_type = 'plain';
		}
	}

	/**
	 * Check if an inline attachment is present.
	 *
	 * @return bool
	 */
	public function inlineImageExists():bool{

		foreach($this->attachments as $attachment){
			if($attachment->disposition === 'inline'){
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if an attachment (non-inline) is present.
	 *
	 * @return bool
	 */
	public function attachmentExists():bool{

		foreach($this->attachments as $attachment){
			if($attachment->disposition === 'attachment'){
				return true;
			}
		}

		return false;
	}

	/**
	 * Create recipient headers.
	 *
	 * @param string $type
	 * @param array  $addr An array of recipients,
	 *                     where each recipient is a 2-element indexed array with element 0 containing an address
	 *                     and element 1 containing a name, like:
	 *                     [['joe@example.com', 'Joe User'], ['zoe@example.com', 'Zoe User']]
	 *
	 * @return string
	 */
	protected function addrAppend(string $type, array $addr):string{
		$addresses = [];

		foreach($addr as $address){
			$addresses[] = $this->addrFormat($address);
		}

		return $type.': '.implode(', ', $addresses).$this->LE;
	}

	/**
	 * Format an address for use in a message header.
	 *
	 * @param array $addr A 2-element indexed array, element 0 containing an address, element 1 containing a name like
	 *                    ['joe@example.com', 'Joe User']
	 *
	 * @return string
	 */
	protected function addrFormat(array $addr):string{

		if(empty($addr[1])){ // No name provided
			return secureHeader($addr[0]);
		}

		return $this->encodeHeader(secureHeader($addr[1]), 'phrase').' <'.secureHeader($addr[0]).'>';
	}

	/**
	 * @param string $message The message to wrap
	 * @param int    $length  The line length to wrap to
	 * @param bool   $qp_mode Whether to run in Quoted-Printable mode
	 *
	 * @return string
	 * @todo: make protected
	 *
	 * Word-wrap message.
	 * For use with mailers that do not automatically perform wrapping
	 * and for quoted-printable encoded messages.
	 * Original written by philippe.
	 *
	 */
	public function wrapText(string $message, int $length, bool $qp_mode = false):string{

		if($length < 1){
			return $message;
		}

		$soft_break = $qp_mode ? sprintf(' =%s', $this->LE) : $this->LE;

		// If utf-8 encoding is used, we will need to make sure we don't
		// split multibyte characters when we wrap
		$is_utf8 = strtolower($this->CharSet) === $this::CHARSET_UTF8;
		$lelen   = strlen($this->LE);

		$message = $this->normalizeBreaks($message);
		//Remove a trailing line break
		if(substr($message, -$lelen) == $this->LE){
			$message = substr($message, 0, -$lelen);
		}

		//Split message into lines
		$lines = explode($this->LE, $message);
		//Message will be rebuilt in here
		$message = '';
		foreach($lines as $line){
			$words     = explode(' ', $line);
			$buf       = '';
			$firstword = true;

			foreach($words as $word){

				if($qp_mode && (strlen($word) > $length)){
					$space_left = $length - strlen($buf) - $lelen;

					if(!$firstword){
						if($space_left > 20){
							$len = $space_left;

							if($is_utf8){
								$len = utf8CharBoundary($word, $len);
							}
							elseif(substr($word, $len - 1, 1) === '='){
								--$len;
							}
							elseif(substr($word, $len - 2, 1) === '='){
								$len -= 2;
							}

							$part    = substr($word, 0, $len);
							$word    = substr($word, $len);
							$buf     .= ' '.$part;
							$message .= $buf.sprintf('=%s', $this->LE);
						}
						else{
							$message .= $buf.$soft_break;
						}

						$buf = '';
					}

					while(strlen($word) > 0){

						if($length <= 0){
							break;
						}

						$len = $length;

						if($is_utf8){
							$len = utf8CharBoundary($word, $len);
						}
						elseif(substr($word, $len - 1, 1) === '='){
							--$len;
						}
						elseif(substr($word, $len - 2, 1) === '='){
							$len -= 2;
						}

						$part = substr($word, 0, $len);
						$word = substr($word, $len);

						if(strlen($word) > 0){
							$message .= $part.sprintf('=%s', $this->LE);
						}
						else{
							$buf = $part;
						}

					}
				}
				else{
					$buf_o = $buf;

					if(!$firstword){
						$buf .= ' ';
					}

					$buf .= $word;

					if(strlen($buf) > $length && $buf_o !== ''){
						$message .= $buf_o.$soft_break;
						$buf     = $word;
					}
				}

				$firstword = false;
			}

			$message .= $buf.$this->LE;
		}

		return $message;
	}

	/**
	 * Assemble message headers.
	 *
	 * @param string $uniqueid
	 *
	 * @return string The assembled headers
	 */
	protected function createHeader(string $uniqueid):string{
		$header = $this->headerLine('Date', empty($this->MessageDate) ? rfcDate() : $this->MessageDate);

		// To be created automatically by mail()
		if($this->SingleTo){
			if($this->Mailer !== 'mail'){
				foreach($this->to as $toaddr){
					$this->SingleToArray[] = $this->addrFormat($toaddr);
				}
			}
		}
		else{
			if(count($this->to) > 0 && $this->Mailer !== 'mail'){
				$header .= $this->addrAppend('To', $this->to);
			}
			elseif(count($this->cc) === 0){
				$header .= $this->headerLine('To', 'undisclosed-recipients:;');
			}
		}

		$header .= $this->addrAppend('From', [[trim($this->From), $this->FromName]]);

		// sendmail and mail() extract Cc from the header before sending
		if(count($this->cc) > 0){
			$header .= $this->addrAppend('Cc', $this->cc);
		}

		// sendmail and mail() extract Bcc from the header before sending
		if(in_array($this->Mailer, ['sendmail', 'qmail', 'mail']) && count($this->bcc) > 0){
			$header .= $this->addrAppend('Bcc', $this->bcc);
		}

		if(count($this->ReplyTo) > 0){
			$header .= $this->addrAppend('Reply-To', $this->ReplyTo);
		}

		// mail() sets the subject itself
		if($this->Mailer !== 'mail'){
			$header .= $this->headerLine('Subject', $this->encodeHeader(secureHeader($this->Subject)));
		}

		// Only allow a custom message ID if it conforms to RFC 5322 section 3.6.4
		// https://tools.ietf.org/html/rfc5322#section-3.6.4
		$this->lastMessageID = !empty($this->MessageID) && preg_match('/^<.*@.*>$/', $this->MessageID)
			? $this->MessageID
			: sprintf('<%s@%s>', $uniqueid, $this->serverHostname());

		$header .= $this->headerLine('Message-ID', $this->lastMessageID);

		if(!empty($this->Priority)){
			$header .= $this->headerLine('X-Priority', $this->Priority);
		}

		$this->XMailer = trim($this->XMailer);

		$xmailer = empty($this->XMailer)
			? 'PHPMailer '.$this::VERSION.' (https://github.com/PHPMailer/PHPMailer)'
			: $this->XMailer;

		$header .= $this->headerLine('X-Mailer', $xmailer);

		if(!empty($this->ConfirmReadingTo)){
			$header .= $this->headerLine('Disposition-Notification-To', '<'.$this->ConfirmReadingTo.'>');
		}

		// Add custom headers
		foreach($this->CustomHeader as $h){
			$header .= $this->headerLine(trim($h[0]), $this->encodeHeader(trim($h[1])));
		}

		if(!$this->sign || !$this->signCredentials){
			$header .= $this->headerLine('MIME-Version', '1.0');
			$header .= $this->getMailMIME($uniqueid);
		}

		return $header;
	}

	/**
	 * Get the message MIME type headers.
	 *
	 * @param string $uniqueid
	 *
	 * @return string
	 */
	protected function getMailMIME(string $uniqueid):string{
		$boundary    = generateBoundary($uniqueid);
		$mime        = '';
		$ismultipart = true;

		switch($this->message_type){
			case 'inline':
				$mime .= $this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';');
				$mime .= $this->textLine(' boundary="'.$boundary[1].'"');
				break;
			case 'attach':
			case 'inline_attach':
			case 'alt_attach':
			case 'alt_inline_attach':
				$mime .= $this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_MIXED.';');
				$mime .= $this->textLine(' boundary="'.$boundary[1].'"');
				break;
			case 'alt':
			case 'alt_inline':
				$mime .= $this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE.';');
				$mime .= $this->textLine(' boundary="'.$boundary[1].'"');
				break;
			default:
				// Catches case 'plain': and case '':
				$mime        .= $this->textLine('Content-Type: '.$this->ContentType.'; charset='.$this->CharSet);
				$ismultipart = false;
				break;
		}

		// RFC1341 part 5 says 7bit is assumed if not specified
		if($this->Encoding !== $this::ENCODING_7BIT){
			// RFC 2045 section 6.4 says multipart MIME parts may only use 7bit, 8bit or binary CTE
			if($ismultipart){
				if($this->Encoding === $this::ENCODING_8BIT){
					$mime .= $this->headerLine('Content-Transfer-Encoding', $this::ENCODING_8BIT);
				}
				// The only remaining alternatives are quoted-printable and base64, which are both 7bit compatible
			}
			else{
				$mime .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);
			}
		}

		if($this->Mailer !== 'mail'){
			$mime .= $this->LE;
		}

		return $mime;
	}

	/**
	 * @param string $uniqueid
	 *
	 * @return string The assembled message body
	 * @todo: make protected
	 *
	 * Assemble the message body.
	 * Returns an empty string on failure.
	 *
	 */
	public function createBody(string $uniqueid):string{
		$boundary = generateBoundary($uniqueid);

		$body = '';

		if($this->sign && $this->signCredentials){
			$body .= $this->getMailMIME($uniqueid).$this->LE;
		}

		$this->Body = $this->wrapText($this->Body, $this->WordWrap);

		$bodyEncoding = $this->Encoding;
		$bodyCharSet  = $this->CharSet;

		//Can we do a 7-bit downgrade?
		if($bodyEncoding === $this::ENCODING_8BIT && !has8bitChars($this->Body)){
			$bodyEncoding = $this::ENCODING_7BIT;
			//All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
			$bodyCharSet = 'us-ascii';
		}

		//If lines are too long, and we're not already using an encoding that will shorten them,
		//change to quoted-printable transfer encoding for the body part only
		if($this->Encoding !== $this::ENCODING_BASE64 && $this->hasLineLongerThanMax($this->Body)){
			$bodyEncoding = $this::ENCODING_QUOTED_PRINTABLE;
		}

		//Use this as a preamble in all multipart message types
		$mimepre = 'This is a multi-part message in MIME format.'.$this->LE;

		if(in_array($this->message_type, ['inline', 'attach', 'inline_attach'])){
			$body .= $mimepre;
			$body .= call_user_func_array(
				[$this, 'body_'.$this->message_type],
				[$this->Body, $boundary, $bodyCharSet, $bodyEncoding]
			);
		}
		elseif(in_array($this->message_type, ['alt', 'alt_inline', 'alt_attach', 'alt_inline_attach'])){

			$this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);

			$altBodyEncoding = $this->Encoding;
			$altBodyCharSet  = $this->CharSet;

			//Can we do a 7-bit downgrade?
			if($altBodyEncoding === $this::ENCODING_8BIT && !has8bitChars($this->AltBody)){
				$altBodyEncoding = $this::ENCODING_7BIT;
				//All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
				$altBodyCharSet = 'us-ascii';
			}

			//If lines are too long, and we're not already using an encoding that will shorten them,
			//change to quoted-printable transfer encoding for the alt body part only
			if($altBodyEncoding !== $this::ENCODING_BASE64 && $this->hasLineLongerThanMax($this->AltBody)){
				$altBodyEncoding = $this::ENCODING_QUOTED_PRINTABLE;
			}

			$body .= $mimepre;
			$body .= call_user_func_array(
				[$this, 'body_'.$this->message_type],
				[$this->Body, $boundary, $bodyCharSet, $bodyEncoding, $altBodyCharSet, $altBodyEncoding]
			);
		}
		else{
			// Catch case 'plain' and case '', applies to simple `text/plain` and `text/html` body content types
			//Reset the `Encoding` property in case we changed it for line length reasons
			$this->Encoding = $bodyEncoding;
			$body           .= $this->encodeString($this->Body, $this->Encoding);
		}

		return $body;
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 *
	 * @return string
	 */
	protected function body_inline(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding):string{
		return $this->getBoundary($boundary[1], $bodyCharSet, '', $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 *
	 * @return string
	 */
	protected function body_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding):string{
		return $this->getBoundary($boundary[1], $bodyCharSet, '', $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 *
	 * @return string
	 */
	protected function body_inline_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding):string{
		return $this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';')
			.$this->textLine(' boundary="'.$boundary[2].'";')
			.$this->textLine(' type="' . $this::CONTENT_TYPE_TEXT_HTML . '"')
			.$this->LE
			.$this->getBoundary($boundary[2], $bodyCharSet, '', $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[2])
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		$body = $this->getBoundary($boundary[1], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->AltBody, $altBodyEncoding)
			.$this->LE
			.$this->getBoundary($boundary[1], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE;

		if(!empty($this->Ical)){
			$body .= $this->getBoundary($boundary[1], '', $this::CONTENT_TYPE_TEXT_CALENDAR.'; method=REQUEST', '')
				.$this->encodeString($this->Ical, $this->Encoding)
				.$this->LE;
		}

		$body .= $this->endBoundary($boundary[1]);

		return $body;
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt_inline(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		return $this->getBoundary($boundary[1], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->AltBody, $altBodyEncoding)
			.$this->LE
			.$this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';')
			.$this->textLine(' boundary="'.$boundary[2].'";')
			.$this->textLine(' type="' . $this::CONTENT_TYPE_TEXT_HTML . '"')
			.$this->LE
			.$this->getBoundary($boundary[2], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[2])
			.$this->LE
			.$this->endBoundary($boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		$body = $this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE.';')
			.$this->textLine(' boundary="'.$boundary[2].'"')
			.$this->LE
			.$this->getBoundary($boundary[2], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->AltBody, $altBodyEncoding)
			.$this->LE
			.$this->getBoundary($boundary[2], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE;

		if(!empty($this->Ical)){
			$body .= $this->getBoundary($boundary[2], '', $this::CONTENT_TYPE_TEXT_CALENDAR.'; method=REQUEST', '')
				.$this->encodeString($this->Ical, $this->Encoding);
		}

		$body .= $this->endBoundary($boundary[2])
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);

		return $body;
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt_inline_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		return $this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE.';')
			.$this->textLine(' boundary="'.$boundary[2].'"')
			.$this->LE
			.$this->getBoundary($boundary[2], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->AltBody, $altBodyEncoding)
			.$this->LE
			.$this->textLine('--'.$boundary[2])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';')
			.$this->textLine(' boundary="'.$boundary[3].'";')
			.$this->textLine(' type="' . $this::CONTENT_TYPE_TEXT_HTML . '"')
			.$this->LE
			.$this->getBoundary($boundary[3], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[3])
			.$this->LE
			.$this->endBoundary($boundary[2])
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	protected function pkcs7Sign(string $message):string{

		if(!$this->signCredentials){
			throw new PHPMailerException('no sign credentials set');
		}

		$tmpdir = sys_get_temp_dir();
		$file   = tempnam($tmpdir, 'pkcs7file');
		$signed = tempnam($tmpdir, 'pkcs7signed'); // will be created by openssl_pkcs7_sign()

		file_put_contents($file, $message); // dump the body

		$signcert = 'file://'.realpath($this->sign_cert_file);
		$privkey  = ['file://'.realpath($this->sign_key_file), $this->sign_key_pass];

		// Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
		// this bug still exists in 7.2+ despite being closed and "fixed"
		$sign = empty($this->sign_extracerts_file)
			? openssl_pkcs7_sign($file, $signed, $signcert, $privkey, [])
			: openssl_pkcs7_sign($file, $signed, $signcert, $privkey, [], PKCS7_DETACHED, $this->sign_extracerts_file);

		$message = file_get_contents($signed);

		unlink($file);
		unlink($signed);

		if(!$sign){
			throw new PHPMailerException($this->lang('signing').openssl_error_string());
		}

		//The message returned by openssl contains both headers and body, so need to split them up
		$parts            = explode("\n\n", $message, 2);
		$this->MIMEHeader .= $parts[0].$this->LE.$this->LE;

		return $parts[1];
	}

	/**
	 * Return the start of a message boundary.
	 *
	 * @param string $boundary
	 * @param string $charSet
	 * @param string $contentType
	 * @param string $encoding
	 *
	 * @return string
	 */
	protected function getBoundary(string $boundary, string $charSet, string $contentType, string $encoding):string{
		$result = '';

		if(empty($charSet)){
			$charSet = $this->CharSet;
		}

		if(empty($contentType)){
			$contentType = $this->ContentType;
		}

		if(empty($encoding)){
			$encoding = $this->Encoding;
		}

		$result .= $this->textLine('--'.$boundary);
		$result .= sprintf('Content-Type: %s; charset=%s', $contentType, $charSet);
		$result .= $this->LE;

		// RFC1341 part 5 says 7bit is assumed if not specified
		if($encoding !== $this::ENCODING_7BIT){
			$result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
		}

		$result .= $this->LE;

		return $result;
	}

	/**
	 * Return the end of a message boundary.
	 *
	 * @param string $boundary
	 *
	 * @return string
	 */
	protected function endBoundary(string $boundary):string{
		return $this->LE.'--'.$boundary.'--'.$this->LE;
	}

	/**
	 * Format a header line.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return string
	 */
	protected function headerLine(string $name, string $value):string{
		return $name.': '.$value.$this->LE;
	}

	/**
	 * Return a formatted mail line.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function textLine(string $value):string{
		return $value.$this->LE;
	}

	/**
	 * Attach all file, string, and binary attachments to the message.
	 * Returns an empty string on failure.
	 *
	 * @param string $disposition_type
	 * @param string $boundary
	 *
	 * @return string
	 */
	protected function attachAll(string $disposition_type, string $boundary):string{
		// Return text of body
		$mime    = [];
		$cidUniq = [];
		$incl    = [];

		// Add all attachments
		foreach($this->attachments as $attachment){
			// Check if it is a valid disposition_filter
			if($attachment->disposition === $disposition_type){
				// Check for string attachment
				$string = '';
				$path   = '';

				if($attachment->isStringAttachment){
					$string = $attachment->content;
				}
				else{
					$path = $attachment->content;
				}

				$inclhash = hash('sha256', serialize($attachment));

				if(in_array($inclhash, $incl)){
					continue;
				}

				$incl[] = $inclhash;

				if($attachment->disposition === 'inline' && array_key_exists($attachment->cid, $cidUniq)){
					continue;
				}

				$cidUniq[$attachment->cid] = true;

				$mime[] = sprintf('--%s%s', $boundary, $this->LE);
				//Only include a filename property if we have one
				$mime[] = !empty($attachment->name)
					? sprintf(
						'Content-Type: %s; name="%s"%s',
						$attachment->type,
						$this->encodeHeader(secureHeader($attachment->name)),
						$this->LE
					)
					: sprintf('Content-Type: %s%s', $attachment->type, $this->LE);

				// RFC1341 part 5 says 7bit is assumed if not specified
				if($attachment->encoding !== $this::ENCODING_7BIT){
					$mime[] = sprintf('Content-Transfer-Encoding: %s%s', $attachment->encoding, $this->LE);
				}

				if(!empty($attachment->cid)){
					$mime[] = sprintf('Content-ID: <%s>%s', $attachment->cid, $this->LE);
				}

				// If a filename contains any of these chars, it should be quoted,
				// but not otherwise: RFC2183 & RFC2045 5.1
				// Fixes a warning in IETF's msglint MIME checker
				// Allow for bypassing the Content-Disposition header totally
				if(!empty($attachment->disposition)){
					$encoded_name = $this->encodeHeader(secureHeader($attachment->name));

					/** @noinspection RegExpRedundantEscape */
					if(preg_match('/[ \(\)<>@,;:\\"\/\[\]\?=]/', $encoded_name)){
						$mime[] = sprintf(
							'Content-Disposition: %s; filename="%s"%s',
							$attachment->disposition,
							$encoded_name,
							$this->LE.$this->LE
						);
					}
					else{
						$mime[] = !empty($encoded_name)
							? sprintf(
								'Content-Disposition: %s; filename=%s%s',
								$attachment->disposition,
								$encoded_name,
								$this->LE.$this->LE
							)
							: sprintf('Content-Disposition: %s%s', $attachment->disposition, $this->LE.$this->LE);
					}
				}
				else{
					$mime[] = $this->LE;
				}

				// Encode as string attachment
				$mime[] = $attachment->isStringAttachment
					? $this->encodeString($string, $attachment->encoding)
					: $this->encodeFile($path, $attachment->encoding);

				if($this->isError()){
					return '';
				}

				$mime[] = $this->LE;
			}
		}

		$mime[] = sprintf('--%s--%s', $boundary, $this->LE);

		return implode('', $mime);
	}

	/**
	 * Encode a file attachment in requested format.
	 * Returns an empty string on failure.
	 *
	 * @param string $path     The full path to the file
	 * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	protected function encodeFile(string $path, string $encoding = self::ENCODING_BASE64):string{

		if(!fileCheck($path) || !isPermittedPath($path)){
			throw new PHPMailerException($this->lang('file_open').$path);
		}

		$file_buffer = file_get_contents($path);

		if($file_buffer === false){
			throw new PHPMailerException($this->lang('file_open').$path);
		}

		$file_buffer = $this->encodeString($file_buffer, $encoding);

		return $file_buffer;
	}

	/**
	 * @param string $str      The text to encode
	 * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 * @todo: make protected
	 *
	 * Encode a string in requested format.
	 * Returns an empty string on failure.
	 *
	 */
	public function encodeString(string $str, string $encoding = self::ENCODING_BASE64):string{

		switch(strtolower($encoding)){
			case $this::ENCODING_BASE64:
				return chunk_split(base64_encode($str), $this::LINE_LENGTH_STD, $this->LE);
			case $this::ENCODING_7BIT:
			case $this::ENCODING_8BIT:
				$encoded = $this->normalizeBreaks($str);
				// Make sure it ends with a line break
				if(substr($encoded, -strlen($this->LE)) !== $this->LE){
					$encoded .= $this->LE;
				}

				return $encoded;
			case $this::ENCODING_BINARY:
				return $str;
			case $this::ENCODING_QUOTED_PRINTABLE:
				return $this->normalizeBreaks(quoted_printable_encode($str));
		}

		$this->setError($this->lang('encoding').$encoding);
		throw new PHPMailerException($this->lang('encoding').$encoding);
	}

	/**
	 * @param string $str      The header value to encode
	 * @param string $position What context the string will be used in
	 *
	 * @return string
	 * @todo: make protected
	 *
	 * Encode a header value (not including its label) optimally.
	 * Picks shortest of Q, B, or none. Result includes folding if needed.
	 * See RFC822 definitions for phrase, comment and text positions.
	 *
	 */
	public function encodeHeader(string $str, string $position = 'text'):string{
		$matchcount = 0;

		switch(strtolower($position)){
			case 'phrase':

				if(!preg_match('/[\200-\377]/', $str)){
					// Can't use addslashes as we don't know the value of magic_quotes_sybase
					$encoded = addcslashes($str, "\0..\37\177\\\"");
					if(($encoded === $str) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)){
						return $encoded;
					}

					return "\"$encoded\"";
				}

				$matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
				break;
			/* @noinspection PhpMissingBreakStatementInspection */
			case 'comment':
				$matchcount = preg_match_all('/[()"]/', $str, $matches);
			//fallthrough
			case 'text':
			default:
				$matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
		}

		//RFCs specify a maximum line length of 78 chars, however mail() will sometimes
		//corrupt messages with headers longer than 65 chars. See #818
		$lengthsub = $this->Mailer === 'mail' ? 13 : 0;
		$maxlen    = $this::LINE_LENGTH_STD - $lengthsub;
		// Try to select the encoding which should produce the shortest output
		if($matchcount > strlen($str) / 3){
			// More than a third of the content will need encoding, so B encoding will be most efficient
			$encoding = 'B';
			//This calculation is:
			// max line length
			// - shorten to avoid mail() corruption
			// - Q/B encoding char overhead ("` =?<charset>?[QB]?<content>?=`")
			// - charset name length
			$maxlen = $this::LINE_LENGTH_STD - $lengthsub - 8 - strlen($this->CharSet);

			if($this->hasMultiBytes($str)){
				// Use a custom function which correctly encodes and wraps long
				// multibyte strings without breaking lines within a character
				$encoded = $this->base64EncodeWrapMB($str, "\n");
			}
			else{
				$encoded = base64_encode($str);
				$maxlen  -= $maxlen % 4;
				$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
			}

			$encoded = preg_replace('/^(.*)$/m', ' =?'.$this->CharSet."?$encoding?\\1?=", $encoded);
		}
		elseif($matchcount > 0){
			//1 or more chars need encoding, use Q-encode
			$encoding = 'Q';
			//Recalc max line length for Q encoding - see comments on B encode
			$maxlen  = $this::LINE_LENGTH_STD - $lengthsub - 8 - strlen($this->CharSet);
			$encoded = encodeQ($str, $position);
			$encoded = $this->wrapText($encoded, $maxlen, true);
			$encoded = str_replace('='.$this->LE, "\n", trim($encoded));
			$encoded = preg_replace('/^(.*)$/m', ' =?'.$this->CharSet."?$encoding?\\1?=", $encoded);
		}
		elseif(strlen($str) > $maxlen){
			//No chars need encoding, but line is too long, so fold it
			$encoded = trim($this->wrapText($str, $maxlen, false));

			if($encoded === $str){
				//Wrapping nicely didn't work, wrap hard instead
				$encoded = trim(chunk_split($str, $this::LINE_LENGTH_STD, $this->LE));
			}

			$encoded = str_replace($this->LE, "\n", trim($encoded));
			$encoded = preg_replace('/^(.*)$/m', ' \\1', $encoded);
		}
		else{
			//No reformatting needed
			return $str;
		}

		return trim($this->normalizeBreaks($encoded));
	}

	/**
	 * Check if a string contains multi-byte characters.
	 *
	 * @param string $str multi-byte text to wrap encode
	 *
	 * @return bool
	 */
	protected function hasMultiBytes(string $str):bool{
		return strlen($str) > mb_strlen($str, $this->CharSet);
	}

	/**
	 * Encode and wrap long multibyte strings for mail headers
	 * without breaking lines within a character.
	 * Adapted from a function by paravoid.
	 *
	 * @see http://www.php.net/manual/en/function.mb-encode-mimeheader.php#60283
	 *
	 * @param string $str       multi-byte text to wrap encode
	 * @param string $linebreak string to use as linefeed/end-of-line
	 *
	 * @return string
	 */
	protected function base64EncodeWrapMB(string $str, string $linebreak = null):string{
		$linebreak = $linebreak ?? $this->LE;
		$start     = '=?'.$this->CharSet.'?B?';
		$end       = '?=';
		$encoded   = '';

		$mb_length = mb_strlen($str, $this->CharSet);
		// Each line must have length <= 75, including $start and $end
		$length = 75 - strlen($start) - strlen($end);
		// Average multi-byte ratio
		$ratio = $mb_length / strlen($str);
		// Base64 has a 4:3 ratio
		$avgLength = floor($length * $ratio * .75);

		for($i = 0; $i < $mb_length; $i += $offset){
			$lookBack = 0;

			do{
				$offset = $avgLength - $lookBack;
				$chunk  = mb_substr($str, $i, $offset, $this->CharSet);
				$chunk  = base64_encode($chunk);
				++$lookBack;
			}
			while(strlen($chunk) > $length);

			$encoded .= $chunk.$linebreak;
		}

		// Chomp the last linefeed
		return substr($encoded, 0, -strlen($linebreak));
	}

	/**
	 * Check if an embedded attachment is present with this cid.
	 *
	 * @param string $cid
	 *
	 * @return bool
	 */
	protected function cidExists(string $cid):bool{

		foreach($this->attachments as $attachment){
			if($attachment->disposition === 'inline' && $attachment->cid === $cid){
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the server hostname.
	 * Returns 'localhost.localdomain' if unknown.
	 *
	 * @return string
	 */
	protected function serverHostname():string{
		$hostname = '';

		if(!empty($this->Hostname)){
			$hostname = $this->Hostname;
		}
		elseif(isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)){
			$hostname = $_SERVER['SERVER_NAME'];
		}
		elseif(function_exists('gethostname') && gethostname() !== false){
			$hostname = gethostname();
		}
		elseif(php_uname('n') !== false){
			$hostname = php_uname('n');
		}

		if(!isValidHost($hostname)){
			return 'localhost.localdomain';
		}

		return $hostname;
	}

	/**
	 * @param string $message
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function messageFromPlaintext(string $message):PHPMailer{
		$this->Body        = $message;
		$this->ContentType = $this::CONTENT_TYPE_PLAINTEXT;

		return $this;
	}

	/**
	 * Create a message body from an HTML string.
	 * Automatically inlines images and creates a plain-text version by converting the HTML,
	 * overwriting any existing values in Body and AltBody.
	 * Do not source $message content from user input!
	 * $basedir is prepended when handling relative URLs, e.g. <img src="/images/a.png"> and must not be empty
	 * will look for an image file in $basedir/images/a.png and convert it to inline.
	 * If you don't provide a $basedir, relative paths will be left untouched (and thus probably break in email)
	 * Converts data-uri images into embedded attachments.
	 * If you don't want to apply these transformations to your HTML, just set Body and AltBody directly.
	 *
	 * @param string        $message  HTML message string
	 * @param string        $basedir  Absolute path to a base directory to prepend to relative paths to images
	 * @param null|callable $advanced Whether to use the internal HTML to text converter
	 *                                or your own custom converter
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 *
	 * @see PHPMailer::html2text()
	 */
	public function messageFromHTML(string $message, string $basedir = null, $advanced = null):PHPMailer{
		preg_match_all('/(src|background)=["\'](.*)["\']/Ui', $message, $images);

		if(array_key_exists(2, $images)){

			if(strlen($basedir) > 1 && substr($basedir, -1) !== '/'){
				// Ensure $basedir has a trailing /
				$basedir .= '/';
			}

			foreach($images[2] as $imgindex => $url){
				// Convert data URIs into embedded images
				//e.g. "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
				if(preg_match('#^data:(image/(?:jpe?g|gif|png));?(base64)?,(.+)#', $url, $match) > 0){

					if(count($match) === 4 && $match[2] === $this::ENCODING_BASE64){
						$data = base64_decode($match[3]);
					}
					elseif(empty($match[2])){
						$data = rawurldecode($match[3]);
					}
					else{
						//Not recognised so leave it alone
						continue;
					}

					//Hash the decoded data, not the URL so that the same data-URI image used in multiple places
					//will only be embedded once, even if it used a different encoding
					$cid = hash('sha256', $data).'@phpmailer.0'; // RFC2392 S 2

					if(!$this->cidExists($cid)){
						$this->addStringEmbeddedImage($data, $cid, 'embed'.$imgindex, $this::ENCODING_BASE64, $match[1]);
					}

					$message = str_replace($images[0][$imgindex], $images[1][$imgindex].'="cid:'.$cid.'"', $message);

					continue;
				}

				if( // Only process relative URLs if a basedir is provided (i.e. no absolute local paths)
					!empty($basedir)
					// Ignore URLs containing parent dir traversal (..)
					&& strpos($url, '..') === false
					// Do not change urls that are already inline images
					&& strpos($url, 'cid:') !== 0
					// Do not change absolute URLs, including anonymous protocol
					&& !preg_match('#^[a-z][a-z0-9+.-]*:?//#i', $url)
				){
					$filename  = mb_pathinfo($url, PATHINFO_BASENAME);
					$directory = dirname($url);

					if($directory === '.'){
						$directory = '';
					}

					$cid = hash('sha256', $url).'@phpmailer.0'; // RFC2392 S 2

					if(strlen($basedir) > 1 && substr($basedir, -1) !== '/'){
						$basedir .= '/';
					}

					if(strlen($directory) > 1 && substr($directory, -1) !== '/'){
						$directory .= '/';
					}

					if($this->addEmbeddedImage(
						$basedir.$directory.$filename,
						$cid,
						$filename,
						$this::ENCODING_BASE64,
						filenameToType($filename)
					)
					){
						$message = preg_replace(
							'/'.$images[1][$imgindex].'=["\']'.preg_quote($url, '/').'["\']/Ui',
							$images[1][$imgindex].'="cid:'.$cid.'"',
							$message
						);
					}
				}
			}
		}

		$this->ContentType = $this::CONTENT_TYPE_TEXT_HTML;
		// Convert all message body line breaks to LE, makes quoted-printable encoding work much better
		$this->Body    = $this->normalizeBreaks($message);
		$this->AltBody = $this->normalizeBreaks($this->html2text($message, $advanced));

		if(!empty($this->AltBody)){
			$this->AltBody = 'This is an HTML-only message. To view it, activate HTML in your email application.'.$this->LE;
		}

		return $this;
	}

	/**
	 * Convert an HTML string into plain text.
	 * This is used by messageFromHTML().
	 * Note - older versions of this function used a bundled advanced converter
	 * which was removed for license reasons in #232.
	 * Example usage:
	 *
	 * ```php
	 * // Use default conversion
	 * $plain = $mail->html2text($html);
	 * // Use your own custom converter
	 * $plain = $mail->html2text($html, function($html) {
	 *     $converter = new MyHtml2text($html);
	 *     return $converter->get_text();
	 * });
	 * ```
	 *
	 * @param string        $html     The HTML text to convert
	 * @param null|callable $advanced Any boolean value to use the internal converter,
	 *                                or provide your own callable for custom conversion
	 *
	 * @return string
	 */
	protected function html2text(string $html, $advanced = null):string{

		if(is_callable($advanced)){
			return call_user_func($advanced, $html);
		}

		return html_entity_decode(
			trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))),
			ENT_QUOTES,
			$this->CharSet
		);
	}

	/**
	 * @param string $text
	 * @param string $breaktype What kind of line break to use; defaults to $this->LE
	 *
	 * @return string
	 * @todo: make protected
	 *
	 * Normalize line breaks in a string.
	 * Converts UNIX LF, Mac CR and Windows CRLF line breaks into a single line break format.
	 * Defaults to CRLF (for message bodies) and preserves consecutive breaks.
	 *
	 */
	public function normalizeBreaks(string $text, string $breaktype = null):string{
		$breaktype = $breaktype ?? $this->LE;

		// Normalise to \n
		$text = str_replace(["\r\n", "\r"], "\n", $text);
		// Now convert LE as needed
		if($breaktype !== "\n"){
			$text = str_replace("\n", $breaktype, $text);
		}

		return $text;
	}

	/**
	 * @param string $body Message Body
	 *
	 * @return string
	 * @todo   : make protected
	 *
	 * Generate a DKIM canonicalization body.
	 * Uses the 'simple' algorithm from RFC6376 section 3.4.3.
	 * Canonicalized bodies should *always* use CRLF, regardless of mailer setting.
	 *
	 * @see    https://tools.ietf.org/html/rfc6376#section-3.4.3
	 *
	 */
	public function DKIM_BodyC(string $body):string{

		if(empty($body)){
			return "\r\n";
		}

		// Normalize line endings to CRLF
		$body = $this->normalizeBreaks($body, "\r\n");

		//Reduce multiple trailing line breaks to a single one
		return rtrim($body, "\r\n")."\r\n";
	}

	/**
	 * Create the DKIM header and body in a new message header.
	 *
	 * @param string $headers_line Header lines
	 * @param string $subject      Subject
	 * @param string $body         Body
	 *
	 * @return string
	 */
	public function DKIM_Add(string $headers_line, string $subject, string $body):string{
		$DKIMsignatureType     = 'rsa-sha256'; // Signature & hash algorithms
		$DKIMcanonicalization  = 'relaxed/simple'; // Canonicalization of header/body
		$DKIMquery             = 'dns/txt'; // Query method
		$DKIMtime              = time(); // Signature Timestamp = seconds since 00:00:00 - Jan 1, 1970 (UTC time zone)
		$subject_header        = 'Subject: '.$subject;
		$headers               = explode($this->LE, $headers_line);
		$from_header           = '';
		$to_header             = '';
		$date_header           = '';
		$current               = '';
		$copiedHeaderFields    = '';
		$foundExtraHeaders     = [];
		$extraHeaderKeys       = '';
		$extraHeaderValues     = '';
		$extraCopyHeaderFields = '';

		foreach($headers as $header){

			if(strpos($header, 'From:') === 0){
				$from_header = $header;
				$current     = 'from_header';
			}
			elseif(strpos($header, 'To:') === 0){
				$to_header = $header;
				$current   = 'to_header';
			}
			elseif(strpos($header, 'Date:') === 0){
				$date_header = $header;
				$current     = 'date_header';
			}
			elseif(!empty($this->DKIM_headers)){

				foreach($this->DKIM_headers as $extraHeader){

					if(strpos($header, $extraHeader.':') === 0){
						$headerValue = $header;

						foreach($this->CustomHeader as $customHeader){

							if($customHeader[0] === $extraHeader){
								$headerValue = trim($customHeader[0]).': '.$this->encodeHeader(trim($customHeader[1]));

								break;
							}
						}

						$foundExtraHeaders[$extraHeader] = $headerValue;
						$current                         = '';

						break;
					}
				}

			}
			else{
				if(!empty(${$current}) && strpos($header, ' =?') === 0){
					${$current} .= $header;
				}
				else{
					$current = '';
				}
			}
		}

		foreach($foundExtraHeaders as $key => $value){
			$extraHeaderKeys   .= ':'.$key;
			$extraHeaderValues .= $value."\r\n";

			if($this->DKIM_copyHeaders){
				$extraCopyHeaderFields .= ' |'.str_replace('|', '=7C', DKIM_QP($value)).";\r\n";
			}
		}

		if($this->DKIM_copyHeaders){
			$from               = str_replace('|', '=7C', DKIM_QP($from_header));
			$to                 = str_replace('|', '=7C', DKIM_QP($to_header));
			$date               = str_replace('|', '=7C', DKIM_QP($date_header));
			$subject            = str_replace('|', '=7C', DKIM_QP($subject_header));
			$copiedHeaderFields = " z=$from\r\n".
			                      " |$to\r\n".
			                      " |$date\r\n".
			                      " |$subject;\r\n".
			                      $extraCopyHeaderFields;
		}

		$body    = $this->DKIM_BodyC($body);
		$DKIMlen = strlen($body); // Length of body
		$DKIMb64 = base64_encode(pack('H*', hash('sha256', $body))); // Base64 of packed binary SHA-256 hash of body

		$ident = !empty($this->DKIM_identity)
			? ' i='.$this->DKIM_identity.';'
			: '';

		$dkimhdrs = 'DKIM-Signature: v=1;'.
		            ' a='.$DKIMsignatureType.';'.
		            ' q='.$DKIMquery.';'.
		            ' l='.$DKIMlen.';'.
		            ' s='.$this->DKIM_selector.";\r\n".
		            ' t='.$DKIMtime.'; c='.$DKIMcanonicalization.";\r\n".
		            ' h=From:To:Date:Subject'.$extraHeaderKeys.";\r\n".
		            ' d='.$this->DKIM_domain.';'.$ident."\r\n".
		            $copiedHeaderFields.
		            ' bh='.$DKIMb64.";\r\n".
		            ' b=';

		$toSign = DKIM_HeaderC(
			$from_header."\r\n".
			$to_header."\r\n".
			$date_header."\r\n".
			$subject_header."\r\n".
			$extraHeaderValues.
			$dkimhdrs
		);

		$signed = DKIM_Sign($toSign, $this->DKIM_key, $this->DKIM_passphrase);

		return $this->normalizeBreaks($dkimhdrs.$signed).$this->LE;
	}

	/**
	 * @param string $str
	 *
	 * @return bool
	 * @todo: make protected
	 *
	 * Detect if a string contains a line longer than the maximum line length
	 * allowed by RFC 2822 section 2.1.1.
	 *
	 */
	public function hasLineLongerThanMax(string $str):bool{
		return (bool)preg_match('/^(.{'.($this::LINE_LENGTH_MAX + strlen($this->LE)).',})/m', $str);
	}

	/**
	 * Perform a callback.
	 *
	 * @param bool   $isSent
	 * @param array  $to
	 * @param array  $cc
	 * @param array  $bcc
	 * @param string $subject
	 * @param string $body
	 * @param string $from
	 * @param array  $extra
	 */
	protected function doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from, $extra):void{
		if(!empty($this->action_function) && is_callable($this->action_function)){
			call_user_func($this->action_function, $isSent, $to, $cc, $bcc, $subject, $body, $from, $extra);
		}
	}

	/**
	 * @return bool True if an error did occur
	 * @todo
	 * Check if an error occurred.
	 *
	 */
	public function isError():bool{
		return $this->error_count > 0;
	}

	/**
	 * @param string $msg
	 *
	 * @todo
	 * Add an error message to the error container.
	 *
	 */
	protected function setError(string $msg):void{
		++$this->error_count;

		if($this->Mailer === 'smtp' && $this->smtp instanceof SMTP){
			$lasterror = $this->smtp->getError();

			if(!empty($lasterror['error'])){
				$msg .= $this->lang('smtp_error').$lasterror['error'];

				if(!empty($lasterror['detail'])){
					$msg .= ' Detail: '.$lasterror['detail'];
				}

				if(!empty($lasterror['smtp_code'])){
					$msg .= ' SMTP code: '.$lasterror['smtp_code'];
				}

				if(!empty($lasterror['smtp_code_ex'])){
					$msg .= ' Additional SMTP info: '.$lasterror['smtp_code_ex'];
				}
			}
		}

		$this->ErrorInfo = $msg;
	}

}
