<?php
/**
 * @filesource   functions.php
 * @created      07.04.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace PHPMailer\PHPMailer;

use DirectoryIterator, ReflectionClass;
use PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface;

use function array_key_exists, array_search, array_unique, array_unshift, base64_encode, call_user_func, ctype_alnum, count,
	date, date_default_timezone_get, date_default_timezone_set, escapeshellarg, escapeshellcmd, explode, file_exists,
	file_get_contents, filter_var, floor, function_exists, hash, hexdec, idn_to_ascii, imap_rfc822_parse_adrlist, implode,
	in_array, is_callable, is_file, is_link, is_numeric, is_readable, is_string, mb_check_encoding, mb_convert_encoding,
	mb_strlen, mb_substr, openssl_pkey_free, openssl_pkey_get_private, openssl_sign, ord, preg_match, preg_match_all,
	preg_replace, property_exists, random_bytes, readlink, sprintf, str_replace, strlen, strpos, strrpos, strtolower, substr,
	trim, html_entity_decode, strip_tags;

use const DIRECTORY_SEPARATOR, FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_VALIDATE_EMAIL, FILTER_VALIDATE_IP, FILTER_VALIDATE_URL,
	INTL_IDNA_VARIANT_UTS46, PATHINFO_BASENAME, PATHINFO_DIRNAME, PATHINFO_EXTENSION, PATHINFO_FILENAME, ENT_QUOTES;

const INCLUDES_PHPMAILER_FUNCTIONS = true;

const MIMETYPES = [
	'xl'    => 'application/excel',
	'js'    => 'application/javascript',
	'hqx'   => 'application/mac-binhex40',
	'cpt'   => 'application/mac-compactpro',
	'bin'   => 'application/macbinary',
	'doc'   => 'application/msword',
	'word'  => 'application/msword',
	'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
	'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
	'ppsx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
	'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	'sldx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
	'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
	'xlam'  => 'application/vnd.ms-excel.addin.macroEnabled.12',
	'xlsb'  => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
	'class' => 'application/octet-stream',
	'dll'   => 'application/octet-stream',
	'dms'   => 'application/octet-stream',
	'exe'   => 'application/octet-stream',
	'lha'   => 'application/octet-stream',
	'lzh'   => 'application/octet-stream',
	'psd'   => 'application/octet-stream',
	'sea'   => 'application/octet-stream',
	'so'    => 'application/octet-stream',
	'oda'   => 'application/oda',
	'pdf'   => 'application/pdf',
	'ai'    => 'application/postscript',
	'eps'   => 'application/postscript',
	'ps'    => 'application/postscript',
	'smi'   => 'application/smil',
	'smil'  => 'application/smil',
	'mif'   => 'application/vnd.mif',
	'xls'   => 'application/vnd.ms-excel',
	'ppt'   => 'application/vnd.ms-powerpoint',
	'wbxml' => 'application/vnd.wap.wbxml',
	'wmlc'  => 'application/vnd.wap.wmlc',
	'dcr'   => 'application/x-director',
	'dir'   => 'application/x-director',
	'dxr'   => 'application/x-director',
	'dvi'   => 'application/x-dvi',
	'gtar'  => 'application/x-gtar',
	'php3'  => 'application/x-httpd-php',
	'php4'  => 'application/x-httpd-php',
	'php'   => 'application/x-httpd-php',
	'phtml' => 'application/x-httpd-php',
	'phps'  => 'application/x-httpd-php-source',
	'swf'   => 'application/x-shockwave-flash',
	'sit'   => 'application/x-stuffit',
	'tar'   => 'application/x-tar',
	'tgz'   => 'application/x-tar',
	'xht'   => 'application/xhtml+xml',
	'xhtml' => 'application/xhtml+xml',
	'zip'   => 'application/zip',
	'mid'   => 'audio/midi',
	'midi'  => 'audio/midi',
	'mp2'   => 'audio/mpeg',
	'mp3'   => 'audio/mpeg',
	'm4a'   => 'audio/mp4',
	'mpga'  => 'audio/mpeg',
	'aif'   => 'audio/x-aiff',
	'aifc'  => 'audio/x-aiff',
	'aiff'  => 'audio/x-aiff',
	'ram'   => 'audio/x-pn-realaudio',
	'rm'    => 'audio/x-pn-realaudio',
	'rpm'   => 'audio/x-pn-realaudio-plugin',
	'ra'    => 'audio/x-realaudio',
	'wav'   => 'audio/x-wav',
	'mka'   => 'audio/x-matroska',
	'bmp'   => 'image/bmp',
	'gif'   => 'image/gif',
	'jpeg'  => 'image/jpeg',
	'jpe'   => 'image/jpeg',
	'jpg'   => 'image/jpeg',
	'png'   => 'image/png',
	'tiff'  => 'image/tiff',
	'tif'   => 'image/tiff',
	'webp'  => 'image/webp',
	'heif'  => 'image/heif',
	'heifs' => 'image/heif-sequence',
	'heic'  => 'image/heic',
	'heics' => 'image/heic-sequence',
	'eml'   => 'message/rfc822',
	'css'   => 'text/css',
	'html'  => 'text/html',
	'htm'   => 'text/html',
	'shtml' => 'text/html',
	'log'   => 'text/plain',
	'text'  => 'text/plain',
	'txt'   => 'text/plain',
	'rtx'   => 'text/richtext',
	'rtf'   => 'text/rtf',
	'vcf'   => 'text/vcard',
	'vcard' => 'text/vcard',
	'ics'   => 'text/calendar',
	'xml'   => 'text/xml',
	'xsl'   => 'text/xml',
	'wmv'   => 'video/x-ms-wmv',
	'mpeg'  => 'video/mpeg',
	'mpe'   => 'video/mpeg',
	'mpg'   => 'video/mpeg',
	'mp4'   => 'video/mp4',
	'm4v'   => 'video/mp4',
	'mov'   => 'video/quicktime',
	'qt'    => 'video/quicktime',
	'rv'    => 'video/vnd.rn-realvideo',
	'avi'   => 'video/x-msvideo',
	'movie' => 'video/x-sgi-movie',
	'webm'  => 'video/webm',
	'mkv'   => 'video/x-matroska',
];

/**
 * Check that a string looks like an email address.
 * Validation patterns supported:
 * *  null: Use PHP built-in FILTER_VALIDATE_EMAIL;
 * * `pcre` Use PCRE implementation;
 * * `html5` Use the pattern given by the HTML5 spec for 'email' type form input elements.
 *
 * @param string      $address   The email address to check
 * @param string|null $validator Which pattern to use
 *
 * @return bool
 */
