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

abstract class MailerAbstract implements LoggerAwareInterface{
	use LoggerAwareTrait;

	/**
	 * The POP3 PHPMailer Version number.
	 *
	 * @var string
	 */
	public const VERSION = '6.0.7';

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
	protected const DEFAULT_PORT_SMTP = 25;

	/**
	 * Default POP3 port number.
	 *
	 * @var int
	 */
	protected const DEFAULT_PORT_POP3 = 110;

	/**
	 * Default timeout in seconds.
	 *
	 * @var int
	 */
	protected const DEFAULT_TIMEOUT = 30;

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
	 * SMTP RFC standard line ending.
	 *
	 * @var string
	 */
	protected $LE = "\r\n";

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
	 * MailerAbstract constructor.
	 */
	public function __construct(){
		$this->logger = new NullLogger;
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
	 * Set the line break format string, e.g. "\r\n".
	 *
	 * @param string $le
	 */
	protected function setLE(string $le):void{
		$this->LE = $le;
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

}
