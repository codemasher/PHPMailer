<?php
/**
 * Class PHPMailerOptions
 *
 * @filesource   PHPMailerOptions.php
 * @created      22.11.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use chillerlan\Settings\SettingsContainerAbstract;

/**
 * @property string      $lang
 * @property string|null $XMailer
 * @property string      $hostname
 * @property string      $charSet
 * @property string|null $smtp_host
 * @property int         $smtp_port
 * @property string|null $smtp_username
 * @property string|null $smtp_password
 * @property int         $smtp_timeout
 * @property bool        $smtp_keepalive
 * @property bool        $smtp_auth
 * @property string|null $smtp_authtype
 * @property string|null $smtp_encryption
 * @property bool        $smtp_auto_tls
 * @property array       $smtp_stream_context_options
 * @property string|null $smtp_dsn
 * @property bool        $smtp_verp
 * @property string|null $pop3_host
 * @property int         $pop3_port
 * @property int         $pop3_timeout
 * @property string|null $pop3_username
 * @property string|null $pop3_password
 * @property bool        $allowEmpty
 * @property int         $wordWrap
 * @property bool        $useSendmailOptions
 * @property string      $sendmail_path
 * @property string      $qmail_path
 * @property bool        $singleTo
 * @property bool        $smime_sign
 * @property string|null $sign_cert_file
 * @property string|null $sign_key_file
 * @property string|null $sign_extracerts_file
 * @property string      $sign_key_pass
 * @property bool        $DKIM_sign
 * @property string      $DKIM_domain
 * @property string      $DKIM_selector
 * @property string      $DKIM_key
 * @property string|null $DKIM_passphrase
 * @property string|null $DKIM_identity
 * @property array|null  $DKIM_headers
 * @property bool        $DKIM_copyHeaders
 * @property string|null $validator
 */
class PHPMailerOptions extends SettingsContainerAbstract{

	/**
	 * The language to use
	 *
	 * @var string
	 */
	protected $lang = 'en';

	/**
	 * What to put in the X-Mailer header.
	 * Options: An empty string for PHPMailer default, whitespace for none, or a string to use.
	 *
	 * @var string|null
	 */
	protected $XMailer = null;

	/**
	 * The hostname to use in the Message-ID header and as default HELO string.
	 * If empty, PHPMailer attempts to find one with, in order,
	 * $_SERVER['SERVER_NAME'], gethostname(), php_uname('n'), or the value
	 * 'localhost.localdomain'.
	 *
	 * @var string
	 */
	protected $hostname = null;

	/**
	 * The character set of the message.
	 *
	 * @var string
	 */
	protected $charSet = PHPMailerInterface::CHARSET_UTF8;

	/**
	 * SMTP host(s).
	 * Either a single hostname or multiple semicolon-delimited fallback hostnames.
	 * You can also specify a different port
	 * for each host by using this format: [hostname:port]
	 * (e.g. "smtp1.example.com:25;smtp2.example.com").
	 * You can also specify encryption type, for example:
	 * (e.g. "tls://smtp1.example.com:587;ssl://smtp2.example.com:465").
	 * Hosts will be tried in order.
	 *
	 * @var string|null
	 */
	protected $smtp_host = null;

	/**
	 * The SMTP server port.
	 *
	 * @var int
	 */
	protected $smtp_port = SMTPMailer::DEFAULT_PORT_SMTP;

	/**
	 * SMTP/POP3 server username.
	 *
	 * In case of XOAUTH2 authentication, this should be the OAuth2 user that is associated with the bearer token.
	 *
	 * @link https://developers.google.com/gmail/imap/xoauth2-protocol
	 *
	 * @var string|null
	 */
	protected $smtp_username = null;

	/**
	 * SMTP/POP3 server password.
	 * In case of XOAUTH2 authentication, this should be the OAuth2 token.
	 *
	 * @link https://developers.google.com/gmail/imap/xoauth2-protocol
	 *
	 * @var string|null
	 */
	protected $smtp_password = null;

	/**
	 * The timeout value for connection, in seconds.
	 * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
	 * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
	 *
	 * @see http://tools.ietf.org/html/rfc2821#section-4.5.3.2
	 *
	 * @var int
	 */
	protected $smtp_timeout = 5;

	/**
	 * Whether to keep SMTP connection open after each message.
	 * If this is set to true then to close the connection
	 * requires an explicit call to closeSMTP().
	 *
	 * @var bool
	 */
	protected $smtp_keepalive = false;

	/**
	 * Whether to use SMTP authentication.
	 * Uses the Username and Password properties.
	 *
	 * @see \PHPMailer\PHPMailer\PHPMailerOptions::$smtp_username
	 * @see \PHPMailer\PHPMailer\PHPMailerOptions::$smtp_password
	 *
	 * @var bool
	 */
	protected $smtp_auth = false;

	/**
	 * SMTP auth type.
	 * Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified.
	 *
	 * @var string|null
	 */
	protected $smtp_authtype = null;

	/**
	 * What kind of encryption to use on the SMTP connection.
	 * Options: null, SMTPMailer::ENCRYPTION_STARTTLS, or SMTPMailer::ENCRYPTION_SMTPS.
	 *
	 * @var string|null
	 */
	protected $smtp_encryption = null;

	/**
	 * Whether to enable TLS encryption automatically if a server supports it,
	 * even if `SMTPSecure` is not set to 'tls'.
	 * Be aware that this requires that the server's certificates are valid.
	 *
	 * @var bool
	 */
	protected $smtp_auto_tls = true;

	/**
	 * Options array passed to stream_context_create when connecting via SMTP.
	 *
	 * @var array
	 */
	protected $smtp_stream_context_options = [];

