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

use ErrorException;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};
use PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface;

use function class_exists, extension_loaded, function_exists, preg_match, sprintf, str_replace, strtoupper;

abstract class MailerAbstract implements LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * The POP3 PHPMailer Version number.
	 *
	 * @var string
	 */
	public const VERSION = '7.0.0-dev';

	/**
	 * The maximum line length allowed by RFC 5321 section 4.5.3.1.6,
	 * *excluding* a trailing CRLF break.
	 * @see https://tools.ietf.org/html/rfc5321#section-4.5.3.1.6
	 *
	 * @var int
	 */
	public const LINE_LENGTH_MAX = 998;

	/**
	 * The maximum line length allowed for replies in RFC 5321 section 4.5.3.1.5,
	 * *including* a trailing CRLF line break.
	 * @see https://tools.ietf.org/html/rfc5321#section-4.5.3.1.5
	 *
	 * @var int
	 */
	public const MAX_REPLY_LENGTH = 512;

	/**
	 * The lower maximum line length allowed by RFC 2822 section 2.1.1.
	 * This length does NOT include the line break
	 * 76 means that lines will be 77 or 78 chars depending on whether
	 * the line break format is LF or CRLF; both are valid.
	 *
	 * @var int
	 */
	public const LINE_LENGTH_STD = 76;

	/**
	 * The maximum line length supported by mail().
	 *
	 * Background: mail() will sometimes corrupt messages
	 * with headers headers longer than 65 chars, see #818.
	 *
	 * @var int
	 */
	public const LINE_LENGTH_STD_MAIL = 63;

	public const CHARSET_ASCII    = 'us-ascii';
	public const CHARSET_ISO88591 = 'iso-8859-1';
	public const CHARSET_UTF8     = 'utf-8';

	public const CONTENT_TYPE_PLAINTEXT             = 'text/plain';
	public const CONTENT_TYPE_TEXT_CALENDAR         = 'text/calendar';
	public const CONTENT_TYPE_TEXT_HTML             = 'text/html';
	public const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
	public const CONTENT_TYPE_MULTIPART_MIXED       = 'multipart/mixed';
	public const CONTENT_TYPE_MULTIPART_RELATED     = 'multipart/related';

	public const ENCODING_7BIT             = '7bit';
	public const ENCODING_8BIT             = '8bit';
	public const ENCODING_BASE64           = 'base64';
	public const ENCODING_BINARY           = 'binary';
	public const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

	protected const ENCODINGS = [
		self::ENCODING_7BIT,
		self::ENCODING_8BIT,
		self::ENCODING_BASE64,
		self::ENCODING_BINARY,
		self::ENCODING_QUOTED_PRINTABLE,
	];

	public const ENCRYPTION_STARTTLS = 'tls';
	public const ENCRYPTION_SMTPS    = 'ssl';

	/**
	 * The SMTP port to use if one is not specified.
	 *
	 * @var int
	 */
	public const DEFAULT_PORT_SMTP = 25;

	/**
	 * Default POP3 port number.
	 *
	 * @var int
	 */
	public const DEFAULT_PORT_POP3 = 110;

	/**
	 * Default timeout in seconds.
	 *
	 * @var int
	 */
	protected const DEFAULT_TIMEOUT_POP3 = 30;

	/**
	 * SMTP/POP3 host(s).
	 * Either a single hostname or multiple semicolon-delimited hostnames.
	 * You can also specify a different port
	 * for each host by using this format: [hostname:port]
	 * (e.g. "smtp1.example.com:25;smtp2.example.com").
	 * You can also specify encryption type, for example:
	 * (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465").
	 * Hosts will be tried in order.
	 *
	 * @var string
	 */
	public $host = 'localhost';

	/**
	 * The SMTP/POP3 server port.
	 *
	 * @var int
	 */
	public $port;

	/**
	 * SMTP/POP3 username.
	 *
	 * @var string
	 */
	public $username = '';

	/**
	 * SMTP/POP3 password.
	 *
	 * @var string
	 */
	public $password = '';

	/**
	 * Whether to generate VERP addresses on send.
	 * Only applicable when sending via SMTP.
	 *
	 * @see https://en.wikipedia.org/wiki/Variable_envelope_return_path
	 * @see http://www.postfix.org/VERP_README.html Postfix VERP info
	 *
	 * @var bool
	 */
	public $do_verp = false;

	/**
	 * The timeout value for connection, in seconds.
	 * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
	 * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
	 *
	 * @see http://tools.ietf.org/html/rfc2821#section-4.5.3.2
	 *
	 * @var int
	 */
	public $timeout = 5;

	/**
	 * The socket for the server connection.
	 *
	 * @var ?resource
	 */
	protected $socket;

	/**
	 * line endings
	 *
	 * Maintain backward compatibility with legacy Linux command line mailers
	 *
	 * @var string
	 */
	protected $LE = PHP_EOL;

	/**
	 * @var bool
	 */
	protected $streamOK = null;

	/**
	 * The array of available languages.
	 *
	 * @var  \PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface
	 */
	protected $lang;

	/**
	 * MailerAbstract constructor.
	 *
	 * @param \Psr\Log\LoggerInterface|null $logger
	 *
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function __construct(LoggerInterface $logger = null){
		$this->logger = $logger ?? new NullLogger;

		// check for missing extensions first (may occur if not installed via composer)
		foreach(['filter', 'mbstring', 'openssl'] as $ext){
			if(!extension_loaded($ext)){
				throw new PHPMailerException(sprintf($this->lang->string('extension_missing'), $ext));
			}
		}

		// This is enabled by default since 5.0.0 but some providers disable it
		// Check this once and cache the result
		if($this->streamOK === null){
			$this->streamOK = function_exists('stream_socket_client');
		}

		$this->setLanguage('en');
	}

	/**
	 * Return the current line break format string.
	 *
	 * @return string
	 */
	public function getLE():string{
		return $this->LE;
	}

	/**
	 * Set the language for error messages.
	 * The default language is English.
	 *
	 * @param string $langcode ISO 639-1 2-character language code (e.g. French is "fr")
	 *
	 * @return \PHPMailer\PHPMailer\MailerAbstract
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setLanguage(string $langcode):MailerAbstract{

		//Validate $langcode
		if(!preg_match('/^[a-z]{2}(?:-_[a-z]{2})?$/i', $langcode)){
			$langcode = 'en';
		}

		$class = 'Language'.strtoupper(str_replace('-', '_', $langcode));
		$fqcn = __NAMESPACE__.'\\Language\\'.$class;

		if(!class_exists($fqcn)){
			throw new PHPMailerException(sprintf($this->lang->string('language_missing'), $class));
		}

		$this->lang = new $fqcn;

		return $this;
	}

	/**
	 * @param \PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface $language
	 *
	 * @return \PHPMailer\PHPMailer\MailerAbstract
	 */
	public function setLanguageInterface(PHPMailerLanguageInterface $language):MailerAbstract{
		$this->lang = $language;

		return $this;
	}

	/**
	 * Get the array of strings for the current language.
	 *
	 * @return array
	 */
	public function getTranslations():array{
		return $this->lang->strings();
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
