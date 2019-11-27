<?php
/**
 * Class UnitTest
 *
 * @filesource   UnitTest.php
 * @created      11.04.2019
 * @package      PHPMailer\Test
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\{PHPMailer, PHPMailerException};

use function PHPMailer\PHPMailer\{generateId, normalizeBreaks, punyencodeAddress};
use function file_get_contents, hash, mb_convert_encoding, openssl_pkey_export_to_file,
	openssl_pkey_new, quoted_printable_decode, realpath, str_repeat, str_replace, strlen, unlink;

use const OPENSSL_KEYTYPE_RSA;

class UnitTest extends TestAbstract{

	public function invalidAttachmentProvider():array{
		return [
			'remote URL'    => ['https://github.com/PHPMailer/PHPMailer/raw/master/README.md'],
			'phar resource' => ['phar://phar.php'],
			'nonexistent'   => ['./foo.bar'],
		];
	}

	/**
	 * Rejection of non-local file attachments test.
	 *
	 * @dataProvider invalidAttachmentProvider
	 *
	 * @param string $path
	 */
	public function testRejectNonLocalFileAttachment(string $path){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Could not access file: '.$path);

		$this->mailer->addAttachment($path);
	}

	public function testAddAttachmentInvalidEncoding(){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Unknown encoding: whatever');

		$this->mailer->addAttachment(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'), 'foo', 'whatever');
	}

	/**
	 * Test header encoding & folding.
	 */
	public function testHeaderEncodingB(){
		// This should select B-encoding automatically and should not fold
		$exp = '=?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6k=?=';
		$act = str_repeat('é', 10);
		$this->assertSame($exp, $this->mailer->encodeHeader($act), 'B-encoded header value incorrect');
	}

	public function testHeaderEncodingFoldedB(){
		// This should select B-encoding automatically and should fold
		$exp = '=?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqQ==?=';
		$act = str_repeat('é', $this->mailer::LINE_LENGTH_STD + 1);
		$this->assertSame($exp, $this->mailer->encodeHeader($act), 'Folded B-encoded header value incorrect');
	}

	public function testHeaderEncodingQ(){
		// This should select Q-encoding automatically and should not fold
		$exp = '=?UTF-8?Q?eeeeeeeee=C3=A9?=';
		$act = str_repeat('e', 9).'é';
		$this->assertSame($exp, $this->mailer->encodeHeader($act), 'Q-encoded header value incorrect');
	}

	public function testHeaderEncodingUnencoded(){
		// This should not change
		$exp = 'eeeeeeeeee';
		$act = 'eeeeeeeeee';
		$this->assertSame($exp, $this->mailer->encodeHeader($act), 'Unencoded header value incorrect');
	}

	public function testHeaderEncodingFoldedQUtf8(){
		//This should Q-encode as UTF-8 and fold
		$act = str_repeat('é', $this->mailer::LINE_LENGTH_STD + 10);
		$exp = '=?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqQ==?=';

		$this->assertEquals($exp, $this->mailer->encodeHeader($act), 'Long UTF-8 header value incorrect');
	}

	public function messageTypeDataprovider():array{
		return [
			'inline'            => ['inline'],
			'attach'            => ['attach'],
			'inline_attach'     => ['inline_attach'],
			'alt'               => ['alt'],
			'alt_inline'        => ['alt_inline'],
			'alt_attach'        => ['alt_attach'],
			'alt_inline_attach' => ['alt_inline_attach'],
		];
	}

	/**
	 * @dataProvider messageTypeDataprovider
	 *
	 * @param string $messageType
	 */
	public function testCreateBody(string $messageType){
		$this->setProperty($this->mailer, 'messageType', $messageType);

		$uid  = generateId();
		$body = $this->mailer->createBody($uid);

		$this->assertIsString($body);
	}

	/**
	 * @todo: line endings
	 * @see PHPMailer::preSend()
	 *
	 * Test constructing a multipart message that contains lines that are too long for RFC compliance.
	 */
	public function testLongBody(){
		$this->mailer->setContentType($this->mailer::CONTENT_TYPE_PLAINTEXT);
		$this->mailer->setEncoding($this->mailer::ENCODING_8BIT);

		$oklen  = str_repeat(str_repeat('0', $this->mailer::LINE_LENGTH_MAX).$this->mailer->getLE(), 2);
		// Use +2 to ensure line length is over limit - LE may only be 1 char
		$badlen = str_repeat(str_repeat('1', $this->mailer::LINE_LENGTH_MAX + 2).$this->mailer->getLE(), 2);

		$body = 'This message contains lines that are too long.'.$this->mailer->getLE().$oklen.$badlen.$oklen;

		$this->assertTrue(
			$this->mailer->hasLineLongerThanMax($body),
			'Test content does not contain long lines!'
		);

		$body = $this->buildBody($body);

		$this->setMessage($body, __FUNCTION__);

		$this->mailer->setMessageBody($body);
		$this->mailer->setAltBody($body);

		$this->mailer->preSend();

		$message = $this->mailer->getSentMIMEMessage();

		$this->assertFalse(
			$this->mailer->hasLineLongerThanMax($message),
			'Long line not corrected (Max: '.($this->mailer::LINE_LENGTH_MAX + strlen($this->mailer->getLE())).' chars)'
		);

		$this->assertStringContainsString(
			'Content-Transfer-Encoding: quoted-printable',
			$message,
			'Long line did not cause transfer encoding switch.'
		);
	}

	/**
	 * @todo: line endings
	 * @see PHPMailer::preSend()
	 *
	 * Test constructing a message that does NOT contain lines that are too long for RFC compliance.
	 */
	public function testShortBody(){
		$this->mailer->setEncoding($this->mailer::ENCODING_8BIT);

		$oklen = str_repeat(str_repeat('0', $this->mailer::LINE_LENGTH_MAX).$this->mailer->getLE(), 10);
		$body  = 'This message does not contain lines that are too long.'.$this->mailer->getLE().$oklen;

		$this->assertFalse(
			$this->mailer->hasLineLongerThanMax($this->getPropertyValue('body')),
			'Test content contains long lines!'
		);

		$this->setMessage($this->buildBody($body), __FUNCTION__);

		$this->mailer->preSend();

		$message = $this->mailer->getSentMIMEMessage();

		$this->assertFalse($this->mailer->hasLineLongerThanMax($message), 'Long line not corrected.');

		// @todo: acts out on linux
#		$this->assertStringNotContainsString('Content-Transfer-Encoding: quoted-printable', $message, 'Short line caused transfer encoding switch.');
	}

	/**
	 * Tests this denial of service attack.
	 *
	 * @see https://sourceforge.net/p/phpmailer/bugs/383/
	 */
	public function testCVE_2010_2423(){
		//Encoding name longer than 68 chars
		$this->mailer->setEncoding('1234567890123456789012345678901234567890123456789012345678901234567890');
		//Call wrapText with a zero length value
		$this->mailer->wrapText(str_repeat('This should no longer cause a denial of service. ', 30), 0);

		$this->markTestIncomplete('According to the ticket, this should get stuck in a loop, though I can\'t make it happen.');
	}

	/**
	 * Test addressing.
	 */
	public function testAddressing(){
		$this->assertFalse($this->mailer->addTO(''), 'Empty address accepted');
		$this->assertFalse($this->mailer->addTO('', 'Nobody'), 'Empty address with name accepted');
		$this->assertFalse($this->mailer->addTO('a@example..com'), 'Invalid address accepted');
		$this->assertTrue($this->mailer->addTO('a@example.com'), 'Addressing failed');
		$this->assertFalse($this->mailer->addTO('a@example.com'), 'Duplicate addressing failed');
		$this->assertTrue($this->mailer->addCC('b@example.com'), 'CC addressing failed');
		$this->assertFalse($this->mailer->addCC('b@example.com'), 'CC duplicate addressing failed');
		$this->assertFalse($this->mailer->addCC('a@example.com'), 'CC duplicate addressing failed (2)');
		$this->assertTrue($this->mailer->addBCC('c@example.com'), 'BCC addressing failed');
		$this->assertFalse($this->mailer->addBCC('c@example.com'), 'BCC duplicate addressing failed');
		$this->assertFalse($this->mailer->addBCC('a@example.com'), 'BCC duplicate addressing failed (2)');
		$this->assertTrue($this->mailer->addReplyTo('a@example.com'), 'Replyto Addressing failed');
		$this->assertFalse($this->mailer->addReplyTo('a@example..com'), 'Invalid Replyto address accepted');

		$this->mailer->clearCCs();
		$this->mailer->clearBCCs();
		$this->mailer->clearReplyTos();
	}

	public function testSetFrom(){
		// @todo
		$this->mailer->setFrom('a@example.com', 'some name', true);
		$this->assertSame($this->getPropertyValue('sender'), 'a@example.com', 'setFrom failed to set sender');
		$this->mailer->setSender('');
		$this->mailer->setFrom('a@example.com', 'some name', false);
		$this->assertNull($this->getPropertyValue('sender'), 'setFrom should not have set sender');

		// test double quotes in from name
		$this->mailer->setFrom('bob@example.com', '"Bob\'s Burgers" (Bob\'s "Burgers")', true);
		$this->assertSame($this->getPropertyValue('from'), 'bob@example.com');
		$this->assertSame($this->getPropertyValue('fromName'), '"Bob\'s Burgers" (Bob\'s "Burgers")');
	}

	public function testSetFromInvalidAddressException(){
		$this->expectException(PHPMailerException::class);
		$this->mailer->setFrom('a@example.com.', 'some name');
	}


	/**
	 * Tests removal of duplicate recipients and reply-tos.
	 *
	 * @group network
	 */
	public function testDuplicateIDNAddressRemoved(){
		$this->assertTrue($this->mailer->addTO('test@françois.ch'));
		$this->assertFalse($this->mailer->addTO('test@françois.ch'));
		$this->assertTrue($this->mailer->addTO('test@FRANÇOIS.CH'));
		$this->assertFalse($this->mailer->addTO('test@FRANÇOIS.CH'));
		$this->assertTrue($this->mailer->addTO('test@xn--franois-xxa.ch'));
		$this->assertFalse($this->mailer->addTO('test@xn--franois-xxa.ch'));
		$this->assertFalse($this->mailer->addTO('test@XN--FRANOIS-XXA.CH'));

		$this->assertTrue($this->mailer->addReplyTo('test+replyto@françois.ch'));
		$this->assertFalse($this->mailer->addReplyTo('test+replyto@françois.ch'));
		$this->assertTrue($this->mailer->addReplyTo('test+replyto@FRANÇOIS.CH'));
		$this->assertFalse($this->mailer->addReplyTo('test+replyto@FRANÇOIS.CH'));
		$this->assertTrue($this->mailer->addReplyTo('test+replyto@xn--franois-xxa.ch'));
		$this->assertFalse($this->mailer->addReplyTo('test+replyto@xn--franois-xxa.ch'));
		$this->assertFalse($this->mailer->addReplyTo('test+replyto@XN--FRANOIS-XXA.CH'));

		$this->mailer->setMessageBody('IDN duplicate remove');
		$this->mailer->preSend();

		// There should be only one "To" address and one "Reply-To" address.
		$this->assertCount(1, $this->mailer->getTOs(), 'Bad count of "to" recipients');
		$this->assertCount(1, $this->mailer->getReplyTos(), 'Bad count of "reply-to" addresses');
	}

	/**
	 * Test address escaping.
	 */
	public function testAddressEscaping(){
		$this->mailer->setSubject('Address escaping');
		$this->mailer->clearTOs();
		$this->mailer->addTO('foo@example.com', 'Tim "The Book" O\'Reilly');
		$this->mailer->setMessageBody($this->buildBody('Test correct escaping of quotes in addresses.'));

		$this->mailer->preSend();
		$b = $this->mailer->getSentMIMEMessage();
		$this->assertStringContainsString('To: "Tim \"The Book\" O\'Reilly" <foo@example.com>', $b);

		$this->mailer->setSubject('Address escaping invalid');
		$this->mailer->clearTOs();
		$this->mailer->addTO('foo@example.com', 'Tim "The Book" O\'Reilly');
		$this->mailer->addTO('invalidaddressexample.com', 'invalidaddress');
		$this->mailer->setMessageBody($this->buildBody('invalid address'));

		$this->mailer->preSend();

		$this->mailer->addAttachment(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer_mini.png'), 'phpmailer_mini.png');
		$this->assertTrue($this->mailer->attachmentExists());
	}

	/**
	 * Encoding and charset tests.
	 */
	public function testEncodings(){
		$this->assertSame($this->mailer->encodeString('hello', PHPMailer::ENCODING_BINARY), 'hello', 'Binary encoding changed input');
	}

	public function testEncodingException(){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Unknown encoding: asdfghjkl');

		$this->mailer->encodeString('hello', 'asdfghjkl');
	}

	/**
	 * DKIM copied header fields tests.
	 *
	 * @group dkim
	 *
	 * @see   https://tools.ietf.org/html/rfc6376#section-3.5
	 */
	public function testDKIMOptionalHeaderFieldsCopy(){
		$privatekeyfile = 'dkim_private.pem';
		$pk             = openssl_pkey_new([
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		]);

		openssl_pkey_export_to_file($pk, $privatekeyfile);

		//Example from https://tools.ietf.org/html/rfc6376#section-3.5
		$from    = 'from@example.com';
		$to      = 'to@example.com';
		$date    = 'date';
		$subject = 'example';
		$le      = $this->mailer->getLE();

		$headerLines      = 'From:'.$from.$le.'To:'.$to.$le.'Date:'.$date.$le;
		$copyHeaderFields = ' z=From:'.$from.$le.' |To:'.$to.$le.' |Date:'.$date.$le.' |Subject:'.$subject.';'.$le;

		$this->options->DKIM_domain      = 'example.com';
		$this->options->DKIM_selector    = 'phpmailer';
		$this->options->DKIM_key         = 'dkim_private.pem';
		$this->options->DKIM_copyHeaders = true;
		$this->options->DKIM_sign        = true;

		$this->mailer->setOptions($this->options);

		$this->assertStringContainsString(
			$copyHeaderFields,
			$this->mailer->DKIM_Add($headerLines, $subject, ''),
			'DKIM header with copied header fields incorrect'
		);

		$this->options->DKIM_copyHeaders = false;

		$this->mailer->setOptions($this->options);

		$this->assertStringNotContainsString(
			$copyHeaderFields,
			$this->mailer->DKIM_Add($headerLines, $subject, ''),
			'DKIM header without copied header fields incorrect'
		);

		unlink($privatekeyfile);
	}

	/**
	 * DKIM signing extra headers tests.
	 *
	 * @group dkim
	 */
	public function testDKIMExtraHeaders(){
		$privatekeyfile = 'dkim_private.pem';
		$pk             = openssl_pkey_new([
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		]);

		openssl_pkey_export_to_file($pk, $privatekeyfile);

		$this->options->DKIM_domain   = 'example.com';
		$this->options->DKIM_selector = 'phpmailer';
		$this->options->DKIM_key      = 'dkim_private.pem';
		$this->options->DKIM_headers  = ['Baz', 'List-Unsubscribe'];
		$this->options->DKIM_sign     = true;

		$this->mailer->setOptions($this->options);

		//Example from https://tools.ietf.org/html/rfc6376#section-3.5
		$from           = 'from@example.com';
		$to             = 'to@example.com';
		$date           = 'date';
		$subject        = 'example';
		$anyHeader      = 'foo';
		$unsubscribeUrl = '<https://www.example.com/unsubscribe/?newsletterId=anytoken&amp;actionToken=anyToken'.
		                  '&otherParam=otherValue&anotherParam=anotherVeryVeryVeryLongValue>';

		$this->mailer->addCustomHeader('X-AnyHeader', $anyHeader);
		$this->mailer->addCustomHeader('Baz', 'bar');
		$this->mailer->addCustomHeader('List-Unsubscribe', $unsubscribeUrl);

		$headerLines = "From:$from\r\nTo:$to\r\nDate:$date\r\n";
		$headerLines .= "X-AnyHeader:$anyHeader\r\nBaz:bar\r\n";
		$headerLines .= 'List-Unsubscribe:'.$this->mailer->encodeHeader($unsubscribeUrl)."\r\n";

		$headerFields = 'h=From:To:Date:Baz:List-Unsubscribe:Subject';

		$result = $this->mailer->DKIM_Add($headerLines, $subject, '');

		$this->assertStringContainsString($headerFields, $result, 'DKIM header with extra headers incorrect');

		unlink($privatekeyfile);
	}

	/**
	 * Tests the Custom header getter.
	 */
	public function testCustomHeaderGetter(){
		$this->mailer->addCustomHeader('foo', 'bar');
		$this->assertSame([['foo', 'bar']], $this->mailer->getCustomHeaders());

		$this->mailer->addCustomHeader('foo', 'baz');
		$this->assertSame([['foo', 'bar'], ['foo', 'baz']], $this->mailer->getCustomHeaders());

		$this->mailer->clearCustomHeaders();
		$this->assertEmpty($this->mailer->getCustomHeaders());

		$this->mailer->addCustomHeader('yux');
		$this->assertSame([['yux']], $this->mailer->getCustomHeaders());

		$this->mailer->addCustomHeader('Content-Type: application/json');
		$this->assertSame([['yux'], ['Content-Type', ' application/json']], $this->mailer->getCustomHeaders());
	}

	/**
	 * @noinspection HtmlUnknownTarget
	 * @noinspection HtmlRequiredAltAttribute
	 */
	public function testMessageFromHTMLIgnorePaths(){
		// Test that local paths without a basedir are ignored
		$this->mailer->messageFromHTML('<img src="/etc/hostname">test');
		$this->assertStringContainsString('src="/etc/hostname"', $this->getPropertyValue('body'));

		// Test that local paths with a basedir are not ignored
		$this->mailer->messageFromHTML('<img src="composer.json">test', realpath($this->INCLUDE_DIR));
		$this->assertStringNotContainsString('src="composer.json"', $this->getPropertyValue('body'));

		// Test that local paths with parent traversal are ignored
		$this->mailer->messageFromHTML('<img src="../composer.json">test', realpath($this->INCLUDE_DIR));
		$this->assertStringNotContainsString('src="composer.json"', $this->getPropertyValue('body'));

		// Test that existing embedded URLs are ignored
		$this->mailer->messageFromHTML('<img src="cid:5d41402abc4b2a76b9719d911017c592">test');
		$this->assertStringContainsString('src="cid:5d41402abc4b2a76b9719d911017c592"', $this->getPropertyValue('body'));

		// Test that absolute URLs are ignored
		$this->mailer->messageFromHTML('<img src="https://github.com/PHPMailer/PHPMailer/blob/master/composer.json">test');
		$this->assertStringContainsString('src="https://github.com/PHPMailer/PHPMailer/blob/master/composer.json"', $this->getPropertyValue('body'));

		// Test that absolute URLs with anonymous/relative protocol are ignored
		// Note that such URLs will not work in email anyway because they have no protocol to be relative to
		$this->mailer->messageFromHTML('<img src="//github.com/PHPMailer/PHPMailer/blob/master/composer.json">test');
		$this->assertStringContainsString('src="//github.com/PHPMailer/PHPMailer/blob/master/composer.json"', $this->getPropertyValue('body'));
	}

	public function testQuotedPrintableEncode(){
		//Check that a quoted printable encode and decode results in the same as went in
		$t = file_get_contents(__FILE__); //Use this file as test content
		//Force line breaks to UNIX-style
		$t = str_replace(["\r\n", "\r"], "\n", $t);
		$this->assertSame(
			$t,
			quoted_printable_decode($this->mailer->encodeString($t, $this->mailer::ENCODING_QUOTED_PRINTABLE)),
			'Quoted-Printable encoding round-trip failed'
		);
		//Force line breaks to Windows-style
		// @todo: test disabled as the default line ending depends on the mailer ($this->LE)
#		$t = str_replace("\n", "\r\n", $t);
#		$this->assertSame(
#			$t,
#			quoted_printable_decode($this->mailer->encodeString($t, $this->mailer::ENCODING_QUOTED_PRINTABLE)),
#			'Quoted-Printable encoding round-trip failed (Windows line breaks)'
#		);
	}

	public function testHTMLEmbeddedImageInvalidFileException(){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Could not access file: thisfiledoesntexist');

		$this->mailer->addEmbeddedImage('thisfiledoesntexist', 'xyz'); //Non-existent file
	}

	public function testAddEmbeddedImageInvalidEncoding(){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Unknown encoding: whatever');

		$this->mailer->addEmbeddedImage(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'), 'foo', 'bar', 'whatever');
	}

	public function testAddStringAttachmentInvalidEncoding(){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Unknown encoding: whatever');

		$this->mailer->addStringAttachment('foo', 'string_attach.txt', 'whatever');
	}

	public function testAddStringEmbeddedImageInvalidEncoding(){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Unknown encoding: whatever');

		$this->mailer->addStringEmbeddedImage(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'), 'foo', 'bar', 'whatever');
	}

	public function testLinebreakNormalization(){
		$eol = $this->mailer->getLE();

		$target = "hello{$eol}World{$eol}Again{$eol}";
		$this->assertSame($target, normalizeBreaks("hello\nWorld\nAgain\n", $eol), 'UNIX break reformatting failed');
		$this->assertSame($target, normalizeBreaks("hello\rWorld\rAgain\r", $eol), 'Mac break reformatting failed');
		$this->assertSame($target, normalizeBreaks("hello\r\nWorld\r\nAgain\r\n", $eol), 'Windows break reformatting failed');
		$this->assertSame($target, normalizeBreaks("hello\nWorld\rAgain\r\n", $eol), 'Mixed break reformatting failed');
	}

	/**
	 * Test setting and retrieving message ID.
	 */
	public function testMessageID(){
		$this->mailer->addTO('user@example.com');

		$id = hash('sha256', 12345);
		$this->mailer->setMessageBody('Test message ID.');
		$this->mailer->setMessageID($id);
		$this->mailer->setMessageBody($this->buildBody(''));
		$this->mailer->preSend();

		$this->assertNotSame($this->mailer->getLastMessageID(), $id, 'Invalid Message ID allowed');

		$id = '<'.hash('sha256', 12345).'@example.com>';
		$this->mailer->setMessageID($id);
		$this->mailer->setMessageBody($this->buildBody(''));
		$this->mailer->preSend();
		$this->assertSame( $this->mailer->getLastMessageID(), $id, 'Custom Message ID not used');

		$this->mailer->setMessageID('');
		$this->mailer->setMessageBody($this->buildBody(''));
		$this->mailer->preSend();
		$this->assertRegExp('/^<.*@.*>$/', $this->mailer->getLastMessageID(), 'Invalid default Message ID');
	}

	/**
	 * Test MIME structure assembly.
	 */
	public function testMIMEStructure(){
		$this->markTestIncomplete('why is this acting out? test needs a fix. (original test doesn\'t with the same source)');


		$this->setMessage('<h3>MIME structure test.</h3>', __FUNCTION__.': MIME structure');
		$this->mailer->setAltBody('MIME structure test.');

		$this->mailer->preSend();
		$this->assertRegExp(
			"/Content-Transfer-Encoding: 8bit\r\n\r\n".
			'This is a multi-part message in MIME format./',
			$this->mailer->getSentMIMEMessage(),
			'MIME structure broken'
		);
	}

	/**
	 * Tests CharSet and Unicode -> ASCII conversions for addresses with IDN.
	 */
	public function testAddressConvertEncoding(){
		$this->options->charSet = $this->mailer::CHARSET_ISO88591;
		$this->mailer->setOptions($this->options);

		// This file is UTF-8 encoded. Create a domain encoded in "iso-8859-1".
		$domain = '@'.mb_convert_encoding('françois.ch', 'ISO-8859-1', 'UTF-8');

		$this->mailer->addTO('test'.$domain);
		$this->mailer->addCC('test+cc'.$domain);
		$this->mailer->addBCC('test+bcc'.$domain);
		$this->mailer->addReplyTo('test+replyto'.$domain);

		// Queued addresses are not returned by get*Addresses() before send() call.
		$this->assertEmpty($this->mailer->getTOs(), 'Bad "to" recipients');
		$this->assertEmpty($this->mailer->getCCs(), 'Bad "cc" recipients');
		$this->assertEmpty($this->mailer->getBCCs(), 'Bad "bcc" recipients');
		$this->assertEmpty($this->mailer->getReplyTos(), 'Bad "reply-to" recipients');

		// Clear queued BCC recipient.
		$this->mailer->clearBCCs();

		$this->mailer->setMessageBody('address convert encoding test');
		$this->mailer->preSend();

		// Addresses with IDN are returned by get*Addresses() after send() call.
		$domain = punyencodeAddress($domain);

		$this->assertSame([['test'.$domain, '']], $this->mailer->getTOs(), 'Bad "to" recipients');
		$this->assertSame([['test+cc'.$domain, '']], $this->mailer->getCCs(), 'Bad "cc" recipients');
		$this->assertEmpty($this->mailer->getBCCs(), 'Bad "bcc" recipients');
		$this->assertSame(['test+replyto'.$domain => ['test+replyto'.$domain, '']], $this->mailer->getReplyTos(), 'Bad "reply-to" addresses');
	}

}
