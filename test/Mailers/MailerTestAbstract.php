<?php
/**
 * Class MailerTestAbstract
 *
 * @filesource   MailerTestAbstract.php
 * @created      11.04.2019
 * @package      PHPMailer\Test\Mailers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test\Mailers;

use PHPMailer\PHPMailer\PHPMailerException;
use PHPMailer\Test\TestAbstract;

abstract class MailerTestAbstract extends TestAbstract{

	protected function setUp():void{
		parent::setUp();

		$this->setupMailer();
	}

	protected function tearDown():void{
		$this->logger->info(str_repeat('-', 40));

		parent::tearDown();
	}

	/**
	 * Test this denial of service attack.
	 *
	 * @see http://www.cybsec.com/vuln/PHPMailer-DOS.pdf
	 */
	public function testDoSInfiniteLoopHeaderExceed998(){
		$this
			->setMessage(
				'This should no longer cause a denial of service.',
				__FUNCTION__.' '.substr(str_repeat('0123456789', 100), 0, 998)
			)
			->assertSentMail();
	}

	/**
	 * Test sending an empty body.
	 *
	 * @todo:
	 * /usr/sbin/sendmail: Zeile 11: /tmp/fakemail/num: Keine Berechtigung
	 * cat: /tmp/fakemail/num: Keine Berechtigung
	 * /usr/sbin/sendmail: Zeile 15: /tmp/fakemail/num: Keine Berechtigung
	 * /usr/sbin/sendmail: Zeile 20: /tmp/fakemail/message_1.eml: Keine Berechtigung
	 */
	public function testEmptyBody(){
		$this->mailer->addTO('user@example.com');
		$this->mailer->Body    = '';
		$this->mailer->Subject = $this->mailer->getMailer().': '.__FUNCTION__;
		$this->mailer->AllowEmpty = true;

		$this->assertSentMail();
	}

	public function testEmptyBodynotAllowedException(){
		$this->expectException(PHPMailerException::class);
		$this->expectExceptionMessage('Message body empty');

		$this->mailer->addTO('user@example.com');
		$this->mailer->Body = '';
		$this->mailer->AllowEmpty = false;
		$this->mailer->send();
	}

	/**
	 * Test low priority.
	 */
	public function testLowPriority(){
		$this->mailer->Priority = 5;
		$this->mailer->addReplyTo('nobody@nobody.com', 'Nobody (Unit Test)');

		$body = 'Here is the main body. There should be a reply to address in this message.';

		$this
			->setMessage($body, __FUNCTION__)
			// $assertFunc example
			->assertSentMail(function(string $sent, array $received){
				$this->assertStringContainsString('X-Priority: 5', $received[0]);
			})
		;
	}

	/**
	 * Word-wrap an ASCII message.
	 */
	public function testWordWrap(){
		$this->mailer->WordWrap = 40;

		$body = str_repeat(
			'Here is the main body of this message.  It should '.
			'be quite a few lines.  It should be wrapped at '.
			'40 characters.  Make sure that it is. ',
			10
		);

		$this->setMessage($body, __FUNCTION__)->assertSentMail();
	}


	/**
	 * Word-wrap a multibyte message.
	 *
	 * @todo: text is being wrapped to 20 chars instead of 40 (multibyte!)
	 */
	public function testWordWrapMultibyte(){
		$this->mailer->WordWrap = 40;

		$body = str_repeat(
			'飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 '.
			'飛飛兒樂 團光茫兒樂 團光茫飛兒樂 團光飛兒樂 團光茫飛兒樂 團光茫兒樂 團光茫 '.
			'飛兒樂 團光茫飛兒樂 團飛兒樂 團光茫光茫飛兒樂 團光茫. ',
			10
		);

		$this->setMessage($body, __FUNCTION__)->assertSentMail();
	}

	/**
	 * Simple plain string attachment test.
	 */
	public function testPlainStringAttachment(){
		$attachment = 'These characters are the content of the string attachment.'."\n".
		              'This might be taken from a database or some other such thing.';

		$this->mailer->addStringAttachment($attachment, 'string_attach.txt');

		$this->setMessage('Here is the text body', __FUNCTION__)->assertSentMail();

	}

	/**
	 * Send a message containing multilingual UTF-8 text.
	 */
	public function testPlainUtf8(){
		$this->mailer->CharSet = $this->mailer::CHARSET_UTF8;

		$body = <<<'EOT'
Chinese text: 郵件內容為空
Russian text: Пустое тело сообщения
Armenian text: Հաղորդագրությունը դատարկ է
Czech text: Prázdné tělo zprávy
EOT;
		$this->setMessage($body, __FUNCTION__)->assertSentMail();
		$msg = $this->mailer->getSentMIMEMessage();
		$this->assertStringNotContainsString("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
	}

	/**
	 * Simple plain file attachment test.
	 */
	public function testMultiplePlainFileAttachment(){
		$this->mailer->addAttachment(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'));
		$this->mailer->addAttachment($this->INCLUDE_DIR.'/README.md', 'test.txt');

		$this->setMessage('Here is the text body', __FUNCTION__)->assertSentMail();
	}

	/**
	 * Simple HTML and attachment test.
	 */
	public function testHTMLAttachment(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;

		$this->mailer->addAttachment(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer_mini.png'), 'phpmailer_mini.png');

		$this->setMessage('This is the <strong>HTML</strong> part of the email.', __FUNCTION__)->assertSentMail();
	}

	/**
	 * Simple HTML and multiple attachment test.
	 */
	public function testHTMLMultiAttachment(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;

		$this->mailer->addAttachment(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer_mini.png'), 'phpmailer_mini.png');
		$this->mailer->addAttachment(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'), 'phpmailer.png');

		$this->setMessage('This is the <strong>HTML</strong> part of the email.', __FUNCTION__)->assertSentMail();
	}

	/**
	 * Simple HTML and attachment test.
	 */
	public function testAltBodyAttachment(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;
		$this->mailer->AltBody     = 'This is the text part of the email.';

		$this->mailer->addAttachment($this->INCLUDE_DIR.'/README.md','test.txt');

		$this->setMessage('This is the <strong>HTML</strong> part of the email.', __FUNCTION__)->assertSentMail();
	}

	/**
	 * @todo: what is the purpose of this test? the mail is being sent with a non-accessible attachment?
	 *
	 * Test embedded image without a name.
	 */
	public function testHTMLStringEmbedNoName(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;

		$this->mailer->addStringEmbeddedImage(
			file_get_contents(realpath($this->INCLUDE_DIR.'/examples/images/phpmailer_mini.png')),
			hash('sha256', 'phpmailer_mini.png').'@phpmailer.0',
			'', //Intentionally empty name
			'base64',
			'', //Intentionally empty MIME type
			'inline'
		);

		$this->setMessage('This is the <strong>HTML</strong> part of the email.', __FUNCTION__)->assertSentMail();
	}

	/**
	 * An embedded attachment test.
	 */
	public function testHTMLEmbeddedImage(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;

		$this->mailer->addEmbeddedImage(
			realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'),
			'my-attach',
			'phpmailer.png',
			'base64',
			'image/png'
		);

		$body = 'Embedded Image: <img alt="phpmailer" src="cid:my-attach"> Here is an image!';

		$this->setMessage($body, __FUNCTION__)->assertSentMail();
	}

	/**
	 * An embedded attachment test.
	 */
	public function testHTMLMultiEmbeddedImage(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;

		$this->mailer->addEmbeddedImage(
			realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'),
			'my-attach',
			'phpmailer.png',
			'base64',
			'image/png'
		);

		$this->mailer->addAttachment($this->INCLUDE_DIR.'/README.md','test.txt');

		$body = 'Embedded Image: <img alt="phpmailer" src="cid:my-attach"> Here is an image!';

		$this->setMessage($body, __FUNCTION__)->assertSentMail();
	}

	/**
	 * Send an HTML message.
	 */
	public function testHtml(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;

		$body = <<<'EOT'
<html>
    <head>
        <title>HTML email test</title>
    </head>
    <body>
        <h1>PHPMailer does HTML!</h1>
        <p>This is a <strong>test message</strong> written in HTML.<br>
        Go to <a href="https://github.com/PHPMailer/PHPMailer/">https://github.com/PHPMailer/PHPMailer/</a>
        for new versions of PHPMailer.</p>
        <p>Thank you!</p>
    </body>
</html>
EOT;
		$this->setMessage($body, __FUNCTION__)->assertSentMail();

		$msg = $this->mailer->getSentMIMEMessage();
		$this->assertStringNotContainsString("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
	}

	/**
	 * Send a message containing ISO-8859-1 text.
	 */
	public function testHtmlIso8859(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;
		$this->mailer->CharSet = $this->mailer::CHARSET_ISO88591;

		//This file is in ISO-8859-1 charset
		//Needs to be external because this file is in UTF-8
		$content = file_get_contents(realpath($this->INCLUDE_DIR.'/examples/contents.html'));
		// This is the string 'éèîüçÅñæß' in ISO-8859-1, base-64 encoded
		$check = base64_decode('6eju/OfF8ebf');
		//Make sure it really is in ISO-8859-1!
		$this->mailer->messageFromHTML(
			mb_convert_encoding(
				$content,
				'ISO-8859-1',
				mb_detect_encoding($content, 'UTF-8, ISO-8859-1, ISO-8859-15', true)
			),
			realpath($this->INCLUDE_DIR.'/examples')
		);

		$this->setMessage($this->mailer->Body, __FUNCTION__);
		$this->assertStringContainsString($check, $this->mailer->Body, 'ISO message body does not contain expected text');
		$this->assertSentMail();
	}

	/**
	 * Send a message containing multilingual UTF-8 text.
	 */
	public function testHtmlUtf8(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;
		$this->mailer->CharSet = $this->mailer::CHARSET_UTF8;

		$body = <<<'EOT'
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>HTML email test</title>
    </head>
    <body>
        <p>Chinese text: 郵件內容為空</p>
        <p>Russian text: Пустое тело сообщения</p>
        <p>Armenian text: Հաղորդագրությունը դատարկ է</p>
        <p>Czech text: Prázdné tělo zprávy</p>
    </body>
</html>
EOT;
		$this->setMessage($body, __FUNCTION__)->assertSentMail();
		$msg = $this->mailer->getSentMIMEMessage();
		$this->assertStringNotContainsString("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
	}

	/**
	 * Send a message containing multilingual UTF-8 text with an embedded image.
	 */
	public function testHtmlUtf8WithEmbeddedImage(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;
		$this->mailer->CharSet = $this->mailer::CHARSET_UTF8;

		$body = <<<'EOT'
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>HTML email test</title>
    </head>
    <body>
        <p>Chinese text: 郵件內容為空</p>
        <p>Russian text: Пустое тело сообщения</p>
        <p>Armenian text: Հաղորդագրությունը դատարկ է</p>
        <p>Czech text: Prázdné tělo zprávy</p>
        Embedded Image: <img alt="phpmailer" src="cid:my-attach">
    </body>
</html>
EOT;
		$this->mailer->addEmbeddedImage(
			realpath($this->INCLUDE_DIR.'/examples/images/phpmailer.png'),
			'my-attach',
			'phpmailer.png',
			'base64',
			'image/png'
		);

		$this->setMessage($body, __FUNCTION__)->assertSentMail();
	}

	/**
	 * Test simple message builder
	 */
	public function testMessageFromHTML(){
		$this->mailer->CharSet = $this->mailer::CHARSET_UTF8;

		//Uses internal HTML to text conversion
		$this->mailer->messageFromHTML(
			file_get_contents(realpath($this->INCLUDE_DIR.'/examples/contentsutf8.html')),
			realpath($this->INCLUDE_DIR.'/examples')
		);

		$this->assertNotEmpty($this->mailer->Body, 'Body not set by messageFromHTML');
		$this->assertNotEmpty($this->mailer->AltBody, 'AltBody not set by messageFromHTML');

		$this->setMessage($this->mailer->Body, __FUNCTION__)->assertSentMail();
	}

	/**
	 * Test custom html2text converter
	 */
	public function testMessageFromHTMLWithCustomConverter(){
		$this->mailer->CharSet = $this->mailer::CHARSET_UTF8;

		//Again, using a custom HTML to text converter
		$this->mailer->messageFromHTML(
			file_get_contents(realpath($this->INCLUDE_DIR.'/examples/contentsutf8.html')),
			realpath($this->INCLUDE_DIR.'/examples'),
			function($html){
				return strtoupper(strip_tags($html));
			}
		);

		$this->assertNotEmpty($this->mailer->Body, 'Body not set by messageFromHTML');
		$this->assertNotEmpty($this->mailer->AltBody, 'AltBody not set by messageFromHTML');

		$this->setMessage($this->mailer->Body, __FUNCTION__)->assertSentMail();
	}

	/**
	 * Simple multipart/alternative test.
	 */
	public function testAltBody(){
		$this->mailer->AltBody  = 'Here is the plain text body of this message. '.
		                        'It should be quite a few lines. It should be wrapped at '.
		                        '40 characters.  Make sure that it is.';
		$this->mailer->WordWrap = 40;
		$this->addNote('This is a multipart/alternative email');
		$this->mailer->Subject .= ': AltBody + Word Wrap';

		$this->setMessage('This is the <strong>HTML</strong> part of the email.', __FUNCTION__)->assertSentMail();
	}

	/**
	 * Plain quoted-printable message.
	 */
	public function testQuotedPrintable(){
		$this->mailer->Encoding = $this->mailer::ENCODING_QUOTED_PRINTABLE;

		$body     = 'Hätten Hüte ein ß im Namen, wären sie Hüße.';
		$expected = 'H=C3=A4tten H=C3=BCte ein =C3=9F im Namen, w=C3=A4ren sie H=C3=BC=C3=9Fe.';

		$this->setMessage($body, __FUNCTION__)->assertSentMail();

		$msg = $this->mailer->getSentMIMEMessage();
		$this->assertStringContainsString($expected, $msg);
	}

	/**
	 * Send an HTML message specifiying the DSN notifications we expect.
	 */
	public function testDsn(){
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_TEXT_HTML;

		$body = <<<'EOT'
<html>
    <head>
        <title>HTML email test</title>
    </head>
    <body>
        <p>PHPMailer</p>
    </body>
</html>
EOT;

		$this->setMessage($body, __FUNCTION__.' DSN: SUCCESS,FAILURE');

		$this->mailer->dsn = 'SUCCESS,FAILURE';
		$this->assertSentMail();

		$this->setMessage($body, __FUNCTION__.' DSN: NEVER');
		//Sends the same mail, but sets the DSN notification to NEVER
		$this->mailer->dsn = 'NEVER';
		$this->assertSentMail();
	}

	/**
	 * Test BCC-only addressing.
	 *
	 * @group network
	 */
	public function testBCCAddressing(){
		$this->setMessage('BCC only addressing', __FUNCTION__);

		$this->mailer->clearAllRecipients();
		$this->mailer->addTO('foo@example.com', 'Foo');
		$this->mailer->preSend();
		$b = $this->mailer->getSentMIMEMessage();
		$this->assertTrue($this->mailer->addBCC('a@example.com'), 'BCC addressing failed');
		$this->assertStringContainsString('To: Foo <foo@example.com>', $b);

		$this->assertSentMail();
	}

	/**
	 * Tests setting and retrieving ConfirmReadingTo address, also known as "read receipt" address.
	 *
	 * @group network
	 */
	public function testConfirmReadingTo(){
		$this->mailer->CharSet = $this->mailer::CHARSET_UTF8;

		$this->setMessage('test confirm reading', __FUNCTION__.': Extra space to trim');

		$this->mailer->ConfirmReadingTo = ' test@example.com';

		$this->assertSentMail(function(string $sent, array $received){
			$this->assertSame('test@example.com', $this->mailer->ConfirmReadingTo, 'Unexpected read receipt address');
			$this->assertStringContainsString('Disposition-Notification-To: <test@example.com>', $received[0]);
		});

		$this->setMessage('test confirm reading', __FUNCTION__.': Address with IDN');

		$this->mailer->ConfirmReadingTo = 'test@françois.ch';

		$this->assertSentMail(function(string $sent, array $received){
			$this->assertSame('test@xn--franois-xxa.ch', $this->mailer->ConfirmReadingTo, 'IDN address not converted to punycode');
			$this->assertStringContainsString('Disposition-Notification-To: <test@xn--franois-xxa.ch>', $received[0]);
		});

	}

	/**
	 * @todo
	 */
	public function testConfirmReadingToException(){
		$this->markTestIncomplete('this is nowhere checked');

		$this->setMessage('test confirm reading exception', __FUNCTION__);

		$this->mailer->ConfirmReadingTo = 'test@example..com';  //Invalid address
		$this->mailer->send();
	}

	/**
	 * Test line break reformatting.
	 */
	public function testLineBreaks(){
		$this->setupMailer();
		$this->mailer->addTO('user@example.com');

		$subject = $this->mailer->getMailer().': '.__FUNCTION__;

		//To see accurate results when using postfix, set `sendmail_fix_line_endings = never` in main.cf
		$this->mailer->Subject = $subject.' DOS line breaks';
		$this->mailer->Body    = "This message\r\ncontains\r\nDOS-format\r\nCRLF line breaks.";
		$this->assertSentMail();

		$this->mailer->Subject = $subject.' UNIX line breaks';
		$this->mailer->Body    = "This message\ncontains\nUNIX-format\nLF line breaks.";
		$this->assertSentMail();

		$this->mailer->Encoding = 'quoted-printable';
		$this->mailer->Subject  = $subject.' DOS line breaks, QP';
		$this->mailer->Body     = "This message\r\ncontains\r\nDOS-format\r\nCRLF line breaks.";
		$this->assertSentMail();

		$this->mailer->Subject = $subject.' UNIX line breaks, QP';
		$this->mailer->Body    = "This message\ncontains\nUNIX-format\nLF line breaks.";
		$this->assertSentMail();
	}

	/**
	 * Test line length detection.
	 */
	public function testLineLength(){
		$oklen  = str_repeat(str_repeat('0', $this->mailer::LINE_LENGTH_MAX)."\r\n", 2);
		$badlen = str_repeat(str_repeat('1', $this->mailer::LINE_LENGTH_MAX + 1)."\r\n", 2);
		$this->assertTrue($this->mailer->hasLineLongerThanMax($badlen), 'Long line not detected (only)');
		$this->assertTrue($this->mailer->hasLineLongerThanMax($oklen.$badlen), 'Long line not detected (first)');
		$this->assertTrue($this->mailer->hasLineLongerThanMax($badlen.$oklen), 'Long line not detected (last)');
		$this->assertTrue(
			$this->mailer->hasLineLongerThanMax($oklen.$badlen.$oklen),
			'Long line not detected (middle)'
		);
		$this->assertFalse($this->mailer->hasLineLongerThanMax($oklen), 'Long line false positive');
		$this->mailer->ContentType = $this->mailer::CONTENT_TYPE_PLAINTEXT;
		$this->mailer->Subject  .= ': Line length test';
		$this->mailer->CharSet  = 'UTF-8';
		$this->mailer->Encoding = '8bit';

		$this->setMessage($oklen.$badlen.$oklen.$badlen, __FUNCTION__)->assertSentMail();

		$this->assertSame('quoted-printable', $this->mailer->Encoding, 'Long line did not override transfer encoding');
	}

	/**
	 * DKIM Signing tests.
	 */
	public function testDKIMSign(){

		$privatekeyfile = 'dkim_private.pem';
		// Make a new key pair
		// 2048 bits is the recommended minimum key length - gmail won't accept less than 1024 bits
		$pk = openssl_pkey_new([
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		]);

		openssl_pkey_export_to_file($pk, $privatekeyfile);

		$this->mailer->setDKIMCredentials('example.com', 'phpmailer', $privatekeyfile);

		$this->setMessage('This message is DKIM signed.', __FUNCTION__)->assertSentMail();
		unlink($privatekeyfile);
	}

	/**
	 * S/MIME Signing tests (self-signed).
	 */
	public function testSMIMESign(){

		$dn = [
			'countryName'            => 'UK',
			'stateOrProvinceName'    => 'Here',
			'localityName'           => 'There',
			'organizationName'       => 'PHP',
			'organizationalUnitName' => 'PHPMailer',
			'commonName'             => 'PHPMailer Test',
			'emailAddress'           => 'phpmailer@example.com',
		];

		$keyconfig = [
			'digest_alg'       => 'sha256',
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		];

		$password  = 'password';
		$certfile  = 'certfile.pem';
		$keyfile   = 'keyfile.pem';

		//Make a new key pair
		$pk = openssl_pkey_new($keyconfig);
		//Create a certificate signing request
		$csr = openssl_csr_new($dn, $pk);
		//Create a self-signed cert
		$cert = openssl_csr_sign($csr, null, $pk, 1);
		//Save the cert
		openssl_x509_export($cert, $certout);
		file_put_contents($certfile, $certout);
		//Save the key
		openssl_pkey_export($pk, $pkeyout, $password);
		file_put_contents($keyfile, $pkeyout);

		$this->mailer->setSignCredentials($certfile, $keyfile, $password);

		$body = 'This message is S/MIME signed.';
		$this->setMessage($body, __FUNCTION__)->assertSentMail();

		$msg = $this->mailer->getSentMIMEMessage();
		$this->assertStringNotContainsString("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');

		unlink($certfile);
		unlink($keyfile);
	}

	/**
	 * S/MIME Signing tests using a CA chain cert.
	 * To test that a generated message is signed correctly, save the message in a file called `signed.eml`
	 * and use openssl along with the certs generated by this script:
	 * `openssl smime -verify -in signed.eml -signer certfile.pem -CAfile cacertfile.pem`.
	 */
	public function testSMIMESignWithCA(){

		$certprops   = [
			'countryName'            => 'UK',
			'stateOrProvinceName'    => 'Here',
			'localityName'           => 'There',
			'organizationName'       => 'PHP',
			'organizationalUnitName' => 'PHPMailer',
			'commonName'             => 'PHPMailer Test',
			'emailAddress'           => 'phpmailer@example.com',
		];

		$cacertprops = [
			'countryName'            => 'UK',
			'stateOrProvinceName'    => 'Here',
			'localityName'           => 'There',
			'organizationName'       => 'PHP',
			'organizationalUnitName' => 'PHPMailer CA',
			'commonName'             => 'PHPMailer Test CA',
			'emailAddress'           => 'phpmailer@example.com',
		];

		$keyconfig   = [
			'digest_alg'       => 'sha256',
			'private_key_bits' => 2048,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		];

		$password    = 'password';
		$cacertfile  = 'cacertfile.pem';
		$cakeyfile   = 'cakeyfile.pem';
		$certfile    = 'certfile.pem';
		$keyfile     = 'keyfile.pem';

		//Create a CA cert
		//Make a new key pair
		$capk = openssl_pkey_new($keyconfig);
		//Create a certificate signing request
		$csr = openssl_csr_new($cacertprops, $capk);
		//Create a self-signed cert
		$cert = openssl_csr_sign($csr, null, $capk, 1);
		//Save the CA cert
		openssl_x509_export($cert, $certout);
		file_put_contents($cacertfile, $certout);
		//Save the CA key
		openssl_pkey_export($capk, $pkeyout, $password);
		file_put_contents($cakeyfile, $pkeyout);

		//Create a cert signed by our CA
		//Make a new key pair
		$pk = openssl_pkey_new($keyconfig);
		//Create a certificate signing request
		$csr = openssl_csr_new($certprops, $pk);
		//Create a self-signed cert
		$cacert = file_get_contents($cacertfile);
		$cert   = openssl_csr_sign($csr, $cacert, $capk, 1);
		//Save the cert
		openssl_x509_export($cert, $certout);
		file_put_contents($certfile, $certout);
		//Save the key
		openssl_pkey_export($pk, $pkeyout, $password);
		file_put_contents($keyfile, $pkeyout);

		$this->mailer->setSignCredentials($certfile, $keyfile, $password, $cacertfile);

		$body = 'This message is S/MIME signed with an extra CA cert.';
		$this->setMessage($body, __FUNCTION__)->assertSentMail();

		$msg = $this->mailer->getSentMIMEMessage();
		$this->assertStringNotContainsString("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');

		unlink($cacertfile);
		unlink($cakeyfile);
		unlink($certfile);
		unlink($keyfile);
	}

}