function validateAddress(string $address, string $validator = null):bool{

	// Reject line breaks in addresses; it's valid RFC5322, but not RFC5321
	// @todo: allow & filter line breaks
	if(strpos($address, "\n") !== false || strpos($address, "\r") !== false){
		return false;
	}

	if($validator === 'pcre'){
		/*
		 * A more complex and more permissive version of the RFC5322 regex on which FILTER_VALIDATE_EMAIL
		 * is based.
		 * In addition to the addresses allowed by filter_var, also permits:
		 *  * dotless domains: `a@b`
		 *  * comments: `1234 @ local(blah) .machine .example`
		 *  * quoted elements: `'"test blah"@example.org'`
		 *  * numeric TLDs: `a@b.123`
		 *  * unbracketed IPv4 literals: `a@192.168.0.1`
		 *  * IPv6 literals: 'first.last@[IPv6:a1::]'
		 * Not all of these will necessarily work for sending!
		 *
		 * @see       http://squiloople.com/2009/12/20/email-address-validation/
		 * @copyright 2009-2010 Michael Rushton
		 * Feel free to use and redistribute this code. But please keep this copyright notice.
		 */
		$pattern = '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)'.
		           '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)'.
		           '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)'.
		           '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*'.
		           '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)'.
		           '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}'.
		           '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:'.
		           '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}'.
		           '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD';

		return (bool)preg_match($pattern, $address);
	}

	if($validator === 'html5'){
		/*
		 * This is the pattern used in the HTML5 spec for validation of 'email' type form input elements.
		 *
		 * @see http://www.whatwg.org/specs/web-apps/current-work/#e-mail-state-(type=email)
		 */
		$pattern = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}'.
		           '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD';

		return (bool)preg_match($pattern, $address);
	}

	return (bool)filter_var($address, FILTER_VALIDATE_EMAIL);
}

/**
 * Parse and validate a string containing one or more RFC822-style comma-separated email addresses
 * of the form "display name <address>" into an array of name/address pairs.
 * Uses the imap_rfc822_parse_adrlist function if the IMAP extension is available.
 * Note that quotes in the name part are removed.
 *
 * @see    http://www.andrew.cmu.edu/user/agreen1/testing/mrbs/web/Mail/RFC822.php A more careful implementation
 *
 * @param string      $addrstr   The address list string
 * @param bool        $useimap   Whether to use the IMAP extension to parse the list
 * @param string|null $validator Which pattern to use
 *
 * @return array
 */
