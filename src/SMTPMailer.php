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

use function count, defined, explode, preg_match, trim;

class SMTPMailer extends PHPMailer{

	/**
	 * SMTP mandates RFC-compliant line endings
	 *
	 * @var string
	 */
	protected $LE = "\r\n";

	/**
	 * An instance of the SMTP sender class.
	 *
	 * @var \PHPMailer\PHPMailer\SMTP
	 */
	protected $smtp;

	/**
	 * Destructor.
	 */
	public function __destruct(){
		//Close any open SMTP connection nicely
		$this->closeSMTP();
	}

	public function postSend():bool{
		return $this->smtpSend($this->MIMEHeader, $this->MIMEBody);
	}

	/**
	 * Get an instance to use for SMTP operations.
	 * Override this function to load your own SMTP implementation,
	 * or set one with setSMTP.
	 *
	 * @return SMTP
	 */
	public function getSMTP():SMTP{

		if(!$this->smtp instanceof SMTP){
			$this->smtp = new SMTP;
		}

		return $this->smtp;
	}

	/**
	 * Provide an instance to use for SMTP operations.
	 *
	 * @param SMTP $smtp
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function setSMTP(SMTP $smtp):PHPMailer{
		$this->smtp = $smtp;

		return $this;
	}

	/**
	 * Close the active SMTP session if one exists.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function closeSMTP():PHPMailer{
		if($this->smtp instanceof SMTP){
			if($this->smtp->connected()){
				$this->smtp->quit();
				$this->smtp->close();
			}
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
	 *
	 * @see  PHPMailer::setSMTP() to use a different class.
	 *
	 * @uses \PHPMailer\PHPMailer\SMTP
	 *
	 */
	protected function smtpSend(string $header, string $body):bool{
		$header   = rtrim($header, "\r\n ").$this->LE.$this->LE;
		$bad_rcpt = 0;

		if(!$this->smtpConnect($this->SMTPOptions)){
			throw new PHPMailerException($this->lang('smtp_connect_failed'));
		}
		//Sender already validated in preSend()
		$smtp_from = empty($this->Sender) ? $this->From : $this->Sender;

		if(!$this->smtp->mail($smtp_from)){
			$msg = $this->lang('from_failed').$smtp_from;
			$this->logger->error($msg);

			throw new PHPMailerException($msg);
		}

		$callbacks = [];
		// Attempt to send to all recipients
		foreach([$this->to, $this->cc, $this->bcc] as $togroup){
			foreach($togroup as $to){

				if(!$this->smtp->recipient($to[0], $this->dsn)){
					$bad_rcpt++;
					$this->logger->error('Recipient failed: '.$to[0]);
					$isSent     = false;
				}
				else{
					$isSent = true;
				}

				$callbacks[] = ['issent' => $isSent, 'to' => $to[0]];
			}
		}

		// Only send the DATA command if we have viable recipients
		if((count($this->all_recipients) > $bad_rcpt) && !$this->smtp->data($header.$body)){
			throw new PHPMailerException($this->lang('data_not_accepted'));
		}

		$smtp_transaction_id = $this->smtp->getLastTransactionID();

		if($this->SMTPKeepAlive){
			$this->smtp->reset();
		}
		else{
			$this->smtp->quit();
			$this->smtp->close();
		}

		foreach($callbacks as $cb){
			$this->doCallback(
				$cb['issent'],
				[$cb['to']],
				[],
				[],
				$this->Subject,
				$body,
				$this->From,
				['smtp_transaction_id' => $smtp_transaction_id]
			);
		}

		//Create error message for any bad addresses
		if($bad_rcpt > 0){
			$this->logger->error($this->lang('recipients_failed'));

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
	 *
	 * @uses \PHPMailer\PHPMailer\SMTP
	 *
	 */
	public function smtpConnect(array $options = null):bool{
		$this->getSMTP();

		$this->smtp->setLogger($this->logger);

		// Already connected?
		if($this->smtp->connected()){
			return true;
		}

		$this->smtp->timeout  = $this->timeout;
		$this->smtp->do_verp  = $this->do_verp;

		$hosts         = explode(';', $this->host);
		$lastexception = null;

		foreach($hosts as $hostentry){
			$hostinfo = [];

			/** @noinspection RegExpRedundantEscape */
			if(!preg_match('/^((ssl|tls):\/\/)*([a-zA-Z0-9\.-]*|\[[a-fA-F0-9:]+\]):?([0-9]*)$/', trim($hostentry), $hostinfo)){
				$this->logger->warning($this->lang('connect_host').' '.$hostentry);
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
				$this->logger->warning($this->lang('connect_host').' '.$hostentry);

				continue;
			}

			$prefix = '';
			$secure = $this->SMTPSecure;
			$tls    = $this->SMTPSecure === $this::ENCRYPTION_STARTTLS;

			if($hostinfo[2] === $this::ENCRYPTION_SMTPS || ($hostinfo[2] === '' && $this->SMTPSecure === $this::ENCRYPTION_SMTPS)){
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
			$port    = $this->port ?? $this::DEFAULT_PORT_SMTP;
			$options = $options ?? $this->SMTPOptions;
			$tport   = (int)$hostinfo[4];

			if($tport > 0 && $tport < 65536){
				$port = $tport;
			}

			if($this->smtp->connect($prefix.$host, $port, $this->timeout, $options)){

				try{
					$hello = $this->Helo ?: $this->serverHostname();

					$this->smtp->hello($hello);
					//Automatically enable TLS encryption if:
					// * it's not disabled
					// * we have openssl extension
					// * we are not already using SSL
					// * the server offers STARTTLS
					if(
						$this->SMTPAutoTLS
						&& $sslext
						&& $secure !== $this::ENCRYPTION_SMTPS
						&& $this->smtp->getServerExt('STARTTLS')
					){
						$tls = true;
					}

					if($tls){
						if(!$this->smtp->startTLS()){
							throw new PHPMailerException($this->lang('connect_host'));
						}
						// We must resend EHLO after TLS negotiation
						$this->smtp->hello($hello);
					}

					if($this->SMTPAuth){
						if(!$this->smtp->authenticate($this->username, $this->password, $this->AuthType, $this->oauth)){
							throw new PHPMailerException($this->lang('authenticate'));
						}
					}

					return true;
				}
				catch(PHPMailerException $e){
					$lastexception = $e;
					$this->logger->error($e->getMessage());
					// We must have connected, but then failed TLS or Auth, so close connection nicely
					$this->smtp->quit();
				}

			}
		}
		// If we get here, all connection attempts have failed, so close connection hard
		$this->smtp->close();
		// As we've caught all exceptions, just report whatever the last one was
		if($lastexception !== null){
			throw $lastexception;
		}

		return false;
	}

}
