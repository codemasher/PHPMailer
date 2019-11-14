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

use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, NullLogger};

use function array_key_exists, count, dirname, extension_loaded, file_exists, function_exists, preg_match;

use const DIRECTORY_SEPARATOR;

abstract class MailerAbstract implements LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * The POP3 PHPMailer Version number.
	 *
	 * @var string
	 */
	public const VERSION = '7.0.0-dev';

	/**
	 * The maximum line length allowed by RFC 2822 section 2.1.1.
	 *
	 * @var int
	 */
	public const LINE_LENGTH_MAX = 998;

	/**
	 * The lower maximum line length allowed by RFC 2822 section 2.1.1.
	 * This length does NOT include the line break
	 * 76 means that lines will be 77 or 78 chars depending on whether
	 * the line break format is LF or CRLF; both are valid.
	 *
	 * @var int
	 */
	public const LINE_LENGTH_STD = 76;

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

	public const MAILER_SMTP     = 'smtp';
	public const MAILER_MAIL     = 'mail';
	public const MAILER_SENDMAIL = 'sendmail';
	public const MAILER_QMAIL    = 'qmail';

	public const ENCRYPTION_STARTTLS = 'tls';
	public const ENCRYPTION_SMTPS    = 'ssl';

	/**
	 * Debug level for no output.
	 *
	 * @var int
	 */
	public const DEBUG_OFF = 0;

	/**
	 * Debug level to show client -> server messages.
	 *
	 * @var int
	 */
	public const DEBUG_CLIENT = 1;

	/**
	 * Debug level to show client -> server and server -> client messages.
	 *
	 * @var int
	 */
	public const DEBUG_SERVER = 2;

	/**
	 * Debug level to show connection status, client -> server and server -> client messages.
	 *
	 * @var int
	 */
	public const DEBUG_CONNECTION = 3;

	/**
	 * Debug level to show all messages.
	 *
	 * @var int
	 */
	public const DEBUG_LOWLEVEL = 4;

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
	 * Error severity: message only, continue processing.
	 *
	 * @var int
	 */
	protected const STOP_MESSAGE = 0;

	/**
	 * Error severity: message, likely ok to continue processing.
	 *
	 * @var int
	 */
	protected const STOP_CONTINUE = 1;

	/**
	 * Error severity: message, plus full stop, critical error reached.
	 *
	 * @var int
	 */
	protected const STOP_CRITICAL = 2;

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
	public $timeout = 300;

	/**
	 * Debug output level.
	 * Options:
	 * * self::DEBUG_OFF (`0`) No debug output, default
	 * * self::DEBUG_CLIENT (`1`) Client commands
	 * * self::DEBUG_SERVER (`2`) Client commands and server responses
	 * * self::DEBUG_CONNECTION (`3`) As DEBUG_SERVER plus connection status
	 * * self::DEBUG_LOWLEVEL (`4`) Low-level data output, all messages.
	 *
	 * @var int
	 */
	public $loglevel = self::DEBUG_OFF;

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
	 * @var array
	 */
	protected $language = [];

	/**
	 * MailerAbstract constructor.
	 *
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function __construct(){
		$this->logger = new NullLogger;

		// check for missing extensions first (may ocur if not installed via composer)
		foreach(['ctype', 'filter', 'mbstring', 'openssl'] as $ext){
			if(!extension_loaded($ext)){
				throw new PHPMailerException($this->lang('extension_missing').$ext);
			}
		}

		// This is enabled by default since 5.0.0 but some providers disable it
		// Check this once and cache the result
		if($this->streamOK === null){
			$this->streamOK = function_exists('stream_socket_client');
		}

	}

	/**
	 * Set debug output level.
	 *
	 * @param int $level
	 *
	 * @return \PHPMailer\PHPMailer\MailerAbstract
	 */
	public function setDebugLevel(int $level = 0):MailerAbstract{
		$this->loglevel = $level;

		return $this;
	}

	/**
	 * Get debug output level.
	 *
	 * @return int
	 */
	public function getDebugLevel():int{
		return $this->loglevel;
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
	 * Output debugging info via PSR-3 LoggerInterface
	 *
	 * @param string $str   Debug string to output
	 * @param int    $level The debug level of this message; see DEBUG_* constants
	 *
	 * @see SMTP::$loglevel
	 */
	protected function edebug(string $str, int $level = self::DEBUG_OFF):void{

		if($level > $this->loglevel){
			return;
		}

		$this->logger->debug($str);
	}

	/**
	 * @todo: simplify, clean up
	 *
	 * Set the language for error messages.
	 * Returns false if it cannot load the language file.
	 * The default language is English.
	 *
	 * @param string $langcode  ISO 639-1 2-character language code (e.g. French is "fr")
	 * @param string $lang_path Path to the language file directory, with trailing separator (slash)
	 *
	 * @return bool
	 */
	public function setLanguage(string $langcode = 'en', string $lang_path = ''):bool{
		// Backwards compatibility for renamed language codes
		$renamed_langcodes = [
			'br' => 'pt_br',
			'cz' => 'cs',
			'dk' => 'da',
			'no' => 'nb',
			'se' => 'sv',
			'rs' => 'sr',
			'tg' => 'tl',
		];

		if(isset($renamed_langcodes[$langcode])){
			$langcode = $renamed_langcodes[$langcode];
		}

		// Define full set of translatable strings in English
		$PHPMAILER_LANG = [
			'authenticate'         => 'SMTP Error: Could not authenticate.',
			'connect_host'         => 'SMTP Error: Could not connect to SMTP host.',
			'data_not_accepted'    => 'SMTP Error: data not accepted.',
			'empty_message'        => 'Message body empty',
			'encoding'             => 'Unknown encoding: ',
			'execute'              => 'Could not execute: ',
			'file_access'          => 'Could not access file: ',
			'file_open'            => 'File Error: Could not open file: ',
			'from_failed'          => 'The following From address failed: ',
			'instantiate'          => 'Could not instantiate mail function.',
			'invalid_address'      => 'Invalid address: ',
			'mailer_not_supported' => ' mailer is not supported.',
			'provide_address'      => 'You must provide at least one recipient email address.',
			'recipients_failed'    => 'SMTP Error: The following recipients failed: ',
			'signing'              => 'Signing Error: ',
			'smtp_connect_failed'  => 'SMTP connect() failed.',
			'smtp_error'           => 'SMTP server error: ',
			'variable_set'         => 'Cannot set or reset variable: ',
			'extension_missing'    => 'Extension missing: ',
		];

		if(empty($lang_path)){
			// Calculate an absolute path so it can work if CWD is not here
			$lang_path = dirname(__DIR__).DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR;
		}
		//Validate $langcode
		if(!preg_match('/^[a-z]{2}(?:_[a-zA-Z]{2})?$/', $langcode)){
			$langcode = 'en';
		}

		$foundlang = true;
		$lang_file = $lang_path.'phpmailer.lang-'.$langcode.'.php';

		// There is no English translation file
		if($langcode !== 'en'){
			// Make sure language file path is readable
			if(!isPermittedPath($lang_file) || !file_exists($lang_file)){
				$foundlang = false;
			}
			else{
				// Overwrite language-specific strings.
				// This way we'll never have missing translation keys.
				$foundlang = include $lang_file;
			}
		}

		$this->language = $PHPMAILER_LANG;

		return (bool)$foundlang; // Returns false if language not found
	}

	/**
	 * Get the array of strings for the current language.
	 *
	 * @return array
	 */
	public function getTranslations():array{
		return $this->language;
	}

	/**
	 * Get an error message in the current language.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function lang(string $key):string{

		if(count($this->language) < 1){
			$this->setLanguage('en'); // set the default language
		}

		if(array_key_exists($key, $this->language)){
			if($key === 'smtp_connect_failed'){
				//Include a link to troubleshooting docs on SMTP connection failure
				//this is by far the biggest cause of support questions
				//but it's usually not PHPMailer's fault.
				return $this->language[$key].' https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';
			}

			return $this->language[$key];
		}

		//Return the key as a fallback
		return $key;
	}

}
