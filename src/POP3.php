<?php
/**
 * PHPMailer POP-Before-SMTP Authentication Class.
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

use PHPMailer\PHPMailer\Language\LanguageTrait;
use Psr\Log\{LoggerAwareTrait, LoggerInterface, NullLogger};
use Throwable;

use function fclose, fgets, fsockopen, fwrite, is_resource, restore_error_handler,
	set_error_handler, sprintf, stream_set_timeout, strlen, substr;

/**
 * PHPMailer POP-Before-SMTP Authentication Class.
 * Specifically for PHPMailer to use for RFC1939 POP-before-SMTP authentication.
 * 1) This class does not support APOP authentication.
 * 2) Opening and closing lots of POP3 connections can be quite slow. If you need
 *   to send a batch of emails then just perform the authentication once at the start,
 *   and then loop through your mail sending script. Providing this process doesn't
 *   take longer than the verification period lasts on your POP3 server, you should be fine.
 * 3) This is really ancient technology; you should only need to use it to talk to very old systems.
 * 4) This POP3 class is deliberately lightweight and incomplete, and implements just
 *   enough to do authentication.
 *   If you want a more complete class there are other POP3 classes for PHP available.
 *
 * @author  Richard Davey (original author) <rich@corephp.co.uk>
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author  Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author  Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 */
class POP3{
	use LoggerAwareTrait, LanguageTrait;

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
	protected const DEFAULT_TIMEOUT_POP3 = 30;

	/**
	 * The socket for the server connection.
	 *
	 * @var ?resource
	 */
	protected $socket;

	/**
	 * Are we connected?
	 *
	 * @var bool
	 */
	protected $connected = false;

	/**
	 * POP3 constructor.
	 *
	 * @param \Psr\Log\LoggerInterface|null $logger
	 */
	public function __construct(LoggerInterface $logger = null){
		$this->logger = $logger ?? new NullLogger;

		$this->setLanguage('en');
	}

	/**
	 * Authenticate with a POP3 server.
	 * A connect, login, disconnect sequence
	 * appropriate for POP-before SMTP authorisation.
	 *
	 * @param string      $host    The hostname to connect to
	 * @param int|null    $port    The port number to connect to
	 * @param int|null    $timeout The timeout value
	 * @param string|null $username
	 * @param string|null $password
	 *
	 * @return bool
	 */
	public function authorise(
		string $host,
		int $port = null,
		int $timeout = null,
		string $username = null,
		string $password = null
	):bool{

		//  connect
		if($this->connect($host, $port, $timeout)){
			if($this->login($username, $password)){
				$this->disconnect();

				return true;
			}
		}

		// We need to disconnect regardless of whether the login succeeded
		$this->disconnect();

		return false;
	}

	/**
	 * Connect to a POP3 server.
	 *
	 * @param string   $host
	 * @param int|null $port
	 * @param int|null $timeout
	 *
	 * @return bool
	 */
	public function connect(string $host, int $port = null, int $timeout = null):bool{
		//  Are we already connected?
		if($this->connected){
			return true;
		}

		$port    = $port ?? $this->port ?? $this::DEFAULT_PORT_POP3;
		$timeout = $timeout ?? $this->timeout ?? $this::DEFAULT_TIMEOUT_POP3;

		//On Windows this will raise a PHP Warning error if the hostname doesn't exist.
		//Rather than suppress it with @fsockopen, capture it cleanly instead
		set_error_handler([$this, 'errorHandler']);

		try{
			//  connect to the POP3 server
			$this->socket = fsockopen($host, $port, $errno, $errstr, $timeout);
		}
		catch(Throwable $exception){
			$this->socket = false;
		}

		//  Restore the error handler
		restore_error_handler();

		//  Did we connect?
		if($this->socket === false){
			//  It would appear not...
			$this->logger->error(sprintf($this->lang->string('pop3_socket_error'), $host, $port, $errno, $errstr));

			return false;
		}

		//  Increase the stream time-out
		stream_set_timeout($this->socket, $timeout, 0);

		// Get the POP3 server response
		// Check for the +OK
		if($this->checkResponse($this->getResponse())){
			//  The connection is established and the POP3 server is talking
			$this->connected = true;

			return true;
		}

		return false;
	}

	/**
	 * Log in to the POP3 server.
	 * Does not support APOP (RFC 2828, 4949).
	 *
	 * @param string|null $username
	 * @param string|null $password
	 *
	 * @return bool
	 */
	public function login(string $username = null, string $password = null):bool{

		if(!$this->connected){
			$this->logger->error($this->lang->string('pop3_not_connected'));
		}

		// Send the Username
		$this->sendString('USER '.($username ?? $this->username ?? '')."\n");

		if($this->checkResponse($this->getResponse())){

			// Send the Password
			$this->sendString('PASS '.($password ?? $this->password ?? '')."\n");

			if($this->checkResponse($this->getResponse())){
				return true;
			}
		}

		return false;
	}

	/**
	 * Disconnect from the POP3 server.
	 */
	public function disconnect(){

		//The QUIT command may cause the daemon to exit, which will kill our connection
		//So ignore errors here
		try{
			$this->sendString('QUIT');

			if(is_resource($this->socket)){
				fclose($this->socket);
			}
		}
		catch(Throwable $e){
			//Do nothing
		}

	}

	/**
	 * Get a response from the POP3 server.
	 *
	 * @param int $size The maximum number of bytes to retrieve
	 *
	 * @return string
	 */
	protected function getResponse(int $size = null):string{
		$response = fgets($this->socket, $size ?? 128);
		$this->logger->debug(sprintf($this->lang->string('server_client'), $response));

		if($response === false){
			$this->logger->error($this->lang->string('pop3_response'));

			return '';
		}

		return $response;
	}

	/**
	 * Send raw data to the POP3 server.
	 *
	 * @param string $string
	 *
	 * @return int
	 */
	protected function sendString(string $string):int{
		$this->logger->debug(sprintf($this->lang->string('client_server'), $string));
		$bytes_written = fwrite($this->socket, $string, strlen($string));

		if($bytes_written === false){
			$this->logger->error($this->lang->string('pop3_request'));

			return 0;
		}

		return $bytes_written;
	}

	/**
	 * Checks the POP3 server response.
	 * Looks for for +OK or -ERR.
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	protected function checkResponse(string $string):bool{

		if(substr($string, 0, 3) !== '+OK'){
			$this->logger->error(sprintf($this->lang->string('pop3_server_error'), $string));

			return false;
		}

		return true;
	}

}
