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

use function fclose, fgets, fsockopen, fwrite, implode, is_resource, restore_error_handler,
	set_error_handler, sprintf, stream_set_timeout, strlen, substr;

use const PHP_EOL;

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
class POP3 extends MailerAbstract{

	/**
	 * Are we connected?
	 *
	 * @var bool
	 */
	protected $connected = false;

	/**
	 * Error container.
	 *
	 * @var array
	 */
	protected $errors = [];

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
	 * @param int|null    $debug_level
	 *
	 * @return bool
	 */
	public function authorise(
		string $host,
		int $port = null,
		int $timeout = null,
		string $username = null,
		string $password = null,
		int $debug_level = null
	):bool{
		$this->loglevel = $debug_level ?? $this::DEBUG_OFF;
		$this->errors   = [];

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
		set_error_handler([$this, 'catchWarning']);

		//  connect to the POP3 server
		$this->socket = fsockopen($host, $port, $errno, $errstr, $timeout);

		//  Restore the error handler
		restore_error_handler();

		//  Did we connect?
		if($this->socket === false){
			//  It would appear not...
			$this->setError(
				sprintf('Failed to connect to server %s on port %s. errno: %s; errstr: %s', $host, $port, $errno, $errstr)
			);

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
			$this->setError('Not connected to POP3 server');
		}

		$username = $username ?? $this->username ?? '';
		$password = $password ?? $this->password ?? '';

		// Send the Username
		$this->sendString('USER '.$username.$this->LE);

		if($this->checkResponse($this->getResponse())){

			// Send the Password
			$this->sendString('PASS '.$password.$this->LE);

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
		$this->sendString('QUIT');

		//The QUIT command may cause the daemon to exit, which will kill our connection
		//So ignore errors here
		try{
			if(is_resource($this->socket)){
				fclose($this->socket);
			}
		}
		catch(PHPMailerException $e){
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
	protected function getResponse(int $size = 128):string{
		$response = fgets($this->socket, $size);
		$this->edebug('Server -> Client: '.$response, $this::DEBUG_CLIENT);

		if($response === false){
			$this->setError('fgets error');

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
		$this->edebug('Client -> Server: '.$string, $this::DEBUG_SERVER);
		$bytes_written = fwrite($this->socket, $string, strlen($string));

		if($bytes_written === false){
			$this->setError('fwrite error');

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
			$this->setError(sprintf('Server reported an error: %s', $string));

			return false;
		}

		return true;
	}

	/**
	 * Add an error to the internal error store.
	 * Also display debug output if it's enabled.
	 *
	 * @param string $error
	 */
	protected function setError(string $error):void{
		$this->errors[] = $error;
		$this->edebug(implode(PHP_EOL, $this->errors), $this::DEBUG_CLIENT);
	}

	/**
	 * Get an array of error messages, if any.
	 *
	 * @return array
	 */
	public function getErrors():array{
		return $this->errors;
	}

	/**
	 * POP3 connection error handler.
	 *
	 * @param int    $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int    $errline
	 */
	protected function catchWarning(int $errno, string $errstr, string $errfile, int $errline):void{
		$this->setError(
			sprintf(
				'Connecting to the POP3 server raised a PHP warning: errno: %s errstr: %s; errfile: %s; errline: %s',
				$errno,
				$errstr,
				$errfile,
				$errline
			)
		);
	}

}