	/**
	 * Comma separated list of DSN notifications
	 * 'NEVER' under no circumstances a DSN must be returned to the sender.
	 *         If you use NEVER all other notifications will be ignored.
	 * 'SUCCESS' will notify you when your mail has arrived at its destination.
	 * 'FAILURE' will arrive if an error occurred during delivery.
	 * 'DELAY'   will notify you if there is an unusual delay in delivery, but the actual
	 *           delivery's outcome (success or failure) is not yet decided.
	 *
	 * @see https://tools.ietf.org/html/rfc3461 See section 4.1 for more information about NOTIFY
	 *
	 * @var string|null
	 */
	protected $smtp_dsn = null;

	/**
	 * Whether to generate VERP addresses on send.
	 * Only applicable when sending via SMTP.
	 *
	 * @see https://en.wikipedia.org/wiki/Variable_envelope_return_path
	 * @see http://www.postfix.org/VERP_README.html Postfix VERP info
	 *
	 * @var bool
	 */
	protected $smtp_verp = false;

	/**
	 * POP3 host. (use this if the host differs from SMTP)
	 *
	 * precedence: function parameter, pop3 option, smtp option
	 *
	 * @var string|null
	 */
	protected $pop3_host = null;

	/**
	 * POP3 port number.
	 *
	 * @var int
	 */
	protected $pop3_port = POP3::DEFAULT_PORT_POP3;

	/**
	 * POP3 timeout in seconds.
	 *
	 * @var int
	 */
	protected $pop3_timeout = POP3::DEFAULT_TIMEOUT_POP3;

	/**
	 * POP3 server username. (use this if the credentials differ from SMTP)
	 *
	 * precedence: function parameter, pop3 option, smtp option
	 *
	 * @var string|null
	 */
	protected $pop3_username = null;

	/**
	 * SMTP server password. (use this if the credentials differ from SMTP)
	 *
	 * precedence: function parameter, pop3 option, smtp option
	 *
	 * @var string|null
	 */
	protected $pop3_password = null;

	/**
	 * Whether to allow sending messages with an empty body.
	 *
	 * @var bool
	 */
	protected $allowEmpty = false;

	/**
	 * Word-wrap the message body to this number of chars.
	 * Set to 0 to not wrap. A useful value here is 78, for RFC2822 section 2.1.1 compliance.
	 *
	 * @see \PHPMailer\PHPMailer\PHPMailerInterface::STD_LINE_LENGTH
	 *
	 * @var int
	 */
	protected $wordWrap = 0;

	/**
	 * Whether mail() uses a fully sendmail-compatible MTA.
	 * One which supports sendmail's "-oi -f" options.
	 *
	 * @var bool
	 */
	protected $useSendmailOptions = true;

	/**
	 * The path to the sendmail program.
	 *
	 * @var string
	 */
	protected $sendmail_path = '/usr/sbin/sendmail';

	/**
	 * The path to the qmail program.
	 *
	 * @var string
	 */
	protected $qmail_path = '/var/qmail/bin/qmail-inject';

	/**
	 * Whether to split multiple to addresses into multiple messages
	 * or send them all in one message.
	 * Only supported in `mail` and `sendmail` transports, not in SMTP.
	 *
	 * @var bool
	 */
	protected $singleTo = false;

	/**
	 * Wheter or not to s/mime sign a message
	 *
	 * @var bool
	 */
	protected $smime_sign = false;

	/**
	 * The S/MIME certificate file path.
	 *
	 * @var string|null
	 */
	protected $sign_cert_file = null;

	/**
	 * The S/MIME key file path.
	 *
	 * @var string|null
	 */
	protected $sign_key_file = null;

	/**
	 * The optional S/MIME extra certificates ("CA Chain") file path.
	 *
	 * @var string|null
	 */
	protected $sign_extracerts_file = null;

	/**
	 * The S/MIME password for the key.
	 * Used only if the key is encrypted.
	 *
	 * @var string
	 */
	protected $sign_key_pass = '';

	/**
	 * Enable DKIM signing
	 *
	 * @var bool
	 */
	protected $DKIM_sign = false;

	/**
	 * DKIM signing domain name.
	 *
	 * @example 'example.com'
	 *
	 * @var string
	 */
	protected $DKIM_domain;

	/**
	 * DKIM selector.
	 *
	 * @var string
	 */
	protected $DKIM_selector;

	/**
	 * DKIM private key file path or key string.
	 *
	 * @var string
	 */
	protected $DKIM_key;

	/**
	 * DKIM passphrase.
	 * Used if your key is encrypted.
	 *
	 * @var string|null
	 */
	protected $DKIM_passphrase = null;

	/**
	 * DKIM Identity.
	 * Usually the email address used as the source of the email.
	 *
	 * @var string|null
	 */
	protected $DKIM_identity = null;

	/**
	 * DKIM Extra signing headers.
	 *
	 * @example ['List-Unsubscribe', 'List-Help']
	 *
	 * @var array|null
	 */
	protected $DKIM_headers = null;

	/**
	 * DKIM Copy header field values for diagnostic use.
	 *
	 * @var bool
	 */
	protected $DKIM_copyHeaders = true;

	/**
	 * Which validator to use by default when validating email addresses.
	 * The default validator uses PHP's FILTER_VALIDATE_EMAIL filter_var option.
	 *
	 * values: pcre, html5
	 *
	 * @see validateAddress()
	 *
	 * @var string|null
	 */
	protected $validator = null;


	protected function set_charSet(string $charSet):void{
		$charSet = strtoupper($charSet);

		if(in_array($charSet, PHPMailerInterface::CHARSETS, true)){
			$this->charSet = $charSet;
		}

	}

}
