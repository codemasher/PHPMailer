<?php
/**
 * PHPMailer - PHP email creation and transport class.
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

use Closure;

use function addcslashes, array_key_exists, base64_decode, base64_encode,
	chunk_split, count, dirname, explode, file_get_contents, file_put_contents,
	function_exists, gethostname, hash, implode, in_array, is_file, openssl_error_string,
	openssl_pkcs7_sign, pack, php_uname, preg_match, preg_match_all, preg_quote, preg_replace, quoted_printable_encode,
	rawurldecode, realpath, rtrim, serialize, sprintf, str_replace, strlen, strpos, strtolower, substr,
	sys_get_temp_dir, tempnam, time, trim, unlink;

use const PKCS7_DETACHED, PATHINFO_BASENAME;

/**
 * PHPMailer - PHP email creation and transport class.
 *
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author  Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author  Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author  Brent R. Matzelle (original founder)
 */
abstract class PHPMailer extends MailerAbstract{ // @todo

	/**
	 * The complete compiled MIME message body.
	 *
	 * @var string
	 */
	protected $mimeBody = '';

	/**
	 * The complete compiled MIME message headers.
	 *
	 * @var string
	 */
	protected $mimeHeader = '';

	/**
	 * Extra headers that createHeader() doesn't fold in.
	 *
	 * @var string
	 */
	protected $mailHeader = '';

	/**
	 * Storage for addresses when SingleTo is enabled.
	 *
	 * @var array
	 */
	protected $singleToArray = [];

	/**
	 * The array of attachments.
	 *
	 * @var \PHPMailer\PHPMailer\Attachment[]
	 */
	protected $attachments = [];

	/**
	 * The array of custom headers.
	 *
	 * @var array
	 */
	protected $customHeaders = [];

	/**
	 * The most recent Message-ID (including angular brackets).
	 *
	 * @var string
	 */
	protected $lastMessageID = '';

	/**
	 * The message's MIME type.
	 *
	 * @var string
	 */
	protected $messageType = '';

	/**
	 * Return the array of attachments.
	 *
	 * @return \PHPMailer\PHPMailer\Attachment[]
	 */
	public function getAttachments():array{
		return $this->attachments;
	}

	/**
	 * Clear all filesystem, string, and binary attachments.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearAttachments():PHPMailer{
		$this->attachments = [];

		return $this;
	}

	/**
	 * Add an attachment from a path on the filesystem.
	 * Never use a user-supplied path to a file!
	 * Returns false if the file could not be found or read.
	 * Explicitly *does not* support passing URLs; PHPMailer is not an HTTP client.
	 * If you need to do that, fetch the resource yourself and pass it in via a local file or string.
	 *
	 * @param string $path        Path to the attachment
	 * @param string $name        Overrides the attachment name
	 * @param string $encoding    File encoding (see $Encoding), defaults to base64
	 * @param string $mimeType    File extension (MIME) type
	 * @param string $disposition Disposition to use, defaults to "attachment"
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function addAttachment(
		string $path,
		string $name = null,
		string $encoding = null,
		string $mimeType = null,
		string $disposition = null
	):PHPMailer{

		if(!isPermittedPath($path) || !@is_file($path)){
			throw new PHPMailerException(sprintf($this->lang->string('file_access'), $path));
		}

		// If a MIME type is not specified, try to work it out from the file name
		if(empty($mimeType)){
			$mimeType = filenameToType($path);
		}

		$filename = mb_pathinfo($path, PATHINFO_BASENAME);
		if(empty($name)){
			$name = $filename;
		}

		$this->attachments[] = (new Attachment($this->lang))
			->setName($name, $filename)
			->setContent($path, $mimeType, $encoding, $disposition, $name)
		;

		return $this;
	}

	/**
	 * Add a string or binary attachment (non-filesystem).
	 * This method can be used to attach ascii or binary data,
	 * such as a BLOB record from a database.
	 *
	 * @param string $string      String attachment data
	 * @param string $filename    Name of the attachment
	 * @param string $encoding    File encoding (see $Encoding), defaults to base64
	 * @param string $mimeType    File extension (MIME) type
	 * @param string $disposition Disposition to use, defaults to "attachment"
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function addStringAttachment(
		string $string,
		string $filename,
		string $encoding = null,
		string $mimeType = null,
		string $disposition = null
	):PHPMailer{

		// If a MIME type is not specified, try to work it out from the file name
		if(empty($mimeType)){
			$mimeType = filenameToType($filename);
		}

		$this->attachments[] = (new Attachment($this->lang))
			->setName(mb_pathinfo($filename, PATHINFO_BASENAME), $filename)
			->setContent($string, $mimeType, $encoding, $disposition)
			->isStringAttachment(true)
		;

		return $this;
	}

	/**
	 * Add an embedded (inline) attachment from a file.
	 * This can include images, sounds, and just about any other document type.
	 * These differ from 'regular' attachments in that they are intended to be
	 * displayed inline with the message, not just attached for download.
	 * This is used in HTML messages that embed the images
	 * the HTML refers to using the $cid value.
	 * Never use a user-supplied path to a file!
	 *
	 * @param string $path        Path to the attachment
	 * @param string $cid         Content ID of the attachment; Use this to reference
	 *                            the content when using an embedded image in HTML
	 * @param string $name        Overrides the attachment name
	 * @param string $encoding    File encoding (see $Encoding), defaults to base64
	 * @param string $mimeType    File MIME type
	 * @param string $disposition Disposition to use, defaults to "inline"
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function addEmbeddedImage(
		string $path,
		string $cid,
		string $name = '',
		string $encoding = null,
		string $mimeType = null,
		string $disposition = null
	):PHPMailer{

		if(!isPermittedPath($path) || !@is_file($path)){
			throw new PHPMailerException(sprintf($this->lang->string('file_access'), $path));
		}

		// If a MIME type is not specified, try to work it out from the file name
		if(empty($mimeType)){
			$mimeType = filenameToType($path);
		}

		$filename = mb_pathinfo($path, PATHINFO_BASENAME);
		if(empty($name)){
			$name = $filename;
		}

		$this->attachments[] = (new Attachment($this->lang))
			->setName($name, $filename)
			->setContent($path, $mimeType, $encoding, $disposition ?? 'inline', $cid)
		;

		return $this;
	}

	/**
	 * Add an embedded stringified attachment.
	 * This can include images, sounds, and just about any other document type.
	 * If your filename doesn't contain an extension, be sure to set the $type to an appropriate MIME type.
	 *
	 * @param string $string      The attachment binary data
	 * @param string $cid         Content ID of the attachment; Use this to reference
	 *                            the content when using an embedded image in HTML
	 * @param string $name        A filename for the attachment. If this contains an extension,
	 *                            PHPMailer will attempt to set a MIME type for the attachment.
	 *                            For example 'file.jpg' would get an 'image/jpeg' MIME type.
	 * @param string $encoding    File encoding (see $Encoding), defaults to base64
	 * @param string $mimeType    MIME type - will be used in preference to any automatically derived type
	 * @param string $disposition Disposition to use, defaults to "inline"
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function addStringEmbeddedImage(
		string $string,
		string $cid,
		string $name = '',
		string $encoding = null,
		string $mimeType = null,
		string $disposition = null
	):PHPMailer{

		// If a MIME type is not specified, try to work it out from the name
		if(empty($mimeType) && !empty($name)){
			$mimeType = filenameToType($name);
		}

		$this->attachments[] = (new Attachment($this->lang))
			->setName($name, $name)
			->setContent($string, $mimeType, $encoding, $disposition ?? 'inline', $cid)
			->isStringAttachment(true)
		;

		return $this;
	}

	/**
	 * Add a custom header.
	 * $name value can be overloaded to contain
	 * both header name and value (name:value).
	 *
	 * @param string      $name  Custom header name
	 * @param string|null $value Header value
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function addCustomHeader(string $name, string $value = null):PHPMailer{

		$this->customHeaders[] = $value === null
			? explode(':', $name, 2) // Value passed in as name:value
			: [$name, $value];

		return $this;
	}

	/**
	 * Returns all custom headers.
	 *
	 * @return array
	 */
	public function getCustomHeaders():array{
		return $this->customHeaders;
	}

