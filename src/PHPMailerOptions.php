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

class PHPMailerOptions extends SettingsContainerAbstract{

	/**
	 * What to put in the X-Mailer header.
	 * Options: An empty string for PHPMailer default, whitespace for none, or a string to use.
	 *
	 * @var string
	 */
	public $XMailer = '';

	/**
	 * The hostname to use in the Message-ID header and as default HELO string.
	 * If empty, PHPMailer attempts to find one with, in order,
	 * $_SERVER['SERVER_NAME'], gethostname(), php_uname('n'), or the value
	 * 'localhost.localdomain'.
	 *
	 * @var string
	 */
	public $hostname = null;

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
	public $smtp_host = null;

	/**
	 * The SMTP server port.
	 *
	 * @var int
	 */
	public $smtp_port = SMTPMailer::DEFAULT_PORT_SMTP;

	/**
	 * SMTP/POP3 server username.
	 *
	 * @var string
	 */
	public $smtp_username = '';

	/**
	 * SMTP/POP3 server password.
	 *
	 * @var string
	 */
	public $smtp_password = '';

	/**
	 * The timeout value for connection, in seconds.
	 * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
	 * This needs to be quite high to function correctly with hosts using greetdelay as an anti-spam measure.
	 *
	 * @see http://tools.ietf.org/html/rfc2821#section-4.5.3.2
	 *
	 * @var int
	 */
	public $smtp_timeout = 5;

	/**
	 * Whether to keep SMTP connection open after each message.
	 * If this is set to true then to close the connection
	 * requires an explicit call to closeSMTP().
	 *
	 * @var bool
	 */
	public $smtp_keepalive = false;

	/**
	 * Whether to use SMTP authentication.
	 * Uses the Username and Password properties.
	 *
	 * @see \PHPMailer\PHPMailer\PHPMailerOptions::$smtp_username
	 * @see \PHPMailer\PHPMailer\PHPMailerOptions::$smtp_password
	 *
	 * @var bool
	 */
	public $smtp_auth = false;

	/**
	 * SMTP auth type.
	 * Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2, attempted in that order if not specified.
	 *
	 * @var string
	 */
	public $smtp_authtype = null;

	/**
	 * What kind of encryption to use on the SMTP connection.
	 * Options: null, SMTPMailer::ENCRYPTION_STARTTLS, or SMTPMailer::ENCRYPTION_SMTPS.
	 *
	 * @var string|null
	 */
	public $smtp_encryption = null;

	/**
	 * Whether to enable TLS encryption automatically if a server supports it,
	 * even if `SMTPSecure` is not set to 'tls'.
	 * Be aware that this requires that the server's certificates are valid.
	 *
	 * @var bool
	 */
	public $smtp_auto_tls = true;

	/**
	 * Options array passed to stream_context_create when connecting via SMTP.
	 *
	 * @var array
	 */
	public $smtp_stream_context_options = [];

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
	 */
	public $smtp_dsn = null;

	/**
	 * Whether to generate VERP addresses on send.
	 * Only applicable when sending via SMTP.
	 *
	 * @see https://en.wikipedia.org/wiki/Variable_envelope_return_path
	 * @see http://www.postfix.org/VERP_README.html Postfix VERP info
	 *
	 * @var bool
	 */
	public $smtp_verp = false;

	/**
	 * POP3 host. (use this if the host differs from SMTP)
	 *
	 * precedence: function parameter, pop3 option, smtp option
	 *
	 * @var string|null
	 */
	public $pop3_host = null;

	/**
	 * POP3 port number.
	 *
	 * @var int
	 */
	public $pop3_port = POP3::DEFAULT_PORT_POP3;

	/**
	 * POP3 timeout in seconds.
	 *
	 * @var int
	 */
	public $pop3_timeout = POP3::DEFAULT_TIMEOUT_POP3;

	/**
	 * POP3 server username. (use this if the credentials differ from SMTP)
	 *
	 * precedence: function parameter, pop3 option, smtp option
	 *
	 * @var string
	 */
	public $pop3_username = null;

	/**
	 * SMTP server password. (use this if the credentials differ from SMTP)
	 *
	 * precedence: function parameter, pop3 option, smtp option
	 *
	 * @var string
	 */
	public $pop3_password = null;

	/**
	 * Whether to allow sending messages with an empty body.
	 *
	 * @var bool
	 */
	public $allowEmpty = false;

	/**
	 * Word-wrap the message body to this number of chars.
	 * Set to 0 to not wrap. A useful value here is 78, for RFC2822 section 2.1.1 compliance.
	 *
	 * @see \PHPMailer\PHPMailer\PHPMailerInterface::STD_LINE_LENGTH
	 *
	 * @var int
	 */
	public $wordWrap = 0;

	/**
	 * Whether mail() uses a fully sendmail-compatible MTA.
	 * One which supports sendmail's "-oi -f" options.
	 *
	 * @var bool
	 */
	public $useSendmailOptions = true;

	/**
	 * The path to the sendmail program.
	 *
	 * @var string
	 */
	public $sendmail_path = '/usr/sbin/sendmail';

	/**
	 * The path to the qmail program.
	 *
	 * @var string
	 */
	public $qmail_path = '/var/qmail/bin/qmail-inject';

	/**
	 * Whether to split multiple to addresses into multiple messages
	 * or send them all in one message.
	 * Only supported in `mail` and `sendmail` transports, not in SMTP.
	 *
	 * @var bool
	 */
	public $singleTo = false;

	/**
	 * Wheter or not to pkcs sign a message
	 *
	 * @var bool
	 */
	public $sign = false;

	/**
	 * Enable DKIM signing
	 *
	 * @var bool
	 */
	public $DKIMSign = false;

	/**
	 * Which validator to use by default when validating email addresses.
	 * The default validator uses PHP's FILTER_VALIDATE_EMAIL filter_var option.
	 *
	 * values: pcre, html5
	 *
	 * @see validateAddress()
	 *
	 * @var string
	 */
	public $validator = null;



}