function parseAddresses(string $addrstr, bool $useimap = true, string $validator = null):array{
	$addresses = [];

	if($useimap && function_exists('imap_rfc822_parse_adrlist')){
		// Use this built-in parser if it's available
		$list = imap_rfc822_parse_adrlist($addrstr, '');
		foreach($list as $address){

			if($address->host !== '.SYNTAX-ERROR.'){

				if(validateAddress($address->mailbox.'@'.$address->host, $validator)){
					$addresses[] = [
						'name'    => (property_exists($address, 'personal') ? $address->personal : ''),
						'address' => $address->mailbox.'@'.$address->host,
					];
				}

			}

		}
	}
	else{
		// Use this simpler parser
		$list = explode(',', $addrstr);
		foreach($list as $address){
			$address = trim($address);
			// Is there a separate name part?
			if(strpos($address, '<') === false){

				// No separate name, just use the whole thing
				if(validateAddress($address, $validator)){
					$addresses[] = [
						'name'    => '',
						'address' => $address,
					];
				}

			}
			else{
				[$name, $email] = explode('<', $address);
				$email = trim(str_replace('>', '', $email));

				if(validateAddress($email, $validator)){
					$addresses[] = [
						'name'    => trim(str_replace(['"', "'"], '', $name)),
						'address' => $email,
					];
				}

			}
		}
	}

	return $addresses;
}

/**
 * Tells whether IDNs (Internationalized Domain Names) are supported or not. This requires the
 * `intl` and `mbstring` PHP extensions.
 *
 * @return bool `true` if required functions for IDN support are present
 */
function idnSupported():bool{
	return function_exists('idn_to_ascii') && function_exists('mb_convert_encoding');
}

/**
 * Return an RFC 822 formatted date.
 *
 * @return string
 */
function rfcDate():string{
	// Set the time zone to whatever the default is to avoid 500 errors
	// Will default to UTC if it's not set properly in php.ini
	date_default_timezone_set(@date_default_timezone_get());

	return date('D, j M Y H:i:s O');
}

/**
 * Validate whether a string contains a valid value to use as a hostname or IP address.
 * IPv6 addresses must include [], e.g. `[::1]`, not just `::1`.
 *
 * @param string $host The host name or IP address to check
 *
 * @return bool
 */