	/**
	 * Clear all custom headers.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function clearCustomHeaders():PHPMailer{
		$this->customHeaders = [];

		return $this;
	}

	/**
	 * Return the Message-ID header of the last email.
	 * Technically this is the value from the last time the headers were created,
	 * but it's also the message ID of the last sent message except in
	 * pathological cases.
	 *
	 * @return string
	 */
	public function getLastMessageID():string{
		return $this->lastMessageID;
	}

	/**
	 * Returns the whole MIME message.
	 * Includes complete headers and body.
	 * Only valid post preSend().
	 *
	 * @return string
	 * @see PHPMailer::preSend()
	 *
	 */
	public function getSentMIMEMessage():string{
		return rtrim($this->mimeHeader.$this->mailHeader, "\n\r").$this->LE.$this->LE.$this->mimeBody;
	}

	/**
	 * Create a message and send it.
	 * Uses the sending method specified by $Mailer.
	 *
	 * @return bool false on error
	 */
	public function send():bool{
		return $this
			->preSend()
			->postSend()
		;
	}

	/**
	 * Prepare a message for sending.
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function preSend():PHPMailer{
		$this->mailHeader  = '';

		if(empty($this->to) && empty($this->cc) && empty($this->bcc)){
			throw new PHPMailerException($this->lang->string('provide_address'));
		}

		// Set whether the message is multipart/alternative
		if(!empty($this->altBody)){
			$this->contentType = $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
		}

		$this->setMessageType();
		// Refuse to send an empty message unless we are specifically allowing it
		if(!$this->options->allowEmpty && empty($this->body)){
			throw new PHPMailerException($this->lang->string('empty_message'));
		}

		//Create unique IDs and preset boundaries
		$uniqueid = generateId();

		// Create body before headers in case body makes changes to headers (e.g. altering transfer encoding)
		$this->mimeHeader = '';
		$this->mimeBody   = $this->createBody($uniqueid);

		if($this->options->smime_sign){
			$this->mimeBody = $this->pkcs7Sign($this->mimeBody);
		}

		// createBody may have added some headers, so retain them
		$this->mimeHeader = $this->createHeader($uniqueid).$this->mimeHeader;

		// To capture the complete message when using mail(), create
		// an extra header list which createHeader() doesn't fold in
		if($this instanceof MailMailer){
			$this->mailHeader .= !empty($this->to)
				? $this->addrAppend('To', $this->to)
				: $this->headerLine('To', 'undisclosed-recipients:;');

			$this->mailHeader .= $this->headerLine('Subject', $this->encodeHeader(secureHeader($this->subject)));
		}

		// Sign with DKIM if enabled
		if($this->options->DKIM_sign){
			$header_dkim = $this->DKIM_Add(
				$this->mimeHeader.$this->mailHeader,
				$this->encodeHeader(secureHeader($this->subject)),
				$this->mimeBody
			);

			$this->mimeHeader = rtrim($this->mimeHeader, "\r\n ").$this->LE.normalizeBreaks($header_dkim, $this->LE).$this->LE;
		}

		return $this;
	}

	/**
	 * Set the message type.
	 * PHPMailer only supports some preset message types, not arbitrary MIME structures.
	 *
	 * @return void
	 */
	protected function setMessageType():void{
		$type = [];

		if(!empty($this->altBody)){
			$type[] = 'alt';
		}

		if($this->inlineImageExists()){
			$type[] = 'inline';
		}

		if($this->attachmentExists()){
			$type[] = 'attach';
		}

		$this->messageType = implode('_', $type);

		if(empty($this->messageType)){
			//The 'plain' message_type refers to the message having a single body element, not that it is plain-text
			$this->messageType = 'plain';
		}
	}

