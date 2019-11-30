<?php
/**
 * Class SMTPMailer
 *
 * @filesource   SMTPMailer.php
 * @created      14.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use Throwable;

use function array_key_exists, array_shift, base64_decode, base64_encode, count, defined, explode, fclose, feof,
	fgets, fsockopen, function_exists, fwrite, hash_hmac, implode, in_array, ini_get, is_array, is_resource, md5,
	pack, preg_match, preg_replace, restore_error_handler, set_error_handler, set_time_limit, sprintf, str_pad,
	str_replace, stream_context_create, stream_get_meta_data, stream_select, stream_set_timeout, stream_socket_client,
	stream_socket_enable_crypto, strlen, strpos, strrpos, strtoupper, substr, time, trim;

use const PHP_OS_FAMILY, STREAM_CLIENT_CONNECT, STREAM_CRYPTO_METHOD_TLS_CLIENT,
	STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

class SMTPMailer extends PHPMailer{

	public const ENCRYPTION_STARTTLS = 'tls';
	public const ENCRYPTION_SMTPS    = 'ssl';

	/**
	 * The SMTP port to use if one is not specified.
	 *
	 * @var int
	 */
	public const DEFAULT_PORT_SMTP     = 25;
	public const DEFAULT_PORT_SMTPS    = 465;
	public const DEFAULT_PORT_STARTTLS = 587;

	/**
	 * Patterns to extract an SMTP transaction id from reply to a DATA command.
	 * The first capture group in each regex will be used as the ID.
	 * MS ESMTP returns the message ID, which may not be correct for internal tracking.
	 *
	 * @var string[]
	 */
	protected const smtp_transaction_id_patterns = [
		'exim'            => '/[\d]{3} OK id=(.*)/',
		'sendmail'        => '/[\d]{3} 2.0.0 (.*) Message/',
		'postfix'         => '/[\d]{3} 2.0.0 Ok: queued as (.*)/',
		'Microsoft_ESMTP' => '/[0-9]{3} 2.[\d].0 (.*)@(?:.*) Queued mail for delivery/',
		'Amazon_SES'      => '/[\d]{3} Ok (.*)/',
		'SendGrid'        => '/[\d]{3} Ok: queued as (.*)/',
		'CampaignMonitor' => '/[\d]{3} 2.0.0 OK:([a-zA-Z\d]{48})/',
	];

	/**
	 * The last transaction ID issued in response to a DATA command,
	 * if one was detected.
	 *
	 * @var string|null
	 */
	protected $last_smtp_transaction_id;

	/**
	 * The set of SMTP extensions sent in reply to EHLO command.
	 * Indexes of the array are extension names.
	 * Value at index 'HELO' or 'EHLO' (according to command that was sent)
	 * represents the server name. In case of HELO it is the only element of the array.
	 * Other values can be boolean TRUE or an array containing extension options.
	 * If null, no HELO/EHLO string has yet been received.
	 *
	 * @var array|null
	 */
	protected $server_caps = null;

	/**
	 * The most recent reply received from the server.
	 *
	 * @var string
	 */
	protected $last_reply = '';

	/**
	 * SMTP mandates RFC-compliant line endings
	 *
	 * @var string
	 */
	protected $LE = "\r\n";

	/**
	 * Destructor.
	 */
	public function __destruct(){
		//Close any open SMTP connection nicely
		$this->closeSMTP();
	}

	/**
	 * @return bool
	 */
	protected function postSend():bool{
		return $this->smtpSend($this->mimeHeader, $this->mimeBody);
	}

	/**
	 * Close the active SMTP session if one exists.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	protected function closeSMTP():PHPMailer{

		if($this->connected()){
			$this->quit();
			$this->close();
		}


		return $this;
	}

	/**
	 * Send mail via SMTP.
	 * Returns false if there is a bad MAIL FROM, RCPT, or DATA input.
	 *
	 * @param string $header The message headers
	 * @param string $body   The message body
	 *
	 * @return bool
	 * @throws PHPMailerException
	 */
	protected function smtpSend(string $header, string $body):bool{
		$header   = rtrim($header, "\r\n ").$this->LE.$this->LE;
		$bad_rcpt = [];

		if(!$this->smtpConnect($this->options->smtp_stream_context_options)){
			throw new PHPMailerException($this->lang->string('smtp_connect_failed'));
		}
		//Sender already validated in preSend()
		$smtp_from = empty($this->sender) ? $this->from : $this->sender;

		if(!$this->mail($smtp_from)){
			throw new PHPMailerException(sprintf($this->lang->string('from_failed'), $smtp_from));
		}

		$callbacks = [];
		// Attempt to send to all recipients
		foreach([$this->to, $this->cc, $this->bcc] as $togroup){
			foreach($togroup as $to){
				$isSent = true;

				if(!$this->recipient($to[0], $this->options->smtp_dsn)){
					$bad_rcpt[] = $to[0];
					$isSent = false;
				}

				$callbacks[] = ['issent' => $isSent, 'to' => $to[0]];
			}
		}

		// Only send the DATA command if we have viable recipients
		if((count($this->allRecipients) > count($bad_rcpt)) && !$this->data($header.$body)){
			throw new PHPMailerException($this->lang->string('data_not_accepted'));
		}

		if($this->options->smtp_keepalive){
			$this->reset();
		}
		else{
			$this->quit();
			$this->close();
		}

		foreach($callbacks as $cb){
			$this->doCallback(
				$cb['issent'],
				[$cb['to']],
				[],
				[],
				$this->subject,
				$body,
				$this->from,
				['smtp_transaction_id' => $this->last_smtp_transaction_id]
			);
		}

		//Create error message for any bad addresses
		if(!empty($bad_rcpt)){
			$this->logger->error(sprintf($this->lang->string('recipients_failed'), implode(', ', $bad_rcpt)));

			return false;
		}

		return true;
	}

	/**
	 * @todo: make protected
	 *
	 * Initiate a connection to an SMTP server.
	 * Returns false if the operation failed.
	 *
	 * @param array $options An array of options compatible with stream_context_create()
	 *
	 * @return bool
	 * @throws PHPMailerException
	 */
	protected function smtpConnect(array $options = null):bool{
		$this->setLogger($this->logger);

		// Already connected?
		if($this->connected()){
			return true;
		}

		$hosts         = explode(';', $this->options->smtp_host);
		$lastexception = null;

		foreach($hosts as $hostentry){
			$hostinfo = [];

			/** @noinspection RegExpRedundantEscape */
			if(!preg_match('/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*|\[[a-fA-F0-9:]+\]):?([0-9]*)$/', trim($hostentry), $hostinfo)){
				$this->logger->warning($this->lang->string('connect_host').' '.$hostentry);
				// Not a valid host entry
				continue;
			}

			// $hostinfo[2]: optional ssl or tls prefix
			// $hostinfo[3]: the hostname
			// $hostinfo[4]: optional port number
			// The host string prefix can temporarily override the current setting for SMTPSecure
			// If it's not specified, the default value is used

			//Check the host name is a valid name or IP address before trying to use it
			if(!isValidHost($hostinfo[3])){
				$this->logger->warning($this->lang->string('connect_host').' '.$hostentry);

				continue;
			}

			$prefix = '';
			$secure = $this->options->smtp_encryption;
			$tls    = $this->options->smtp_encryption === $this::ENCRYPTION_STARTTLS;

			if(
				$hostinfo[2] === $this::ENCRYPTION_SMTPS
				|| ($hostinfo[2] === '' && $this->options->smtp_encryption === $this::ENCRYPTION_SMTPS)
			){
				$prefix = 'ssl://';
				$tls    = false; // Can't have SSL and TLS at the same time
				$secure = $this::ENCRYPTION_SMTPS;
			}
			elseif($hostinfo[2] === $this::ENCRYPTION_STARTTLS){
				$tls = true;
				// tls doesn't use a prefix
				$secure = $this::ENCRYPTION_STARTTLS;
			}

			$host    = $hostinfo[3];
			$port    = $this->options->smtp_port;
			$options = $options ?? $this->options->smtp_stream_context_options;
			$tport   = (int)$hostinfo[4];

			if($tport > 0 && $tport < 65536){
				$port = $tport;
			}

			if($this->connect($prefix.$host, $port, $this->options->smtp_timeout, $options)){

				try{
					$hello = $this->options->hostname ?? $this->serverHostname();

					$this->hello($hello);
					//Automatically enable TLS encryption if:
					// * it's not disabled
					// * we are not already using SSL
					// * the server offers STARTTLS
					if(
						$this->options->smtp_auto_tls
						&& $secure !== $this::ENCRYPTION_SMTPS
						&& $this->getServerExt('STARTTLS')
					){
						$tls = true;
					}

					if($tls){
						if(!$this->startTLS()){
							throw new PHPMailerException($this->lang->string('connect_host'));
						}
						// We must resend EHLO after TLS negotiation
						$this->hello($hello);
					}

					if($this->options->smtp_auth){
						if(!$this->authenticate(
							$this->options->smtp_username,
							$this->options->smtp_password,
							$this->options->smtp_authtype
						)){
							throw new PHPMailerException($this->lang->string('authenticate'));
						}
					}

					return true;
				}
				catch(PHPMailerException $e){
					$lastexception = $e;
					$this->logger->error($e->getMessage());
					// We must have connected, but then failed TLS or Auth, so close connection nicely
					$this->quit();
				}

			}
		}
		// If we get here, all connection attempts have failed, so close connection hard
		$this->close();
		// As we've caught all exceptions, just report whatever the last one was
		if($lastexception !== null){
			throw $lastexception;
		}

		return false;
	}

	/**
	 * Connect to an SMTP server.
	 *
	 * @param string $host           SMTP server IP or host name
	 * @param int    $port           The port number to connect to
	 * @param int    $timeout        How long to wait for the connection to open
	 * @param array  $stream_options An array of options for stream_context_create()
	 *
	 * @return bool
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	protected function connect(string $host, int $port = null, int $timeout = 30, array $stream_options = []):bool{

		// Make sure we are __not__ connected
		if($this->connected()){
			// Already connected, generate error
			$this->logger->error($this->lang->string('already_connected'));

			return false;
		}

		$port = $port ?? $this::DEFAULT_PORT_SMTP;

		// Connect to the SMTP server
		$this->logger->debug(sprintf($this->lang->string('smtp_open'), $host, $port, $timeout), $stream_options);

		$errno  = 0;
		$errstr = '';

		set_error_handler([$this, 'errorHandler']);

		try{

			if($this->streamOK){
				$socket_context = stream_context_create($stream_options);

				$this->socket = stream_socket_client(
					$host.':'.$port,
					$errno,
					$errstr,
					$timeout,
					STREAM_CLIENT_CONNECT,
					$socket_context
				);

			}
			else{
				//Fall back to fsockopen which should work in more places, but is missing some features
				$this->logger->debug($this->lang->string('smtp_nostream'));

				$this->socket = fsockopen($host, $port, $errno, $errstr, $timeout);
			}

		}
		catch(Throwable $exception){
			throw new PHPMailerException($exception->getMessage());
		}

		restore_error_handler();

		// Verify we connected properly
		if(!is_resource($this->socket)){
			$this->logger->error(sprintf($this->lang->string('smtp_connect_error'), $errstr, $errno));

			return false;
		}

		$this->logger->debug($this->lang->string('smtp_dbg_open'));

		// SMTP server can take longer to respond, give longer timeout for first read
		// Windows does not have support for this timeout function
		if(PHP_OS_FAMILY !== 'Windows'){
			$max = (int)ini_get('max_execution_time');

			// Don't bother if unlimited
			if($max !== 0 && $timeout > $max){
				set_time_limit($timeout);
			}

			stream_set_timeout($this->socket, $timeout, 0);
		}

		// Get any announcement
		$announce = $this->getLines();
		$this->logger->debug(sprintf($this->lang->string('server_client'), $announce));

		return true;
	}

	/**
	 * Initiate a TLS (encrypted) session.
	 *
	 * @return bool
	 */
	protected function startTLS():bool{

		if(!$this->sendCommand('STARTTLS', 'STARTTLS', [220])){
			return false;
		}

		//Allow the best TLS version(s) we can
		$crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;

		//PHP 5.6.7 dropped inclusion of TLS 1.1 and 1.2 in STREAM_CRYPTO_METHOD_TLS_CLIENT
		//so add them back in manually if we can
		if(defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')){
			$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
			$crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
		}

		// Begin encrypted connection
		set_error_handler([$this, 'errorHandler']);

		try{
			$crypto_ok = (bool)stream_socket_enable_crypto($this->socket, true, $crypto_method);
		}
		catch(Throwable $exception){
			$this->logger->warning($this->lang->string('stream_crypto_fail'));
			$crypto_ok = false;
		}

		restore_error_handler();

		return $crypto_ok;
	}

	/**
	 * Perform SMTP authentication.
	 * Must be run after hello().
	 *
	 * @param string                  $username The user name
	 * @param string                  $password The password
	 * @param string                  $authtype The auth type (CRAM-MD5, PLAIN, LOGIN, XOAUTH2)
	 *
	 * @return bool True if successfully authenticated
	 * @see    hello()
	 */
	protected function authenticate(
		string $username,
		string $password,
		string $authtype = null
	):bool{

		if(!$this->server_caps){
			$this->logger->error($this->lang->string('auth_before_helo'));

			return false;
		}

		if(array_key_exists('EHLO', $this->server_caps)){
			// SMTP extensions are available; try to find a proper authentication method
			if(!array_key_exists('AUTH', $this->server_caps)){
				// 'at this stage' means that auth may be allowed after the stage changes
				// e.g. after STARTTLS
				$this->logger->error($this->lang->string('auth_stage'));

				return false;
			}

			$this->logger->debug(sprintf($this->lang->string('dbg_auth_requested'), $authtype ? $authtype : 'UNSPECIFIED'));
			$this->logger->debug(sprintf($this->lang->string('dbg_auth_available'), implode(',', $this->server_caps['AUTH'])));

			// If we have requested a specific auth type, check the server supports it before trying others
			if($authtype !== null && !in_array($authtype, $this->server_caps['AUTH'])){
				$this->logger->warning(sprintf($this->lang->string('auth_unavailable'), $authtype));
				$authtype = null;
			}

			if(empty($authtype)){
				// If no auth mechanism is specified, attempt to use these, in this order
				// Try CRAM-MD5 first as it's more secure than the others
				foreach(['CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2'] as $method){
					if(in_array($method, $this->server_caps['AUTH'])){
						$authtype = $method;
						break;
					}
				}

				if(empty($authtype)){
					$this->logger->error($this->lang->string('no_supported_auth'));

					return false;
				}

				$this->logger->debug(sprintf($this->lang->string('dbg_auth_method'), $authtype));
			}

			if(!in_array($authtype, $this->server_caps['AUTH'])){
				$this->logger->error(sprintf($this->lang->string('auth_unsupported'), $authtype));

				return false;
			}

		}
		elseif(empty($authtype)){
			$authtype = 'LOGIN';
		}

		switch($authtype){
			case 'PLAIN':
				// Start authentication
				if(!$this->sendCommand('AUTH', 'AUTH PLAIN', [334])){
					return false;
				}
				// Send encoded username and password
				if(!$this->sendCommand('User & Password', base64_encode("\0".$username."\0".$password), [235])){
					return false;
				}

				break;

			case 'LOGIN':
				// Start authentication
				if(!$this->sendCommand('AUTH', 'AUTH LOGIN', [334])){
					return false;
				}

				if(!$this->sendCommand('Username', base64_encode($username), [334])){
					return false;
				}

				if(!$this->sendCommand('Password', base64_encode($password), [235])){
					return false;
				}

				break;

			case 'CRAM-MD5':
				// Start authentication
				if(!$this->sendCommand('AUTH CRAM-MD5', 'AUTH CRAM-MD5', [334])){
					return false;
				}
				// Get the challenge
				$challenge = base64_decode(substr($this->last_reply, 4));

				// Build the response
				$response = $username.' '.$this->hmac($challenge, $password);

				// send encoded credentials
				return $this->sendCommand('Username', base64_encode($response), [235]);

			case 'XOAUTH2':
				// Start authentication
				$auth = base64_encode(sprintf("user=%s\001auth=Bearer %s\001\001", $username, $password));
				if(!$this->sendCommand('AUTH', 'AUTH XOAUTH2 '.$auth, [235])){
					return false;
				}

				break;

			default:
				$this->logger->error(sprintf($this->lang->string('auth_unsupported'), $authtype));

				return false;
		}

		return true;
	}

	/**
	 * Calculate an MD5 HMAC hash.
	 * Works like hash_hmac('md5', $data, $key)
	 * in case that function is not available.
	 *
	 * @param string $data The data to hash
	 * @param string $key  The key to hash with
	 *
	 * @return string
	 */
	protected function hmac(string $data, string $key):string{

		if(function_exists('hash_hmac')){
			return hash_hmac('md5', $data, $key);
		}

		// The following borrowed from
		// http://php.net/manual/en/function.mhash.php#27225

		// RFC 2104 HMAC implementation for php.
		// Creates an md5 HMAC.
		// Eliminates the need to install mhash to compute a HMAC
		// by Lance Rushing

		$bytelen = 64; // byte length for md5

		if(strlen($key) > $bytelen){
			$key = pack('H*', md5($key));
		}

		$key    = str_pad($key, $bytelen, "\x00");
		$ipad   = str_pad('', $bytelen, "\x36");
		$opad   = str_pad('', $bytelen, "\x5c");
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return md5($k_opad.pack('H*', md5($k_ipad.$data)));
	}

	/**
	 * Check connection state.
	 *
	 * @return bool True if connected
	 */
	protected function connected():bool{

		if(!is_resource($this->socket)){
			return false;
		}

		$sock_status = stream_get_meta_data($this->socket);

		if($sock_status['eof']){
			// The socket is valid but we are not connected
			$this->logger->debug($this->lang->string('dbg_eof'));
			$this->close();

			return false;
		}

		return true; // everything looks good
	}

	/**
	 * Close the socket and clean up the state of the class.
	 * Don't use this function without first trying to use QUIT.
	 *
	 * @return \PHPMailer\PHPMailer\SMTPMailer
	 * @see quit()
	 *
	 */
	protected function close():SMTPMailer{
		$this->server_caps = null;

		if(is_resource($this->socket)){
			// close the connection and cleanup
			fclose($this->socket);
			$this->socket = null; //Makes for cleaner serialization
			$this->logger->debug($this->lang->string('smtp_closed'));
		}

		return $this;
	}

	/**
	 * Send an SMTP DATA command.
	 * Issues a data command and sends the msg_data to the server,
	 * finializing the mail transaction. $msg_data is the message
	 * that is to be send with the headers. Each header needs to be
	 * on a single line followed by a <CRLF> with the message headers
	 * and the message body being separated by an additional <CRLF>.
	 * Implements RFC 821: DATA <CRLF>.
	 *
	 * @param string $msg_data Message data to send
	 *
	 * @return bool
	 */
	protected function data(string $msg_data):bool{

		//This will use the standard timelimit
		if(!$this->sendCommand('DATA', 'DATA', [354])){
			return false;
		}

		/* The server is ready to accept data!
		 * According to rfc821 we should not send more than 1000 characters on a single line (including the LE)
		 * so we will break the data up into lines by \r and/or \n then if needed we will break each of those into
		 * smaller lines to fit within the limit.
		 * We will also look for lines that start with a '.' and prepend an additional '.'.
		 * NOTE: this does not count towards line-length limit.
		 */

		// Normalize line breaks before exploding
		$lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $msg_data));

		/* To distinguish between a complete RFC822 message and a plain message body, we check if the first field
		 * of the first line (':' separated) does not contain a space then it _should_ be a header and we will
		 * process all lines before a blank line as headers.
		 */

		$field      = substr($lines[0], 0, strpos($lines[0], ':'));
		$in_headers = false;

		if(!empty($field) && strpos($field, ' ') === false){
			$in_headers = true;
		}

		foreach($lines as $line){
			$lines_out = [];

			if($in_headers && $line === ''){
				$in_headers = false;
			}

			//Break this line up into several smaller lines if it's too long
			//Micro-optimisation: isset($str[$len]) is faster than (strlen($str) > $len),
			while(isset($line[$this::LINE_LENGTH_MAX])){
				//Working backwards, try to find a space within the last MAX_LINE_LENGTH chars of the line to break on
				//so as to avoid breaking in the middle of a word
				$pos = strrpos(substr($line, 0, $this::LINE_LENGTH_MAX), ' ');
				//Deliberately matches both false and 0
				if(!$pos){
					//No nice break found, add a hard break
					$pos         = $this::LINE_LENGTH_MAX - 1;
					$lines_out[] = substr($line, 0, $pos);
					$line        = substr($line, $pos);
				}
				else{
					//Break at the found point
					$lines_out[] = substr($line, 0, $pos);
					//Move along by the amount we dealt with
					$line = substr($line, $pos + 1);
				}
				//If processing headers add a LWSP-char to the front of new line RFC822 section 3.1.1
				if($in_headers){
					$line = "\t".$line;
				}
			}

			$lines_out[] = $line;

			//Send the lines to the server
			foreach($lines_out as $line_out){
				//RFC2821 section 4.5.2
				if(!empty($line_out) && $line_out[0] == '.'){
					$line_out = '.'.$line_out;
				}

				$this->client_send($line_out.$this->LE, 'DATA');
			}
		}

		//Message data has been sent, complete the command
		//Increase timelimit for end of DATA command
		$savetimelimit                  = $this->options->smtp_timeout;
		$this->options->smtp_timeout    *= 2;
		$result                         = $this->sendCommand('DATA END', '.', [250]);
		$this->last_smtp_transaction_id = $this->recordLastTransactionID();
		//Restore timelimit
		$this->options->smtp_timeout    = $savetimelimit;

		return $result;
	}

	/**
	 * Send an SMTP HELO or EHLO command.
	 * Used to identify the sending server to the receiving server.
	 * This makes sure that client and server are in a known state.
	 * Implements RFC 821: HELO <SP> <domain> <CRLF>
	 * and RFC 2821 EHLO.
	 *
	 * @param string $host The host name or IP to connect to
	 *
	 * @return bool
	 */
	protected function hello(string $host = ''):bool{
		//Try extended hello first (RFC 2821)
		return $this->sendHello('EHLO', $host) || $this->sendHello('HELO', $host);
	}

	/**
	 * Send an SMTP HELO or EHLO command.
	 * Low-level implementation used by hello().
	 *
	 * @param string $hello The HELO string
	 * @param string $host  The hostname to say we are
	 *
	 * @return bool
	 *
	 * @see    hello()
	 */
	protected function sendHello(string $hello, string $host):bool{
		$noerror           = $this->sendCommand($hello, $hello.' '.$host, [250]);
		$this->server_caps = $noerror ? $this->parseHelloFields($hello, $this->last_reply) : null;

		return $noerror;
	}

	/**
	 * Parse a reply to HELO/EHLO command to discover server extensions.
	 * In case of HELO, the only parameter that can be discovered is a server name.
	 *
	 * @param string $type `HELO` or `EHLO`
	 * @param string $response
	 *
	 * @return array
	 */
	protected function parseHelloFields(string $type, string $response):array{
		$ret   = [];
		$lines = explode("\n", $response);

		foreach($lines as $n => $s){
			// First 4 chars contain response code followed by - or space
			$s = trim(substr($s, 4));

			if(empty($s)){
				continue;
			}

			$fields = explode(' ', $s);

			if(!empty($fields)){

				if(!$n){
					$name   = $type;
					$fields = $fields[0];
				}
				else{
					$name = array_shift($fields);

					switch($name){
						case 'SIZE':
							$fields = $fields ? $fields[0] : 0;
							break;
						case 'AUTH':
							if(!is_array($fields)){
								$fields = [];
							}
							break;
						default:
							$fields = true;
					}
				}

				$ret[$name] = $fields;
			}
		}

		return $ret;
	}

	/**
	 * Send an SMTP MAIL command.
	 * Starts a mail transaction from the email address specified in
	 * $from. Returns true if successful or false otherwise. If True
	 * the mail transaction is started and then one or more recipient
	 * commands may be called followed by a data command.
	 * Implements RFC 821: MAIL <SP> FROM:<reverse-path> <CRLF>.
	 *
	 * @param string $from Source address of this message
	 *
	 * @return bool
	 */
	protected function mail(string $from):bool{
		$useVerp = $this->options->smtp_verp ? ' XVERP' : '';

		return $this->sendCommand('MAIL FROM', 'MAIL FROM:<'.$from.'>'.$useVerp, [250]);
	}

	/**
	 * Send an SMTP QUIT command.
	 * Closes the socket if there is no error or the $close_on_error argument is true.
	 * Implements from RFC 821: QUIT <CRLF>.
	 *
	 * @param bool $close_on_error Should the connection close if an error occurs?
	 *
	 * @return bool
	 */
	protected function quit(bool $close_on_error = true):bool{
		$noerror = $this->sendCommand('QUIT', 'QUIT', [221]);
		// @todo: simplify
		if($noerror || $close_on_error){
			$this->close();
		}

		return $noerror;
	}

	/**
	 * Send an SMTP RCPT command.
	 * Sets the TO argument to $toaddr.
	 * Returns true if the recipient was accepted false if it was rejected.
	 * Implements from RFC 821: RCPT <SP> TO:<forward-path> <CRLF>.
	 *
	 * @param string $address The address the message is being sent to
	 * @param string $dsn     Comma separated list of DSN notifications. NEVER, SUCCESS, FAILURE
	 *                        or DELAY. If you specify NEVER all other notifications are ignored.
	 *
	 * @return bool
	 */
	protected function recipient(string $address, string $dsn = null):bool{

		if(empty($dsn)){
			$rcpt = 'RCPT TO:<'.$address.'>';
		}
		else{
			$dsn    = strtoupper($dsn);
			$notify = [];

			if(strpos($dsn, 'NEVER') !== false){
				$notify[] = 'NEVER';
			}
			else{
				foreach(['SUCCESS', 'FAILURE', 'DELAY'] as $value){
					if(strpos($dsn, $value) !== false){
						$notify[] = $value;
					}
				}
			}

			$rcpt = 'RCPT TO:<'.$address.'> NOTIFY='.implode(',', $notify);
		}

		return $this->sendCommand('RCPT TO', $rcpt, [250, 251]);
	}

	/**
	 * Send an SMTP RSET command.
	 * Abort any transaction that is currently in progress.
	 * Implements RFC 821: RSET <CRLF>.
	 *
	 * @return bool True on success
	 */
	protected function reset():bool{
		return $this->sendCommand('RSET', 'RSET', [250]);
	}

	/**
	 * Send a command to an SMTP server and check its return code.
	 *
	 * @param string $command       The command name - not sent to the server
	 * @param string $commandstring The actual command to send
	 * @param array  $expect        One or more expected integer success codes
	 *
	 * @return bool True on success
	 */
	protected function sendCommand(string $command, string $commandstring, array $expect):bool{

		if(!$this->connected()){
			$this->logger->error(sprintf($this->lang->string('cmd_unconnected'), $command));

			return false;
		}

		//Reject line breaks in all commands
		if(strpos($commandstring, "\n") !== false || strpos($commandstring, "\r") !== false){
			$this->logger->error(sprintf($this->lang->string('cmd_linebreaks'), $command));

			return false;
		}

		$this->client_send($commandstring.$this->LE, $command);

		$this->last_reply = $this->getLines();
		// Fetch SMTP code and possible error code explanation
		if(preg_match('/^([0-9]{3})[ -](?:([0-9]\\.[0-9]\\.[0-9]{1,2}) )?/', $this->last_reply, $matches)){
			$code    = $matches[1];
			$code_ex = count($matches) > 2 ? $matches[2] : null;
			// Cut off error code from each response line
			$detail = preg_replace(
				'/'.$code.'[ -]'.($code_ex ? str_replace('.', '\\.', $code_ex).' ' : '').'/m',
				'',
				$this->last_reply
			);
		}
		else{
			// Fall back to simple parsing if regex fails
			$code    = substr($this->last_reply, 0, 3);
			$code_ex = null;
			$detail  = substr($this->last_reply, 4);
		}

		$this->logger->debug(sprintf($this->lang->string('server_client'), $this->last_reply));

		if(!in_array($code, $expect)){
			$this->logger->error(
				sprintf($this->lang->string('command_failed'), $command, $this->last_reply),
				['detail' => $detail, 'code' => $code, 'code_ex' => $code_ex]
			);

			return false;
		}

		return true;
	}

	/**
	 * Send an SMTP SAML command.
	 * Starts a mail transaction from the email address specified in $from.
	 * Returns true if successful or false otherwise. If True
	 * the mail transaction is started and then one or more recipient
	 * commands may be called followed by a data command. This command
	 * will send the message to the users terminal if they are logged
	 * in and send them an email.
	 * Implements RFC 821: SAML <SP> FROM:<reverse-path> <CRLF>.
	 *
	 * @param string $from The address the message is from
	 *
	 * @return bool
	 */
	protected function sendAndMail(string $from):bool{
		return $this->sendCommand('SAML', "SAML FROM:$from", [250]);
	}

	/**
	 * Send an SMTP VRFY command.
	 *
	 * @param string $name The name to verify
	 *
	 * @return bool
	 */
	protected function verify(string $name):bool{
		return $this->sendCommand('VRFY', "VRFY $name", [250, 251]);
	}

	/**
	 * Send an SMTP NOOP command.
	 * Used to keep keep-alives alive, doesn't actually do anything.
	 *
	 * @return bool
	 */
	protected function noop():bool{
		return $this->sendCommand('NOOP', 'NOOP', [250]);
	}

	/**
	 * Send an SMTP TURN command.
	 * This is an optional command for SMTP that this class does not support.
	 * This method is here to make the RFC821 Definition complete for this class
	 * and _may_ be implemented in future.
	 * Implements from RFC 821: TURN <CRLF>.
	 *
	 * @return bool
	 */
	protected function turn():bool{
		$this->logger->error($this->lang->string('cmd_turn'));

		return false;
	}

	/**
	 * Send raw data to the server.
	 *
	 * @param string $data    The data to send
	 * @param string $command Optionally, the command this is part of, used only for controlling debug output
	 *
	 * @return int The number of bytes sent to the server (@todo: return value unused)
	 */
	protected function client_send(string $data, string $command = null):int{
		//If SMTP transcripts are left enabled, or debug output is posted online
		//it can leak credentials, so hide credentials in all but lowest level
		in_array($command, ['User & Password', 'Username', 'Password'], true)
			? $this->logger->debug(sprintf($this->lang->string('client_server'), '<credentials hidden>'))
			: $this->logger->debug(sprintf($this->lang->string('client_server'), $data));

		set_error_handler([$this, 'errorHandler']);

		try{
			$result = (int)fwrite($this->socket, $data);
		}
		catch(Throwable $exception){
			$result = 0;
		}

		restore_error_handler();

		return (int)$result;
	}

	/**
	 * Get metadata about the SMTP server from its HELO/EHLO response.
	 * The method works in three ways, dependent on argument value and current state:
	 *   1. HELO/EHLO has not been sent - returns null and populates $this->error.
	 *   2. HELO has been sent -
	 *     $name == 'HELO': returns server name
	 *     $name == 'EHLO': returns boolean false
	 *     $name == any other string: returns null and populates $this->error
	 *   3. EHLO has been sent -
	 *     $name == 'HELO'|'EHLO': returns the server name
	 *     $name == any other string: if extension $name exists, returns True
	 *       or its options (e.g. AUTH mechanisms supported). Otherwise returns False.
	 *
	 * @param string $name Name of SMTP extension or 'HELO'|'EHLO'
	 *
	 * @return string|null
	 */
	protected function getServerExt(string $name):?string{

		if(!$this->server_caps){
			$this->logger->error($this->lang->string('no_helo'));

			return null;
		}

		if(!array_key_exists($name, $this->server_caps)){

			if('HELO' == $name){
				return $this->server_caps['EHLO'];
			}

			if('EHLO' == $name || array_key_exists('EHLO', $this->server_caps)){
				return null;
			}

			$this->logger->error($this->lang->string('helo_noinfo'));

			return null;
		}

		return $this->server_caps[$name];
	}

	/**
	 * Read the SMTP server's response.
	 * Either before eof or socket timeout occurs on the operation.
	 * With SMTP we can tell if we have more lines to read if the
	 * 4th character is '-' symbol. If it is a space then we don't
	 * need to read anything else.
	 *
	 * @return string
	 */
	protected function getLines():string{
		// If the connection is bad, give up straight away
		if(!is_resource($this->socket)){
			return '';
		}

		$data    = '';
		$endtime = 0;

		stream_set_timeout($this->socket, $this->options->smtp_timeout);

		if($this->options->smtp_timeout > 0){
			$endtime = time() + $this->options->smtp_timeout;
		}

		$selR = [$this->socket];
		$selW = null;

		while(is_resource($this->socket) && !feof($this->socket)){
			// Must pass vars in here as params are by reference
			if(!stream_select($selR, $selW, $selW, $this->options->smtp_timeout)){
				$this->logger->debug(sprintf($this->lang->string('smtp_timeout'), $this->options->smtp_timeout));
				break;
			}

			// Deliberate noise suppression - errors are handled afterwards
			$str = @fgets($this->socket, $this::MAX_REPLY_LENGTH);
			$this->logger->debug(sprintf($this->lang->string('smtp_inbound'), trim($str)));
			$data .= $str;

			// If response is only 3 chars (not valid, but RFC5321 S4.2 says it must be handled),
			// or 4th character is a space or a line break char, we are done reading, break the loop,
			if (!isset($str[3]) || $str[3] === ' ' || $str[3] === "\r" || $str[3] === "\n"){
				break;
			}

			// Timed-out? Log and break
			$info = stream_get_meta_data($this->socket);
			if($info['timed_out']){
				$this->logger->debug(sprintf($this->lang->string('smtp_timeout'), $this->options->smtp_timeout));
				break;
			}

			// Now check if reads took too long
			if($endtime && time() > $endtime){
				$this->logger->debug(sprintf($this->lang->string('smtp_timelimit'), $this->options->smtp_timeout));
				break;
			}
		}

		return $data;
	}

	/**
	 * Extract and return the ID of the last SMTP transaction based on
	 * a list of patterns provided in SMTP::smtp_transaction_id_patterns.
	 * Relies on the host providing the ID in response to a DATA command.
	 * If no reply has been received yet or  no pattern was matched,
	 * it will return null.
	 *
	 * @return string|null
	 */
	protected function recordLastTransactionID():?string{

		foreach($this::smtp_transaction_id_patterns as $smtp_transaction_id_pattern){
			if(preg_match($smtp_transaction_id_pattern, $this->last_reply, $matches)){
				return trim($matches[1]);
			}
		}

		return null;
	}

}