function isValidHost(string $host):bool{

	// Simple syntax limits
	if(empty($host) || !is_string($host) || strlen($host) > 256){
		return false;
	}

	// Looks like a bracketed IPv6 address
	if(trim($host, '[]') !== $host){
		return (bool)filter_var(trim($host, '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
	}

	// If removing all the dots results in a numeric string, it must be an IPv4 address.
	// Need to check this first because otherwise things like `999.0.0.0` are considered valid host names
	if(is_numeric(str_replace('.', '', $host))){
		//Is it a valid IPv4 address?
		return (bool)filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}

	if(filter_var('http://'.$host, FILTER_VALIDATE_URL)){
		// Is it a syntactically valid hostname?
		return true;
	}

	return false;
}

/**
 * Get the MIME type for a file extension.
 *
 * @param string $ext File extension
 *
 * @return string MIME type of file
 */
function get_mime_type(string $ext = ''):string{
	$ext = strtolower($ext);

	if(array_key_exists($ext, MIMETYPES)){
		return MIMETYPES[$ext];
	}

	return 'application/octet-stream';
}

/**
 * Map a file name to a MIME type.
 * Defaults to 'application/octet-stream', i.e.. arbitrary binary data.
 *
 * @param string $filename A file name or full path, does not need to exist as a file
 *
 * @return string
 */
function filenameToType(string $filename):string{
	// In case the path is a URL, strip any query string before getting extension
	$qpos = strpos($filename, '?');

	if($qpos !== false){
		$filename = substr($filename, 0, $qpos);
	}

	return get_mime_type(mb_pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Multi-byte-safe pathinfo replacement.
 * Drop-in replacement for pathinfo(), but multibyte- and cross-platform-safe.
 *
 * @see    http://www.php.net/manual/en/function.pathinfo.php#107461
 *
 * @param string     $path    A filename or path, does not need to exist as a file
 * @param int|string $options Either a PATHINFO_* constant,
 *                            or a string name to return only the specified piece
 *
 * @return string|array
 */
function mb_pathinfo(string $path, $options = null){
	$ret      = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
	$pathinfo = [];

	/** @noinspection RegExpRedundantEscape */
	if(preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$#im', $path, $pathinfo)){

		if(array_key_exists(1, $pathinfo)){
			$ret['dirname'] = $pathinfo[1];
		}

		if(array_key_exists(2, $pathinfo)){
			$ret['basename'] = $pathinfo[2];
		}

		if(array_key_exists(5, $pathinfo)){
			$ret['extension'] = $pathinfo[5];
		}

		if(array_key_exists(3, $pathinfo)){
			$ret['filename'] = $pathinfo[3];
		}

	}

	switch($options){
		case PATHINFO_DIRNAME:
		case 'dirname':
			return $ret['dirname'];
		case PATHINFO_BASENAME:
		case 'basename':
			return $ret['basename'];
		case PATHINFO_EXTENSION:
		case 'extension':
			return $ret['extension'];
		case PATHINFO_FILENAME:
		case 'filename':
			return $ret['filename'];
	}

	return $ret;
}

/**
 * Fix CVE-2016-10033 and CVE-2016-10045 by disallowing potentially unsafe shell characters.
 * Note that escapeshellarg and escapeshellcmd are inadequate for our purposes, especially on Windows.
 *
 * @see https://github.com/PHPMailer/PHPMailer/issues/924 CVE-2016-10045 bug report
 *
 * @param string $string The string to be validated
 *
 * @return bool
 */
function isShellSafe(string $string):bool{
	// Future-proof
	if(escapeshellcmd($string) !== $string || !in_array(escapeshellarg($string), ["'$string'", "\"$string\""])){
		return false;
	}

	$length = strlen($string);

	for($i = 0; $i < $length; ++$i){
		$c = $string[$i];

		// All other characters have a special meaning in at least one common shell, including = and +.
		// Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
		// Note that this does permit non-Latin alphanumeric characters based on the current locale.
		if(!ctype_alnum($c) && strpos('@_-.', $c) === false){
			return false;
		}
	}

	return true;
}

/**
 * Check whether a file path is of a permitted type.
 * Used to reject URLs and phar files from functions that access local file paths,
 * such as addAttachment.
 *
 * @param string $path A relative or absolute path to a file
 *
 * @return bool
 */
function isPermittedPath(string $path):bool{
	return !preg_match('#^[a-z]+://#i', $path);
}

/**
 * Checks whether a file (or link) exists & is readable
 *
 * @param string $file
 *
 * @return bool
 */
function fileCheck(string $file):bool{
	return file_exists($file) && is_readable($file) && (is_file($file) || (is_link($file) && is_file(readlink($file))));
}

/**
 * Create a unique ID to use for boundaries.
 *
 * @return string
 */
function generateId():string{
	$bytes = random_bytes(32); //32 bytes = 256 bits

	//We don't care about messing up base64 format here, just want a random string
	return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
}

/**
 * @param string|null $uniqueid
 *
 * @return array
 */
function generateBoundary(string $uniqueid = null):array{
	$uniqueid = $uniqueid ?? generateId();

	return [
		1 => 'b1_'.$uniqueid,
		2 => 'b2_'.$uniqueid,
		3 => 'b3_'.$uniqueid,
	];
}

/**
 * Does a string contain any 8-bit chars (in any charset)?
 *
 * @param string $text
 *
 * @return bool
 */
function has8bitChars(string $text):bool{
	return (bool)preg_match('/[\x80-\xFF]/', $text);
}

/**
 * Strip newlines to prevent header injection.
 *
 * @param string $str
 *
 * @return string
 */
function secureHeader(string $str):string{
	return trim(str_replace(["\r", "\n"], '', $str));
}

/**
 * Find the last character boundary prior to $maxLength in a utf-8
 * quoted-printable encoded string.
 * Original written by Colin Brown.
 *
 * @param string $encodedText utf-8 QP text
 * @param int    $maxLength   Find the last character boundary prior to this length
 *
 * @return int
 */
function utf8CharBoundary(string $encodedText, int $maxLength):int{
	$foundSplitPos = false;
	$lookBack      = 3;

	while(!$foundSplitPos){
		$lastChunk      = substr($encodedText, $maxLength - $lookBack, $lookBack);
		$encodedCharPos = strpos($lastChunk, '=');

		if($encodedCharPos !== false){
			// Found start of encoded character byte within $lookBack block.
			// Check the encoded byte value (the 2 chars after the '=')
			$hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
			$dec = hexdec($hex);

			if($dec < 128){
				// Single byte character.
				// If the encoded char was found at pos 0, it will fit
				// otherwise reduce maxLength to start of the encoded char
				if($encodedCharPos > 0){
					$maxLength -= $lookBack - $encodedCharPos;
				}

				$foundSplitPos = true;
			}
			elseif($dec >= 192){
				// First byte of a multi byte character
				// Reduce maxLength to split at start of character
				$maxLength     -= $lookBack - $encodedCharPos;
				$foundSplitPos = true;
			}
			elseif($dec < 192){
				// Middle byte of a multi byte character, look further back
				$lookBack += 3;
			}
		}
		else{
			// No encoded character found
			$foundSplitPos = true;
		}
	}

	return $maxLength;
}

/**
 * Encode a string using Q encoding.
 *
 * @see http://tools.ietf.org/html/rfc2047#section-4.2
 *
 * @param string $str      the text to encode
 * @param string $position Where the text is going to be used, see the RFC for what that means
 *
 * @return string
 */
function encodeQ(string $str, string $position = 'text'):string{
	// There should not be any EOL in the string
	$pattern = '';
	$encoded = str_replace(["\r", "\n"], '', $str);

	switch(strtolower($position)){
		case 'phrase':
			// RFC 2047 section 5.3
			$pattern = '^A-Za-z0-9!*+\/ -';
			break;
		/*
		 * RFC 2047 section 5.2.
		 * Build $pattern without including delimiters and []
		 */
		/* @noinspection PhpMissingBreakStatementInspection */
		case 'comment':
			$pattern = '\(\)"';
		/* Intentional fall through */
		case 'text':
		default:
			// RFC 2047 section 5.1
			// Replace every high ascii, control, =, ? and _ characters
			/** @noinspection SuspiciousAssignmentsInspection */
			$pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377'.$pattern;
	}

	$matches = [];

	if(preg_match_all("/[{$pattern}]/", $encoded, $matches)){
		// If the string contains an '=', make sure it's the first thing we replace
		// so as to avoid double-encoding
		$eqkey = array_search('=', $matches[0]);

		if($eqkey !== false){
			unset($matches[0][$eqkey]);
			array_unshift($matches[0], '=');
		}

		foreach(array_unique($matches[0]) as $char){
			$encoded = str_replace($char, '='.sprintf('%02X', ord($char)), $encoded);
		}
	}

	// Replace spaces with _ (more readable than =20)
	// RFC 2047 section 4.2(2)
	return str_replace(' ', '_', $encoded);
}

/**
 * @param array $headerLines
 *
 * @return array
 */
function DKIM_parseHeaders(array $headerLines):array{
	$currentHeaderLabel = '';
	$currentHeaderValue = '';
	$parsedHeaders      = [];
	$headerLineIndex    = 0;
	$headerLineCount    = count($headerLines);

	foreach($headerLines as $headerLine){
		$matches = [];

		if(preg_match('/^([^ \t]*?)(?::[ \t]*)(.*)$/', $headerLine, $matches)){

			if($currentHeaderLabel !== ''){
				//We were previously in another header; This is the start of a new header, so save the previous one
				$parsedHeaders[] =  [$currentHeaderLabel, $currentHeaderValue];
			}

			$currentHeaderLabel = $matches[1];
			$currentHeaderValue = $matches[2];
		}
		elseif(preg_match('/^[ \t]+(.*)$/', $headerLine, $matches)){
			//This is a folded continuation of the current header, so unfold it
			$currentHeaderValue .= ' '.$matches[1];
		}

		++$headerLineIndex;

		if($headerLineIndex >= $headerLineCount){
			//This was the last line, so finish off this header
			$parsedHeaders[] = [$currentHeaderLabel, $currentHeaderValue];
		}

	}

	return $parsedHeaders;
}

/**
 * @param array  $copiedHeaders
 * @param string $nl
 *
 * @return string
 */
function DKIM_copyHeaders(array $copiedHeaders, string $nl):string{

	if(empty($copiedHeaders)){
		return '';
	}

	//Assemble a DKIM 'z' tag
	$copiedHeaderFields = ' z=';
	$first              = true;

	foreach($copiedHeaders as $copiedHeader){

		if(!$first){
			$copiedHeaderFields .= $nl.' |';
		}

		//Fold long values
		if(strlen($copiedHeader) > PHPMailerInterface::LINE_LENGTH_STD - 3){
			$copiedHeaderFields .= substr(
				chunk_split($copiedHeader, PHPMailerInterface::LINE_LENGTH_STD - 3, $nl.' '),
				0,
				-strlen($nl.' ')
			);
		}
		else{
			$copiedHeaderFields .= $copiedHeader;
		}

		$first = false;
	}

	return $copiedHeaderFields.';'.$nl;
}

/**
 * Quoted-Printable-encode a DKIM header.
 *
 * @param string $str
 *
 * @return string
 */
function DKIM_QP(string $str):string{
	$line = '';
	$len  = strlen($str);

	for($i = 0; $i < $len; ++$i){
		$ord = ord($str[$i]);

		$line .= ($ord > 0x21 && $ord <= 0x3A) || $ord === 0x3C || ($ord > 0x3E && $ord <= 0x7E)
			? $str[$i]
			: '='.sprintf('%02X', $ord);
	}

	return $line;
}

/**
 * Generate a DKIM canonicalization header.
 * Uses the 'relaxed' algorithm from RFC6376 section 3.4.2.
 * Canonicalized headers should *always* use CRLF, regardless of mailer setting.
 *
 * @see    https://tools.ietf.org/html/rfc6376#section-3.4.2
 *
 * @param string $signHeader Header
 *
 * @return string
 */
function DKIM_HeaderC(string $signHeader):string{
	// Note PCRE \s is too broad a definition of whitespace; RFC5322 defines it as `[ \t]`
	// @see https://tools.ietf.org/html/rfc5322#section-2.2
	// That means this may break if you do something daft like put vertical tabs in your headers.
	//Unfold header lines
	$signHeader = preg_replace('/\r\n[ \t]+/', ' ', $signHeader);
	//Collapse internal whitespace to a single space
#	$signHeader = preg_replace('/[ \t]+/', ' ', $signHeader);
	//Break headers out into an array
	$lines      = explode("\r\n", $signHeader);

	foreach($lines as $key => $line){
		// If the header is missing a :, skip it as it's invalid
		// This is likely to happen because the explode() above will also split
		// on the trailing LE, leaving an empty line
		if(strpos($line, ':') === false){
			continue;
		}

		[$heading, $value] = explode(':', $line, 2);
		// Lower-case header name
		$heading = strtolower($heading);
		// Collapse white space within the value, also convert WSP to space
		$value = preg_replace('/[ \t]+/', ' ', $value);
		// RFC6376 is slightly unclear here - it says to delete space at the *end* of each value
		// But then says to delete space before and after the colon.
		// Net result is the same as trimming both ends of the value.
		// by elimination, the same applies to the field name
		$lines[$key] = trim($heading, " \t").':'.trim($value, " \t");
	}

	return implode("\r\n", $lines);
}

/**
 * Generate a DKIM canonicalization body.
 * Uses the 'simple' algorithm from RFC6376 section 3.4.3.
 * Canonicalized bodies should *always* use CRLF, regardless of mailer setting.
 *
 * @see    https://tools.ietf.org/html/rfc6376#section-3.4.3
 *
 * @param string $body Message Body
 *
 * @return string
 */
function DKIM_BodyC(string $body):string{

	if(empty($body)){
		return "\r\n";
	}

	// Normalize line endings to CRLF
	$body = normalizeBreaks($body, "\r\n");

	//Reduce multiple trailing line breaks to a single one
	return rtrim($body, "\r\n")."\r\n";
}

/**
 * Generate a DKIM signature.
 *
 * @param string      $signHeader
 * @param string      $key
 * @param string|null $passphrase
 *
 * @return string The DKIM signature value
 * @throws \PHPMailer\PHPMailer\PHPMailerException
 */
function DKIM_Sign(string $signHeader, string $key, string $passphrase = null):string{

	if(empty($key)){
		throw new PHPMailerException('invalid DKIM private key');
	}

	if(fileCheck($key)){

		if(!isPermittedPath($key)){
			throw new PHPMailerException('path to DKIM private key is not permitted');
		}

		$key = file_get_contents($key);
	}

	$privKey = !empty($passphrase)
		? openssl_pkey_get_private($key, $passphrase)
		: openssl_pkey_get_private($key);

	if(openssl_sign($signHeader, $signature, $privKey, 'sha256WithRSAEncryption')){
		openssl_pkey_free($privKey);

		return base64_encode($signature);
	}

	openssl_pkey_free($privKey);

	return '';
}

/**
 * Converts IDN in given email address to its ASCII form, also known as punycode, if possible.
 * Important: Address must be passed in same encoding as currently set in PHPMailer::$CharSet.
 * This function silently returns unmodified address if:
 * - No conversion is necessary (i.e. domain name is not an IDN, or is already in ASCII form)
 * - Conversion to punycode is impossible (e.g. required PHP functions are not available)
 *   or fails for any reason (e.g. domain contains characters not allowed in an IDN).
 *
 * @param string $address The email address to convert
 * @param string $charset
 *
 * @return string The encoded address in ASCII form
 * @see    PHPMailerInterface::$charSet
 */
function punyencodeAddress(string $address, string $charset = PHPMailerInterface::CHARSET_ISO88591):string{
	// Verify we have required functions, CharSet, and at-sign.
	$pos = strrpos($address, '@');
	if(idnSupported() && !empty($charset) && $pos !== false){
		$domain = substr($address, ++$pos);
		// Verify CharSet string is a valid one, and domain properly encoded in this CharSet.
		if(has8bitChars($domain) && @mb_check_encoding($domain, $charset)){
			$domain = mb_convert_encoding($domain, 'UTF-8', $charset);
			//Ignore IDE complaints about this line - method signature changed in PHP 5.4
			$errorcode = 0;
			/** @noinspection PhpComposerExtensionStubsInspection */
			$punycode = idn_to_ascii($domain, $errorcode, INTL_IDNA_VARIANT_UTS46);

			if($punycode !== false){
				return substr($address, 0, $pos).$punycode;
			}
		}
	}

	return $address;
}

/**
 * Normalize line breaks in a string.
 * Converts UNIX LF, Mac CR and Windows CRLF line breaks into a single line break format.
 * Defaults to CRLF (for message bodies) and preserves consecutive breaks.
 *
 * @param string $text
 * @param string $breaktype What kind of line break to use; defaults to $this->LE
 *
 * @return string
 */
function normalizeBreaks(string $text, string $breaktype):string{

	// Normalise to \n
	$text = str_replace(["\r\n", "\r"], "\n", $text);
	// Now convert LE as needed
	if($breaktype !== "\n"){
		$text = str_replace("\n", $breaktype, $text);
	}

	return $text;
}

/**
 * Convert an HTML string into plain text.
 * This is used by messageFromHTML().
 * Note - older versions of this function used a bundled advanced converter
 * which was removed for license reasons in #232.
 * Example usage:
 *
 * ```php
 * // Use default conversion
 * $plain = $mail->html2text($html);
 * // Use your own custom converter
 * $plain = $mail->html2text($html, function($html) {
 *     $converter = new MyHtml2text($html);
 *     return $converter->get_text();
 * });
 * ```
 *
 * @param string        $html     The HTML text to convert
 * @param string        $charset
 * @param null|callable $advanced Any boolean value to use the internal converter,
 *                                or provide your own callable for custom conversion
 *
 * @return string
 */
function html2text(string $html, string $charset, $advanced = null):string{

	if(is_callable($advanced)){
		return call_user_func($advanced, $html);
	}

	return html_entity_decode(
		trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))),
		ENT_QUOTES,
		$charset
	);
}

/**
 * @return array
 */
function getLanguages():array{
	$languages = [];

	/** @var \SplFileInfo $e */
	foreach(new DirectoryIterator(__DIR__.DIRECTORY_SEPARATOR.'Language') as $e){

		if($e->isDot() || $e->getExtension() !== 'php'){
			continue;
		}

		$class = __NAMESPACE__.str_replace('/', '\\', substr($e->getPathname(), strlen(__DIR__), -4));
		$r     = new ReflectionClass($class);

		if(!$r->implementsInterface(PHPMailerLanguageInterface::class) || $r->isAbstract()){
			continue;
		}

		$languages[$class] = $r
			->newInstance()
			->strings(['code', 'dir', 'name', 'native_name'])
		;
	}

	return $languages;
}

/**
 * Encode and wrap long multibyte strings for mail headers
 * without breaking lines within a character.
 * Adapted from a function by paravoid.
 *
 * @see http://www.php.net/manual/en/function.mb-encode-mimeheader.php#60283
 *
 * @param string $str       multi-byte text to wrap encode
 * @param string $charset
 * @param string $linebreak string to use as linefeed/end-of-line
 *
 * @return string
 */
function base64EncodeWrapMB(string $str, string $charset, string $linebreak):string{
	$start     = '=?'.$charset.'?B?';
	$end       = '?=';
	$encoded   = '';

	$mb_length = mb_strlen($str, $charset);
	// Each line must have length <= 75, including $start and $end
	$length = 75 - strlen($start) - strlen($end);
	// Average multi-byte ratio
	$ratio = $mb_length / strlen($str);
	// Base64 has a 4:3 ratio
	$avgLength = floor($length * $ratio * .75);

	for($i = 0; $i < $mb_length; $i += $offset){
		$lookBack = 0;

		do{
			$offset = $avgLength - $lookBack;
			$chunk  = mb_substr($str, $i, $offset, $charset);
			$chunk  = base64_encode($chunk);
			++$lookBack;
		}
		while(strlen($chunk) > $length);

		$encoded .= $chunk.$linebreak;
	}

	// Chomp the last linefeed
	return substr($encoded, 0, -strlen($linebreak));
}

/**
 * Check if a string contains multi-byte characters.
 *
 * @param string $str multi-byte text to wrap encode
 * @param string $charset
 *
 * @return bool
 */
function hasMultiBytes(string $str, string $charset):bool{
	return strlen($str) > mb_strlen($str, $charset);
}

/**
 * Word-wrap message.
 * For use with mailers that do not automatically perform wrapping
 * and for quoted-printable encoded messages.
 * Original written by philippe.
 *
 * @param string $message The message to wrap
 * @param int    $length  The line length to wrap to
 * @param string $charset
 * @param string $nl
 * @param bool   $qp_mode Whether to run in Quoted-Printable mode
 *
 * @return string
 */
function wrapText(string $message, int $length, string $charset, string $nl, bool $qp_mode = false):string{

	if($length < 1){
		return $message;
	}

	$soft_break = $qp_mode ? sprintf(' =%s', $nl) : $nl;

	// If utf-8 encoding is used, we will need to make sure we don't
	// split multibyte characters when we wrap
	$is_utf8 = strtolower($charset) === PHPMailerInterface::CHARSET_UTF8;
	$lelen   = strlen($nl);

	$message = normalizeBreaks($message, $nl);
	//Remove a trailing line break
	if(substr($message, -$lelen) == $nl){
		$message = substr($message, 0, -$lelen);
	}

	//Split message into lines
	$lines = explode($nl, $message);
	//Message will be rebuilt in here
	$message = '';
	foreach($lines as $line){
		$words     = explode(' ', $line);
		$buf       = '';
		$firstword = true;

		foreach($words as $word){

			if($qp_mode && (strlen($word) > $length)){
				$space_left = $length - strlen($buf) - $lelen;

				if(!$firstword){
					if($space_left > 20){
						$len = $space_left;

						if($is_utf8){
							$len = utf8CharBoundary($word, $len);
						}
						elseif(substr($word, $len - 1, 1) === '='){
							--$len;
						}
						elseif(substr($word, $len - 2, 1) === '='){
							$len -= 2;
						}

						$part    = substr($word, 0, $len);
						$word    = substr($word, $len);
						$buf     .= ' '.$part;
						$message .= $buf.sprintf('=%s', $nl);
					}
					else{
						$message .= $buf.$soft_break;
					}

					$buf = '';
				}

				while(strlen($word) > 0){

					if($length <= 0){
						break;
					}

					$len = $length;

					if($is_utf8){
						$len = utf8CharBoundary($word, $len);
					}
					elseif(substr($word, $len - 1, 1) === '='){
						--$len;
					}
					elseif(substr($word, $len - 2, 1) === '='){
						$len -= 2;
					}

					$part = substr($word, 0, $len);
					$word = substr($word, $len);

					if(strlen($word) > 0){
						$message .= $part.sprintf('=%s', $nl);
					}
					else{
						$buf = $part;
					}

				}
			}
			else{
				$buf_o = $buf;

				if(!$firstword){
					$buf .= ' ';
				}

				$buf .= $word;

				if(strlen($buf) > $length && $buf_o !== ''){
					$message .= $buf_o.$soft_break;
					$buf     = $word;
				}
			}

			$firstword = false;
		}

		$message .= $buf.$nl;
	}

	return $message;
}

