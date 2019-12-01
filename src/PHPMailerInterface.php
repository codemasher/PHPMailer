<?php
/**
 * Interface PHPMailerInterface
 *
 * @filesource   PHPMailerInterface.php
 * @created      14.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use Closure;
use PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface;

interface PHPMailerInterface{

	/**
	 * The POP3 PHPMailer Version number.
	 *
	 * @var string
	 */
	public const VERSION = '7.0.0-dev';

	/**
	 * The maximum line length allowed by RFC 5321 section 4.5.3.1.6,
	 * *excluding* a trailing CRLF break.
	 * @see https://tools.ietf.org/html/rfc5321#section-4.5.3.1.6
	 *
	 * @var int
	 */
	public const LINE_LENGTH_MAX = 998;

	/**
	 * The maximum line length allowed for replies in RFC 5321 section 4.5.3.1.5,
	 * *including* a trailing CRLF line break.
	 * @see https://tools.ietf.org/html/rfc5321#section-4.5.3.1.5
	 *
	 * @var int
	 */
	public const MAX_REPLY_LENGTH = 512;

	/**
	 * The lower maximum line length allowed by RFC 2822 section 2.1.1.
	 * This length does NOT include the line break
	 * 76 means that lines will be 77 or 78 chars depending on whether
	 * the line break format is LF or CRLF; both are valid.
	 *
	 * @var int
	 */
	public const LINE_LENGTH_STD = 76;

	/**
	 * The maximum line length supported by mail().
	 *
	 * Background: mail() will sometimes corrupt messages
	 * with headers headers longer than 65 chars, see #818.
	 *
	 * @var int
	 */
	public const LINE_LENGTH_STD_MAIL = 63;

	public const CHARSET_ASCII    = 'US-ASCII';
	public const CHARSET_ISO88591 = 'ISO-8859-1';
	public const CHARSET_UTF8     = 'UTF-8';

	public const CHARSETS = [ // @todo
		self::CHARSET_ASCII,
		self::CHARSET_ISO88591,
		self::CHARSET_UTF8,
	];

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

	public const ENCODINGS = [
		self::ENCODING_7BIT,
		self::ENCODING_8BIT,
		self::ENCODING_BASE64,
		self::ENCODING_BINARY,
		self::ENCODING_QUOTED_PRINTABLE,
	];

	public function setLanguage(string $langcode):void;
	public function setLanguageInterface(PHPMailerLanguageInterface $language):void;
	public function getLanguageInterface():PHPMailerLanguageInterface;
	public function setOptions(PHPMailerOptions $options):PHPMailerInterface;
	public function setSendCallback(Closure $callback):PHPMailerInterface;
	public function getLE():string;
	public function setContentType(string $mime):PHPMailerInterface;
	public function setEncoding(string $encoding):PHPMailerInterface;
	public function setMessageID(string $messageID):PHPMailerInterface;
	public function setMessageDate(string $messageDate):PHPMailerInterface;
	public function setPriority(int $priority):PHPMailerInterface;
	public function setFrom(string $address, string $name = null, bool $autoSetSender = true):PHPMailerInterface;
	public function setSender(string $sender):PHPMailerInterface;
	public function setConfirmReadingTo(string $confirmReadingTo):PHPMailerInterface;
	public function setSubject(string $subject):PHPMailerInterface;
	public function setMessageBody(string $content, string $contentType = null):PHPMailerInterface;
	public function setAltBody(string $altBody):PHPMailerInterface;
	public function setIcal(string $iCal):PHPMailerInterface;
	public function addTO(string $address, string $name = null):bool;
	public function addCC(string $address, string $name = null):bool;
	public function addBCC(string $address, string $name = null):bool;
	public function addReplyTo(string $address, string $name = null):bool;
	public function getTOs():array;
	public function getCCs():array;
	public function getBCCs():array;
	public function getReplyTos():array;
	public function clearTOs():PHPMailerInterface;
	public function clearCCs():PHPMailerInterface;
	public function clearBCCs():PHPMailerInterface;
	public function clearReplyTos():PHPMailerInterface;
	public function getAllRecipients():array;
	public function clearAllRecipients():PHPMailerInterface;
}
