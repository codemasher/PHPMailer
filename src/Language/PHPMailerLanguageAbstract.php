<?php
/**
 * Class PHPMailerLanguageAbstract
 *
 * @filesource   PHPMailerLanguageAbstract.php
 * @created      20.11.2019
 * @package      PHPMailer\PHPMailer\Language
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer\Language;

use PHPMailer\PHPMailer\PHPMailerException;

use function property_exists, sprintf, strtolower;

abstract class PHPMailerLanguageAbstract implements PHPMailerLanguageInterface{

	/**
	 * The ISO-639-1 2-letter language code
	 *
	 * @link https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
	 *
	 * @var string
	 */
	protected $code = 'en';

	/**
	 * Direction
	 *  - LTR: left-to-right
	 *  - RTL: right-to-left
	 *
	 * @var string
	 */
	protected $dir = 'LTR';

	/**
	 * The translated language name
	 *
	 * @var string
	 */
	protected $name = 'English';

	/**
	 * The native language name
	 *
	 * @var string
	 */
	protected $native_name = 'English';

	// @todo: sort/group/rename/cleanup

	// common
	protected $server_client          = '[SRV > CLI] %s';
	protected $client_server          = '[CLI > SRV] %s';

	// smtp
	protected $authenticate           = 'SMTP Error: Could not authenticate.';
	protected $connect_host           = 'SMTP Error: Could not connect to SMTP host.';
	protected $data_not_accepted      = 'SMTP Error: data not accepted.';
	protected $smtp_connect_failed    = 'SMTP connect() failed.';
	protected $smtp_error             = 'SMTP server error: ';
	protected $recipients_failed      = 'SMTP Error: The following recipients failed: %s';
	protected $already_connected      = 'Already connected to a server';
	protected $smtp_open              = 'Connection: opening to %1$s:%2$d, timeout=%3$d';
	protected $smtp_nostream          = 'Connection: stream_socket_client not available, falling back to fsockopen';
	protected $smtp_connect_error     = 'SMTP Error: Failed to connect to server: %1$s (%2$d)';
	protected $smtp_dbg_open          = 'Connection: opened';
	protected $stream_crypto_fail     = 'stream_socket_enable_crypto failed';
	protected $auth_before_helo       = 'Authentication is not allowed before HELO/EHLO';
	protected $auth_stage             = 'Authentication is not allowed at this stage';
	protected $dbg_auth_requested     = 'Auth method requested: %s';
	protected $dbg_auth_available     = 'Auth methods available on the server: %s';
	protected $auth_unavailable       = 'Requested auth method not available: %s';
	protected $no_supported_auth      = 'No supported authentication methods found';
	protected $command_failed         = 'SMTP ERROR: %1$s command failed: %2$s';
	protected $dbg_auth_method        = 'Auth method selected: %s';
	protected $auth_unsupported       = 'The requested authentication method "%s" is not supported by the server';
	protected $dbg_eof                = 'SMTP NOTICE: EOF caught while checking if connected';
	protected $smtp_closed            = 'Connection: closed';
	protected $cmd_unconnected        = 'Called "%s" without being connected';
	protected $cmd_linebreaks         = 'Command "%s" contained line breaks';
	protected $cmd_turn               = 'SMTP NOTICE: The SMTP TURN command is not implemented';
	protected $no_helo                = 'No HELO/EHLO was sent';
	protected $helo_noinfo            = 'HELO handshake was used; No information about server extensions available';
	protected $smtp_inbound           = 'SMTP INBOUND: "%s"';
	protected $smtp_timeout           = 'SMTP timed-out (%d sec)';
	protected $smtp_timelimit         = 'SMTP timelimit reached (%d sec)';

	// pop3
	protected $pop3_socket_error      = 'Failed to connect to server %1$s on port %2$d. errno: %3$d; errstr: %4$s';
	protected $pop3_server_error      = 'POP3 server reported an error: %s';
	protected $pop3_not_connected     = 'Not connected to POP3 server';
	protected $pop3_request           = 'Error sending POP3 request (fwrite)';
	protected $pop3_response          = 'Error retrieving POP3 response (fgets)';



	protected $empty_message          = 'Message body empty';
	protected $encoding               = 'Unknown encoding: %s';
	protected $execute                = 'Could not execute: %s';
	protected $file_access            = 'Could not access file: %s';
	protected $file_open              = 'File Error: Could not open file: %s';
	protected $from_failed            = 'The following From address failed: %s';
	protected $instantiate            = 'Could not instantiate mail function.';
	protected $invalid_address        = 'Invalid address (%1$s): %2$s';
	protected $mailer_not_supported   = ' mailer is not supported.';
	protected $provide_address        = 'You must provide at least one recipient email address.';
	protected $signing                = 'Signing Error: %s';
	protected $variable_set           = 'Cannot set or reset variable: ';
	protected $extension_missing      = 'Extension missing: ';
	protected $lang_key               = 'invalid language-string key: %s';
	protected $sign_cert_file         = 'invalid sign cert file: %s';
	protected $sign_key_file          = 'invalid sign key file: %s';
	protected $sign_key_passphrase    = 'invalid sign key passphrase';
	protected $extra_certs_file       = 'invalid extra certs file: %s';
	protected $dkim_key_file          = 'invalid DKIM key file path: %s';
	protected $dkim_domain            = 'invalid DKIM domain';
	protected $dkim_selector          = 'invalid DKIM selector';
	protected $sign_credentials       = 'no sign credentials set';
	protected $invalid_recipient_type = 'Invalid recipient type: %s';
	protected $invalid_mimetype       = 'Invalid mime type: %s';
	protected $invalid_message_id     = 'Invalid message id: %s';

	/**
	 * @inheritDoc
	 */
	public function string(string $key):string{

		if(!property_exists($this, $key)){
			throw new PHPMailerException(sprintf($this->lang_key, $key));
		}

		if($key === 'smtp_connect_failed'){
			//Include a link to troubleshooting docs on SMTP connection failure
			//this is by far the biggest cause of support questions
			//but it's usually not PHPMailer's fault.
			$link = 'https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';

			return $this->dir === 'RTL'
				? $link.' '.$this->{$key}
				: $this->{$key}.' '.$link;
		}

		return $this->{$key};
	}

	/**
	 * @inheritDoc
	 */
	public function strings(array $keys = null):array{

		if(!empty($keys)){
			$ret = [];

			foreach($keys as $key){
				$key = strtolower($key);

				if(property_exists($this, $key)){
					$ret[$key] = $this->{$key};
				}
			}

			return $ret;
		}

		return get_object_vars($this);
	}
}