	/**
	 * Check if an inline attachment is present.
	 *
	 * @return bool
	 */
	public function inlineImageExists():bool{

		foreach($this->attachments as $attachment){
			if($attachment->disposition === 'inline'){
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if an attachment (non-inline) is present.
	 *
	 * @return bool
	 */
	public function attachmentExists():bool{

		foreach($this->attachments as $attachment){
			if($attachment->disposition === 'attachment'){
				return true;
			}
		}

		return false;
	}

	/**
	 * Create recipient headers.
	 *
	 * @param string $type
	 * @param array  $addr An array of recipients,
	 *                     where each recipient is a 2-element indexed array with element 0 containing an address
	 *                     and element 1 containing a name, like:
	 *                     [['joe@example.com', 'Joe User'], ['zoe@example.com', 'Zoe User']]
	 *
	 * @return string
	 */
	protected function addrAppend(string $type, array $addr):string{
		$addresses = [];

		foreach($addr as $address){
			$addresses[] = $this->addrFormat($address);
		}

		return $type.': '.implode(', ', $addresses).$this->LE;
	}

	/**
	 * Format an address for use in a message header.
	 *
	 * @param array $addr A 2-element indexed array, element 0 containing an address, element 1 containing a name like
	 *                    ['joe@example.com', 'Joe User']
	 *
	 * @return string
	 */
	protected function addrFormat(array $addr):string{

		if(empty($addr[1])){ // No name provided
			return secureHeader($addr[0]);
		}

		return $this->encodeHeader(secureHeader($addr[1]), 'phrase').' <'.secureHeader($addr[0]).'>';
	}

	/**
	 * Assemble message headers.
	 *
	 * @param string $uniqueid
	 *
	 * @return string The assembled headers
	 */
	protected function createHeader(string $uniqueid):string{
		$header = $this->headerLine('Date', empty($this->messageDate) ? rfcDate() : $this->messageDate);

		// To be created automatically by mail()
		if($this->options->singleTo){
			if(!$this instanceof MailMailer){
				foreach($this->to as $toaddr){
					$this->singleToArray[] = $this->addrFormat($toaddr);
				}
			}
		}
		else{
			if(!empty($this->to) && !$this instanceof MailMailer){
				$header .= $this->addrAppend('To', $this->to);
			}
			elseif(empty($this->cc)){
				$header .= $this->headerLine('To', 'undisclosed-recipients:;');
			}
		}

		$header .= $this->addrAppend('From', [[$this->from, $this->fromName]]);

		// sendmail and mail() extract Cc from the header before sending
		if(!empty($this->cc)){
			$header .= $this->addrAppend('Cc', $this->cc);
		}

		// sendmail and mail() extract Bcc from the header before sending
		if(!$this instanceof SMTPMailer && !empty($this->bcc)){
			$header .= $this->addrAppend('Bcc', $this->bcc);
		}

		if(!empty($this->replyTo)){
			$header .= $this->addrAppend('Reply-To', $this->replyTo);
		}

		// mail() sets the subject itself
		if(!$this instanceof MailMailer){
			$header .= $this->headerLine('Subject', $this->encodeHeader(secureHeader($this->subject)));
		}

		// Only allow a custom message ID if it conforms to RFC 5322 section 3.6.4
		// https://tools.ietf.org/html/rfc5322#section-3.6.4
		$this->lastMessageID = !empty($this->messageID)
			? $this->messageID
			: sprintf('<%s@%s>', $uniqueid, $this->serverHostname());

		$header .= $this->headerLine('Message-ID', $this->lastMessageID);

		if(!empty($this->priority)){
			$header .= $this->headerLine('X-Priority', $this->priority);
		}

		$xmailer = trim($this->options->XMailer);

		$xmailer = empty($xmailer)
			? 'PHPMailer '.$this::VERSION.' (https://github.com/PHPMailer/PHPMailer)'
			: $xmailer;

		$header .= $this->headerLine('X-Mailer', $xmailer);

		if(!empty($this->confirmReadingTo)){
			$header .= $this->headerLine('Disposition-Notification-To', '<'.$this->confirmReadingTo.'>');
		}

		// Add custom headers
		foreach($this->customHeaders as $h){
			$header .= $this->headerLine(trim($h[0]), $this->encodeHeader(trim($h[1])));
		}

		if(!$this->options->smime_sign){
			$header .= $this->headerLine('MIME-Version', '1.0');
			$header .= $this->getMailMIME($uniqueid);
		}

		return $header;
	}

	/**
	 * Get the message MIME type headers.
	 *
	 * @param string $uniqueid
	 *
	 * @return string
	 */
	protected function getMailMIME(string $uniqueid):string{
		$boundary    = generateBoundary($uniqueid);
		$mime        = '';
		$ismultipart = true;

		switch($this->messageType){
			case 'inline':
				$mime .= $this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';');
				$mime .= $this->textLine(' boundary="'.$boundary[1].'"');
				break;
			case 'attach':
			case 'inline_attach':
			case 'alt_attach':
			case 'alt_inline_attach':
				$mime .= $this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_MIXED.';');
				$mime .= $this->textLine(' boundary="'.$boundary[1].'"');
				break;
			case 'alt':
			case 'alt_inline':
				$mime .= $this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE.';');
				$mime .= $this->textLine(' boundary="'.$boundary[1].'"');
				break;
			default:
				// Catches case 'plain': and case '':
				$mime        .= $this->textLine('Content-Type: '.$this->contentType.'; charset='.$this->options->charSet);
				$ismultipart = false;
				break;
		}

		// RFC1341 part 5 says 7bit is assumed if not specified
		if($this->encoding !== $this::ENCODING_7BIT){
			// RFC 2045 section 6.4 says multipart MIME parts may only use 7bit, 8bit or binary CTE
			if($ismultipart){
				if($this->encoding === $this::ENCODING_8BIT){
					$mime .= $this->headerLine('Content-Transfer-Encoding', $this::ENCODING_8BIT);
				}
				// The only remaining alternatives are quoted-printable and base64, which are both 7bit compatible
			}
			else{
				$mime .= $this->headerLine('Content-Transfer-Encoding', $this->encoding);
			}
		}

#		if(!$this instanceof MailMailer){
#			$mime .= $this->LE;
#		}

		return $mime;
	}

