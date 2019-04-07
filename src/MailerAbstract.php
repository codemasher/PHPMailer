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
	 * Debug level for no output.
	 */
	public const DEBUG_OFF = 0;

	/**
	 * Debug level to show client -> server messages.
	 */
	public const DEBUG_CLIENT = 1;

	/**
	 * Debug level to show client -> server and server -> client messages.
	 */
	public const DEBUG_SERVER = 2;

	/**
	 * Debug level to show connection status, client -> server and server -> client messages.
	 */
	public const DEBUG_CONNECTION = 3;

	/**
	 * Debug level to show all messages.
	 */
	public const DEBUG_LOWLEVEL = 4;

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
