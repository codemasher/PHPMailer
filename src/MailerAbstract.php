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

use Closure;
use ErrorException;
use PHPMailer\PHPMailer\Language\LanguageTrait;
use Psr\Log\{LoggerAwareInterface, LoggerAwareTrait, LoggerInterface, NullLogger};

use function extension_loaded, function_exists, sprintf;

abstract class MailerAbstract implements PHPMailerInterface, LoggerAwareInterface{
	use LoggerAwareTrait, LanguageTrait;

	protected const ENCODINGS = [
		self::ENCODING_7BIT,
		self::ENCODING_8BIT,
		self::ENCODING_BASE64,
		self::ENCODING_BINARY,
		self::ENCODING_QUOTED_PRINTABLE,
	];

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
	protected $action_function;

	/**
	 * @var bool
	 */
	protected $streamOK = null;

	/**
	 * @var \PHPMailer\PHPMailer\PHPMailerOptions|null
	 */
	protected $options;

	/**
	 * MailerAbstract constructor.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailerOptions|null $options
	 * @param \Psr\Log\LoggerInterface|null              $logger
	 *
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function __construct(PHPMailerOptions $options = null, LoggerInterface $logger = null){
		$this->options = $options ?? new PHPMailerOptions;
		$this->logger  = $logger ?? new NullLogger;

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
	 * @param \PHPMailer\PHPMailer\PHPMailerOptions $options
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setOptions(PHPMailerOptions $options):PHPMailerInterface{
		$this->options = $options;

		return $this;
	}

	/**
	 * @param \Closure $callback
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailerInterface
	 */
	public function setSendCallback(Closure $callback):PHPMailerInterface{
		$this->action_function = $callback;

		return $this;
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