	/**
	 * Assemble the message body.
	 * Returns an empty string on failure.
	 *
	 * @param string $uniqueid
	 *
	 * @return string The assembled message body
	 */
	protected function createBody(string $uniqueid):string{
		$boundary = generateBoundary($uniqueid);

		$body = '';

		if($this->options->smime_sign){
			$body .= $this->getMailMIME($uniqueid).$this->LE;
		}

		$this->body = wrapText($this->body, $this->options->wordWrap, $this->options->charSet, $this->LE);

		$bodyEncoding = $this->encoding;
		$bodyCharSet  = $this->options->charSet;

		//Can we do a 7-bit downgrade?
		if($bodyEncoding === $this::ENCODING_8BIT && !has8bitChars($this->body)){
			$bodyEncoding = $this::ENCODING_7BIT;
			//All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
			$bodyCharSet = $this::CHARSET_ASCII;
		}

		//If lines are too long, and we're not already using an encoding that will shorten them,
		//change to quoted-printable transfer encoding for the body part only
		if($this->encoding !== $this::ENCODING_BASE64 && $this->hasLineLongerThanMax($this->body)){
			$bodyEncoding = $this::ENCODING_QUOTED_PRINTABLE;
		}

		//Use this as a preamble in all multipart message types
		$mimepre = 'This is a multi-part message in MIME format.'.$this->LE;

		if(in_array($this->messageType, ['inline', 'attach', 'inline_attach'])){
			$body .= $mimepre;
			$body .= $this->{'body_'.$this->messageType}($this->body, $boundary, $bodyCharSet, $bodyEncoding);
		}
		elseif(in_array($this->messageType, ['alt', 'alt_inline', 'alt_attach', 'alt_inline_attach'])){

			$this->altBody = wrapText($this->altBody, $this->options->wordWrap, $this->options->charSet, $this->LE);

			$altBodyEncoding = $this->encoding;
			$altBodyCharSet  = $this->options->charSet;

			//Can we do a 7-bit downgrade?
			if($altBodyEncoding === $this::ENCODING_8BIT && !has8bitChars($this->altBody)){
				$altBodyEncoding = $this::ENCODING_7BIT;
				//All ISO 8859, Windows codepage and UTF-8 charsets are ascii compatible up to 7-bit
				$altBodyCharSet = $this::CHARSET_ASCII;
			}

			//If lines are too long, and we're not already using an encoding that will shorten them,
			//change to quoted-printable transfer encoding for the alt body part only
			if($altBodyEncoding !== $this::ENCODING_BASE64 && $this->hasLineLongerThanMax($this->altBody)){
				$altBodyEncoding = $this::ENCODING_QUOTED_PRINTABLE;
			}

			$body .= $mimepre;
			$body .= $this->{'body_'.$this->messageType}($this->body, $boundary, $bodyCharSet, $bodyEncoding, $altBodyCharSet, $altBodyEncoding);
		}
		else{
			// Catch case 'plain' and case '', applies to simple `text/plain` and `text/html` body content types
			//Reset the `Encoding` property in case we changed it for line length reasons
			$this->encoding = $bodyEncoding;
			$body           .= $this->encodeString($this->body, $this->encoding);
		}

		return $body;
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 *
	 * @return string
	 */
	protected function body_inline(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding):string{
		return $this->getBoundary($boundary[1], $bodyCharSet, '', $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 *
	 * @return string
	 */
	protected function body_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding):string{
		return $this->getBoundary($boundary[1], $bodyCharSet, '', $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 *
	 * @return string
	 */
	protected function body_inline_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding):string{
		return $this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';')
			.$this->textLine(' boundary="'.$boundary[2].'";')
			.$this->textLine(' type="' . $this::CONTENT_TYPE_TEXT_HTML . '"')
			.$this->LE
			.$this->getBoundary($boundary[2], $bodyCharSet, '', $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[2])
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		$body = $this->getBoundary($boundary[1], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->altBody, $altBodyEncoding)
			.$this->LE
			.$this->getBoundary($boundary[1], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE;

		if(!empty($this->iCal)){
			$body .= $this->getBoundary($boundary[1], '', $this::CONTENT_TYPE_TEXT_CALENDAR.'; method=REQUEST', '')
				.$this->encodeString($this->iCal, $this->encoding)
				.$this->LE;
		}

		$body .= $this->endBoundary($boundary[1]);

		return $body;
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt_inline(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		return $this->getBoundary($boundary[1], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->altBody, $altBodyEncoding)
			.$this->LE
			.$this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';')
			.$this->textLine(' boundary="'.$boundary[2].'";')
			.$this->textLine(' type="' . $this::CONTENT_TYPE_TEXT_HTML . '"')
			.$this->LE
			.$this->getBoundary($boundary[2], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[2])
			.$this->LE
			.$this->endBoundary($boundary[1]);
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		$body = $this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE.';')
			.$this->textLine(' boundary="'.$boundary[2].'"')
			.$this->LE
			.$this->getBoundary($boundary[2], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->altBody, $altBodyEncoding)
			.$this->LE
			.$this->getBoundary($boundary[2], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE;

		if(!empty($this->iCal)){
			$body .= $this->getBoundary($boundary[2], '', $this::CONTENT_TYPE_TEXT_CALENDAR.'; method=REQUEST', '')
				.$this->encodeString($this->iCal, $this->encoding);
		}

		$body .= $this->endBoundary($boundary[2])
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);

		return $body;
	}

	/**
	 * @param string $messageBody
	 * @param array  $boundary
	 * @param string $bodyCharSet
	 * @param string $bodyEncoding
	 * @param string $altBodyCharSet
	 * @param string $altBodyEncoding
	 *
	 * @return string
	 */
	protected function body_alt_inline_attach(string $messageBody, array $boundary, string $bodyCharSet, string $bodyEncoding, string $altBodyCharSet, string $altBodyEncoding):string{
		return $this->textLine('--'.$boundary[1])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_ALTERNATIVE.';')
			.$this->textLine(' boundary="'.$boundary[2].'"')
			.$this->LE
			.$this->getBoundary($boundary[2], $altBodyCharSet, $this::CONTENT_TYPE_PLAINTEXT, $altBodyEncoding)
			.$this->encodeString($this->altBody, $altBodyEncoding)
			.$this->LE
			.$this->textLine('--'.$boundary[2])
			.$this->headerLine('Content-Type', $this::CONTENT_TYPE_MULTIPART_RELATED.';')
			.$this->textLine(' boundary="'.$boundary[3].'";')
			.$this->textLine(' type="' . $this::CONTENT_TYPE_TEXT_HTML . '"')
			.$this->LE
			.$this->getBoundary($boundary[3], $bodyCharSet, $this::CONTENT_TYPE_TEXT_HTML, $bodyEncoding)
			.$this->encodeString($messageBody, $bodyEncoding)
			.$this->LE
			.$this->attachAll('inline', $boundary[3])
			.$this->LE
			.$this->endBoundary($boundary[2])
			.$this->LE
			.$this->attachAll('attachment', $boundary[1]);
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	protected function pkcs7Sign(string $message):string{

		if(!fileCheck($this->options->sign_cert_file) || !isPermittedPath($this->options->sign_cert_file)){
			throw new PHPMailerException(sprintf($this->lang->string('sign_cert_file'), $this->options->sign_cert_file));
		}

		if(!fileCheck($this->options->sign_key_file) || !isPermittedPath($this->options->sign_key_file)){
			throw new PHPMailerException(sprintf($this->lang->string('sign_key_file'), $this->options->sign_key_file));
		}

		if($this->options->sign_extracerts_file !== null){

			if(!fileCheck($this->options->sign_extracerts_file) || !isPermittedPath($this->options->sign_extracerts_file)){
				throw new PHPMailerException(sprintf($this->lang->string('extra_certs_file'), $this->options->sign_extracerts_file));
			}

		}

		$tmpdir = sys_get_temp_dir();
		$file   = tempnam($tmpdir, 'pkcs7file');
		$signed = tempnam($tmpdir, 'pkcs7signed'); // will be created by openssl_pkcs7_sign()

		file_put_contents($file, $message); // dump the body

		$signcert = 'file://'.realpath($this->options->sign_cert_file);
		$privkey  = ['file://'.realpath($this->options->sign_key_file), $this->options->sign_key_pass];

		// Workaround for PHP bug https://bugs.php.net/bug.php?id=69197
		// this bug still exists in 7.2+ despite being closed and "fixed"
		$sign = empty($this->sign_extracerts_file)
			? openssl_pkcs7_sign($file, $signed, $signcert, $privkey, [])
			: openssl_pkcs7_sign($file, $signed, $signcert, $privkey, [], PKCS7_DETACHED, $this->options->sign_extracerts_file);

		$message = file_get_contents($signed);

		unlink($file);
		unlink($signed);

		if(!$sign){
			throw new PHPMailerException(sprintf($this->lang->string('signing'), openssl_error_string()));
		}

		//The message returned by openssl contains both headers and body, so need to split them up
		$parts            = explode("\n\n", $message, 2);
		$this->mimeHeader .= $parts[0].$this->LE.$this->LE;

		return $parts[1];
	}

	/**
	 * Return the start of a message boundary.
	 *
	 * @param string $boundary
	 * @param string $charSet
	 * @param string $contentType
	 * @param string $encoding
	 *
	 * @return string
	 */
	protected function getBoundary(string $boundary, string $charSet, string $contentType, string $encoding):string{
		$result = '';

		if(empty($charSet)){
			$charSet = $this->options->charSet;
		}

		if(empty($contentType)){
			$contentType = $this->contentType;
		}

		if(empty($encoding)){
			$encoding = $this->encoding;
		}

		$result .= $this->textLine('--'.$boundary);
		$result .= sprintf('Content-Type: %s; charset=%s', $contentType, $charSet);
		$result .= $this->LE;

		// RFC1341 part 5 says 7bit is assumed if not specified
		if($encoding !== $this::ENCODING_7BIT){
			$result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
		}

		$result .= $this->LE;

		return $result;
	}

	/**
	 * Return the end of a message boundary.
	 *
	 * @param string $boundary
	 *
	 * @return string
	 */
	protected function endBoundary(string $boundary):string{
		return $this->LE.'--'.$boundary.'--'.$this->LE;
	}

	/**
	 * Format a header line.
	 *
	 * @param string $name
	 * @param string $value
	 *
	 * @return string
	 */
	protected function headerLine(string $name, string $value):string{
		return $name.': '.$value.$this->LE;
	}

	/**
	 * Return a formatted mail line.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function textLine(string $value):string{
		return $value.$this->LE;
	}

	/**
	 * Attach all file, string, and binary attachments to the message.
	 * Returns an empty string on failure.
	 *
	 * @param string $disposition_type
	 * @param string $boundary
	 *
	 * @return string
	 */
	protected function attachAll(string $disposition_type, string $boundary):string{
		// Return text of body
		$mime    = [];
		$cidUniq = [];
		$incl    = [];

		// Add all attachments
		foreach($this->attachments as $attachment){
			// Check if it is a valid disposition_filter
			if($attachment->disposition === $disposition_type){
				$inclhash = hash('sha256', serialize($attachment));

				if(in_array($inclhash, $incl)){
					continue;
				}

				$incl[] = $inclhash;

				if($attachment->disposition === 'inline' && array_key_exists($attachment->cid, $cidUniq)){
					continue;
				}

				$cidUniq[$attachment->cid] = true;

				$mime[] = sprintf('--%s%s', $boundary, $this->LE);
				//Only include a filename property if we have one
				$mime[] = !empty($attachment->name)
					? sprintf(
						'Content-Type: %s; name="%s"%s',
						$attachment->mimeType,
						$this->encodeHeader(secureHeader($attachment->name)),
						$this->LE
					)
					: sprintf('Content-Type: %s%s', $attachment->mimeType, $this->LE);

				// RFC1341 part 5 says 7bit is assumed if not specified
				if($attachment->encoding !== $this::ENCODING_7BIT){
					$mime[] = sprintf('Content-Transfer-Encoding: %s%s', $attachment->encoding, $this->LE);
				}

				//Only set Content-IDs on inline attachments
				if(!empty($attachment->cid) && $attachment->disposition === 'inline'){
					$mime[] = 'Content-ID: <'.$this->encodeHeader(secureHeader($attachment->cid)).'>' . $this->LE;
				}

				// If a filename contains any of these chars, it should be quoted,
				// but not otherwise: RFC2183 & RFC2045 5.1
				// Fixes a warning in IETF's msglint MIME checker
				// Allow for bypassing the Content-Disposition header totally
				if(!empty($attachment->disposition)){
					$encoded_name = $this->encodeHeader(secureHeader($attachment->name));

					/** @noinspection RegExpRedundantEscape */
					if(preg_match('/[ \(\)<>@,;:\\"\/\[\]\?=]/', $encoded_name)){
						$mime[] = sprintf(
							'Content-Disposition: %s; filename="%s"%s',
							$attachment->disposition,
							$encoded_name,
							$this->LE.$this->LE
						);
					}
					else{
						$mime[] = !empty($encoded_name)
							? sprintf(
								'Content-Disposition: %s; filename=%s%s',
								$attachment->disposition,
								$encoded_name,
								$this->LE.$this->LE
							)
							: sprintf('Content-Disposition: %s%s', $attachment->disposition, $this->LE.$this->LE);
					}
				}
				else{
					$mime[] = $this->LE;
				}

				// Encode as string attachment
				$mime[] = $attachment->isStringAttachment
					? $this->encodeString($attachment->content, $attachment->encoding)
					: $this->encodeFile($attachment->content, $attachment->encoding);

				$mime[] = $this->LE;
			}
		}

		$mime[] = sprintf('--%s--%s', $boundary, $this->LE);

		return implode('', $mime);
	}

	/**
	 * Encode a file attachment in requested format.
	 * Returns an empty string on failure.
	 *
	 * @param string $path     The full path to the file
	 * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	protected function encodeFile(string $path, string $encoding = self::ENCODING_BASE64):string{

		if(!fileCheck($path) || !isPermittedPath($path)){
			throw new PHPMailerException(sprintf($this->lang->string('file_open'), $path));
		}

		$file_buffer = file_get_contents($path);

		if($file_buffer === false){
			throw new PHPMailerException(sprintf($this->lang->string('file_open'), $path));
		}

		$file_buffer = $this->encodeString($file_buffer, $encoding);

		return $file_buffer;
	}

	/**
	 * Encode a string in requested format.
	 * Returns an empty string on failure.
	 *
	 * @param string $str      The text to encode
	 * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	protected function encodeString(string $str, string $encoding = self::ENCODING_BASE64):string{

		switch(strtolower($encoding)){
			case $this::ENCODING_BASE64:
				return chunk_split(base64_encode($str), $this::LINE_LENGTH_STD, $this->LE);
			case $this::ENCODING_7BIT:
			case $this::ENCODING_8BIT:
				$encoded = normalizeBreaks($str, $this->LE);
				// Make sure it ends with a line break
				if(substr($encoded, -strlen($this->LE)) !== $this->LE){
					$encoded .= $this->LE;
				}

				return $encoded;
			case $this::ENCODING_BINARY:
				return $str;
			case $this::ENCODING_QUOTED_PRINTABLE:
				return normalizeBreaks(quoted_printable_encode($str), $this->LE);
		}

		throw new PHPMailerException(sprintf($this->lang->string('encoding'), $encoding));
	}

	/**
	 * Encode a header value (not including its label) optimally.
	 * Picks shortest of Q, B, or none. Result includes folding if needed.
	 * See RFC822 definitions for phrase, comment and text positions.
	 *
	 * @param string $str      The header value to encode
	 * @param string $position What context the string will be used in
	 *
	 * @return string
	 */
	protected function encodeHeader(string $str, string $position = 'text'):string{
		$matchcount = 0;

		switch(strtolower($position)){
			case 'phrase':

				if(!preg_match('/[\200-\377]/', $str)){
					// Can't use addslashes as we don't know the value of magic_quotes_sybase
					$encoded = addcslashes($str, "\0..\37\177\\\"");
					if(($encoded === $str) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)){
						return $encoded;
					}

					return "\"$encoded\"";
				}

				$matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
				break;
			/* @noinspection PhpMissingBreakStatementInspection */
			case 'comment':
				$matchcount = preg_match_all('/[()"]/', $str, $matches);
			//fallthrough
			case 'text':
			default:
				$matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
		}

		$charset = has8bitChars($str)
			? $this->options->charSet
			: $this::CHARSET_ASCII;

		$maxlen = $this instanceof MailMailer
			? $this::LINE_LENGTH_STD_MAIL
			: $this::LINE_LENGTH_STD;

		// Q/B encoding adds 8 chars and the charset ("` =?<charset>?[QB]?<content>?=`").
		$maxlen -= 8 + strlen($charset);

		// Select the encoding that produces the shortest output and/or prevents corruption.
		if($matchcount > strlen($str) / 3){
			// More than 1/3 of the content needs encoding, use B-encode.
			$encoding = 'B';
		}
		elseif($matchcount > 0){
			// Less than 1/3 of the content needs encoding, use Q-encode.
			$encoding = 'Q';
		}
		elseif(strlen($str) > $maxlen){
			// No encoding needed, but value exceeds max line length, use Q-encode to prevent corruption.
			$encoding = 'Q';
		}
		else{
			// No reformatting needed
			$encoding = false;
		}

		switch($encoding){
			case 'B':
				if(hasMultiBytes($str, $this->options->charSet)){
					// Use a custom function which correctly encodes and wraps long
					// multibyte strings without breaking lines within a character
					$encoded = base64EncodeWrapMB($str, $this->options->charSet, "\n");
				}
				else{
					$encoded = base64_encode($str);
					$maxlen  -= $maxlen % 4;
					$encoded = trim(chunk_split($encoded, $maxlen, "\n"));
				}
				$encoded = preg_replace('/^(.*)$/m', ' =?'.$charset."?$encoding?\\1?=", $encoded);
				break;
			case 'Q':
				$encoded = wrapText(encodeQ($str, $position), $maxlen, $this->options->charSet, $this->LE, true);
				$encoded = str_replace('='.$this->LE, "\n", trim($encoded));
				$encoded = preg_replace('/^(.*)$/m', ' =?'.$charset."?$encoding?\\1?=", $encoded);
				break;
			default:
				return $str;
		}

		return trim(normalizeBreaks($encoded, $this->LE));
	}

	/**
	 * Check if an embedded attachment is present with this cid.
	 *
	 * @param string $cid
	 *
	 * @return bool
	 */
	protected function cidExists(string $cid):bool{

		foreach($this->attachments as $attachment){
			if($attachment->disposition === 'inline' && $attachment->cid === $cid){
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the server hostname.
	 * Returns 'localhost.localdomain' if unknown.
	 *
	 * @return string
	 */
	protected function serverHostname():string{
		$hostname = '';

		if(!empty($this->options->hostname)){
			$hostname = $this->options->hostname;
		}
		elseif(isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER)){
			$hostname = $_SERVER['SERVER_NAME'];
		}
		elseif(function_exists('gethostname') && gethostname() !== false){
			$hostname = gethostname();
		}
		elseif(php_uname('n') !== false){
			$hostname = php_uname('n');
		}

		if(!isValidHost($hostname)){
			return 'localhost.localdomain';
		}

		return $hostname;
	}

	/**
	 * @param string $message
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 */
	public function messageFromPlaintext(string $message):PHPMailer{
		$this->body        = $message;
		$this->contentType = $this::CONTENT_TYPE_PLAINTEXT;

		return $this;
	}

	/**
	 * Create a message body from an HTML string.
	 * Automatically inlines images and creates a plain-text version by converting the HTML,
	 * overwriting any existing values in Body and AltBody.
	 * Do not source $message content from user input!
	 * $basedir is prepended when handling relative URLs, e.g. <img src="/images/a.png"> and must not be empty
	 * will look for an image file in $basedir/images/a.png and convert it to inline.
	 * If you don't provide a $basedir, relative paths will be left untouched (and thus probably break in email)
	 * Converts data-uri images into embedded attachments.
	 * If you don't want to apply these transformations to your HTML, just set Body and AltBody directly.
	 *
	 * @param string        $message  HTML message string
	 * @param string        $basedir  Absolute path to a base directory to prepend to relative paths to images
	 * @param null|callable $advanced Whether to use the internal HTML to text converter
	 *                                or your own custom converter
	 *
	 * @return \PHPMailer\PHPMailer\PHPMailer
	 *
	 * @see PHPMailer::html2text()
	 */
	public function messageFromHTML(string $message, string $basedir = null, $advanced = null):PHPMailer{
		preg_match_all('/(src|background)=["\'](.*)["\']/Ui', $message, $images);

		if(array_key_exists(2, $images)){

			if(strlen($basedir) > 1 && substr($basedir, -1) !== '/'){
				// Ensure $basedir has a trailing /
				$basedir .= '/';
			}

			foreach($images[2] as $imgindex => $url){
				// Convert data URIs into embedded images
				//e.g. "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="
				if(preg_match('#^data:(image/(?:jpe?g|gif|png));?(base64)?,(.+)#', $url, $match) > 0){

					if(count($match) === 4 && $match[2] === $this::ENCODING_BASE64){
						$data = base64_decode($match[3]);
					}
					elseif(empty($match[2])){
						$data = rawurldecode($match[3]);
					}
					else{
						//Not recognised so leave it alone
						continue;
					}

					//Hash the decoded data, not the URL so that the same data-URI image used in multiple places
					//will only be embedded once, even if it used a different encoding
					$cid = substr(hash('sha256', $data), 0, 32).'@phpmailer.0'; // RFC2392 S 2

					if(!$this->cidExists($cid)){
						$this->addStringEmbeddedImage($data, $cid, 'embed'.$imgindex, $this::ENCODING_BASE64, $match[1]);
					}

					$message = str_replace($images[0][$imgindex], $images[1][$imgindex].'="cid:'.$cid.'"', $message);

					continue;
				}

				if( // Only process relative URLs if a basedir is provided (i.e. no absolute local paths)
					!empty($basedir)
					// Ignore URLs containing parent dir traversal (..)
					&& strpos($url, '..') === false
					// Do not change urls that are already inline images
					&& strpos($url, 'cid:') !== 0
					// Do not change absolute URLs, including anonymous protocol
					&& !preg_match('#^[a-z][a-z0-9+.-]*:?//#i', $url)
				){
					$filename  = mb_pathinfo($url, PATHINFO_BASENAME);
					$directory = dirname($url);

					if($directory === '.'){
						$directory = '';
					}

					$cid = substr(hash('sha256', $url), 0, 32).'@phpmailer.0'; // RFC2392 S 2

					if(strlen($basedir) > 1 && substr($basedir, -1) !== '/'){
						$basedir .= '/';
					}

					if(strlen($directory) > 1 && substr($directory, -1) !== '/'){
						$directory .= '/';
					}

					if($this->addEmbeddedImage(
						$basedir.$directory.$filename,
						$cid,
						$filename,
						$this::ENCODING_BASE64,
						filenameToType($filename)
					)
					){
						$message = preg_replace(
							'/'.$images[1][$imgindex].'=["\']'.preg_quote($url, '/').'["\']/Ui',
							$images[1][$imgindex].'="cid:'.$cid.'"',
							$message
						);
					}
				}
			}
		}

		$this->contentType = $this::CONTENT_TYPE_TEXT_HTML;
		// Convert all message body line breaks to LE, makes quoted-printable encoding work much better
		$this->body    = normalizeBreaks($message, $this->LE);
		$this->altBody = normalizeBreaks(html2text($message, $this->options->charSet, $advanced), $this->LE);

		if(!empty($this->altBody)){
			$this->altBody = 'This is an HTML-only message. To view it, activate HTML in your email application.'.$this->LE;
		}

		return $this;
	}

	/**
	 * Create the DKIM header and body in a new message header.
	 *
	 * @param string $headers Header lines
	 * @param string $subject Subject
	 * @param string $body    Body
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function DKIM_Add(string $headers, string $subject, string $body):string{

		if(!$this->options->DKIM_domain){
			throw new PHPMailerException($this->lang->string('dkim_domain'));
		}

		if(!$this->options->DKIM_selector){
			throw new PHPMailerException($this->lang->string('dkim_selector'));
		}

		if(stripos($headers, 'Subject') === false){
			$headers .= 'Subject: '.$subject.$this->LE;
		}

		$copiedHeaders     = [];
		$headersToSignKeys = [];
		$headersToSign     = [];

		//Always sign these headers without being asked
		$autoSignHeaders = [
			'From', 'To', 'CC', 'Date', 'Subject', 'Reply-To', 'Message-ID', 'Content-Type', 'Mime-Version', 'X-Mailer',
		];

		$addHeader = function(array $h) use (&$headersToSignKeys, &$headersToSign, &$copiedHeaders):void{
			$headersToSignKeys[] = $h[0];
			$headersToSign[]     = $h[0].': '.$h[1];

			if($this->options->DKIM_copyHeaders){
				// Note no space after this, as per RFC
				$copiedHeaders[$h[0]] = $h[0].':'.str_replace('|', '=7C', DKIM_QP($h[1]));
			}

		};

		foreach(DKIM_parseHeaders(explode($this->LE, $headers)) as $header){
			//Is this header one that must be included in the DKIM signature?
			if(in_array($header[0], $autoSignHeaders, true)){
				$addHeader($header);

				continue;
			}

			//Is this an extra custom header we've been asked to sign?
			if(in_array($header[0], $this->options->DKIM_headers ?? [], true)){
				//Find its value in custom headers
				foreach($this->customHeaders as $customHeader){

					if($customHeader[0] === $header[0]){
						$addHeader($header);
						//Skip straight to the next header
						continue 2;
					}
				}
			}
		}

		$body  = DKIM_BodyC($body);
		$ident = '';
		$copiedHeaderFields = '';

		if($this->options->DKIM_identity){
			$ident = ' i='.$this->options->DKIM_identity.';'.$this->LE;
		}

		if($this->options->DKIM_copyHeaders){
			$copiedHeaderFields = DKIM_copyHeaders($copiedHeaders, $this->LE);
		}

		//The DKIM-Signature header is included in the signature *except for* the value of the `b` tag
		//which is appended after calculating the signature
		//https://tools.ietf.org/html/rfc6376#section-3.5
		$dkimSignatureHeader = 'DKIM-Signature: v=1;'.
			' d='.$this->options->DKIM_domain.';'.
			' s='.$this->options->DKIM_selector.';'.
			$this->LE.
			' a=rsa-sha256;'.
			' q=dns/txt;'.
			' l='.strlen($body).';'.
			' t='.time().';'.
			' c=relaxed/simple;'.
			$this->LE.
			' h='.implode(':', $headersToSignKeys).';'.
			$this->LE.
			$ident.
			$copiedHeaderFields.
			' bh='.base64_encode(pack('H*', hash('sha256', $body))).';'. // Base64 of packed binary SHA-256 hash of body
			$this->LE.
			' b=';

		//Canonicalize the set of headers
		$canonicalizedHeaders = DKIM_HeaderC(implode($this->LE, $headersToSign).$this->LE.$dkimSignatureHeader);
		$signature            = DKIM_Sign($canonicalizedHeaders, $this->options->DKIM_key, $this->options->DKIM_passphrase);
		$signature            = trim(chunk_split($signature, self::LINE_LENGTH_STD - 3, $this->LE.' '));

		return normalizeBreaks($dkimSignatureHeader.$signature, $this->LE).$this->LE;
	}

	/**
	 * Detect if a string contains a line longer than the maximum line length
	 * allowed by RFC 2822 section 2.1.1.
	 *
	 * @param string $str
	 *
	 * @return bool
	 */
	protected function hasLineLongerThanMax(string $str):bool{
		return (bool)preg_match('/^(.{'.($this::LINE_LENGTH_MAX + strlen($this->LE)).',})/m', $str);
	}

	/**
	 * Perform a callback.
	 *
	 * @param bool   $isSent
	 * @param array  $to
	 * @param array  $cc
	 * @param array  $bcc
	 * @param string $subject
	 * @param string $body
	 * @param string $from
	 * @param array  $extra
	 *
	 * @return void
	 */
	protected function doCallback(
		bool $isSent,
		array $to,
		array $cc,
		array $bcc,
		string $subject,
		string $body,
		string $from,
		array $extra
	):void{
		if($this->sendCallback instanceof Closure){
			$this->sendCallback->call($this, $isSent, $to, $cc, $bcc, $subject, $body, $from, $extra);
		}
	}

}
