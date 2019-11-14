<?php
/**
 * Class FunctionTest
 *
 * @filesource   FunctionTest.php
 * @created      07.04.2019
 * @package      PHPMailer\Test
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

use function PHPMailer\PHPMailer\{
	DKIM_HeaderC, encodeQ, filenameToType, get_mime_type, isValidHost,
	mb_pathinfo, parseAddresses, punyencodeAddress, validateAddress
};
use function chr, mb_convert_encoding, str_repeat, trim;

use const PATHINFO_BASENAME, PATHINFO_DIRNAME;

class FunctionTest extends TestCase{

	public function validAddressProvider1():array{
		// these addresses are valid for all built-in validators
		return [
			['first@example.org'],
			['first.last@example.org'],
			['1234567890123456789012345678901234567890123456789012345678901234@example.org'],
			['first.last@x23456789012345678901234567890123456789012345678901234567890123.example.org'],
			['first.last@123.example.org'],
			['user+mailbox@example.org'],
			['customer/department=shipping@example.org'],
			['$A12345@example.org'],
			['!def!xyz%abc@example.org'],
			['_somename@example.org'],
			['dclo@us.example.com'],
			['peter.piper@example.org'],
			['test@example.org'],
			['TEST@example.org'],
			['1234567890@example.org'],
			['test+test@example.org'],
			['test-test@example.org'],
			['t*est@example.org'],
			['+1~1+@example.org'],
			['{_test_}@example.org'],
			['test.test@example.org'],
			['test@123.123.123.x123'],
			['test@example.example.org'],
			['test@example.example.example.org'],
			['customer/department@example.org'],
			['_Yosemite.Sam@example.org'],
			['~@example.org'],
			['Ima.Fool@example.org'],
			['name.lastname@example.com'],
			['a@example.com'],
			['a-b@example.com'],
			['+@b.c'],
			['+@b.com'],
			['a@b.co-foo.uk'],
			['valid@about.museum'],
			['shaitan@my-domain.thisisminekthx'],
			['user%uucp!path@example.edu'],
			['cdburgess+!#$%&\'*-/=?+_{}|~test@example.com'],
			['test@test.com'],
			['test@xn--example.com'],
			['test@example.com'],
			['first.last@xn--bcher-kva.ch'],
			['first.last@xn--j1ail.xn--p1ai'],
			['first.last@xn--phplst-6va.com'],
		];
	}

	/**
	 * @dataProvider validAddressProvider1
	 *
	 * @param string $address
	 */
	public function testValidAddresses1(string $address){
		foreach(['pcre', 'html5', 'php'] as $validator){
			$this->assertTrue(validateAddress($address, $validator));
		}
	}

	public function validAddressProvider2():array{
		// these addresses are valid for pcre & php validator
		return [
			['"first\"last"@example.org'],
			['"first@last"@example.org'],
			['"first\last"@example.org'],
			['first.last@[12.34.56.78]'],
			['"first\last"@example.org'],
			['"Abc\@def"@example.org'],
			['"Fred\ Bloggs"@example.org'],
			['"Joe.\Blow"@example.org'],
			['"Abc@def"@example.org'],
			['"test.test"@example.org'],
			['test."test"@example.org'],
			['"test@test"@example.org'],
			['test@[123.123.123.123]'],
			['"test\test"@example.org'],
			['"test\blah"@example.org'],
			['"test\blah"@example.org'],
			['"test\"blah"@example.org'],
			['"Austin@Powers"@example.org'],
			['"Ima.Fool"@example.org'],
			['"first"."last"@example.org'],
			['"first".middle."last"@example.org'],
			['"first".last@example.org'],
			['first."last"@example.org'],
			['"first"."middle"."last"@example.org'],
			['"first.middle"."last"@example.org'],
			['"first.middle.last"@example.org'],
			['"first..last"@example.org'],
			['"first\"last"@example.org'],
			['first."mid\dle"."last"@example.org'],
			['aaa@[123.123.123.123]'],
			['"Joe\Blow"@example.org'],
			['first.last@[IPv6:::]'],
			['first.last@[IPv6:::b4]'],
			['first.last@[IPv6:::b3:b4]'],
			['first.last@[IPv6:a1::b4]'],
			['first.last@[IPv6:a1::]'],
			['first.last@[IPv6:a1:a2::]'],
			['first.last@[IPv6:0123:4567:89ab:cdef::]'],
			['first.last@[IPv6:0123:4567:89ab:CDEF::]'],
			['first.last@[IPv6:::a3:a4:b1:ffff:11.22.33.44]'],
			['first.last@[IPv6:a1:a2:a3:a4::11.22.33.44]'],
			['first.last@[IPv6:a1::11.22.33.44]'],
			['first.last@[IPv6:a1:a2::11.22.33.44]'],
			['first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.44]'],
			['first.last@[IPv6:0123:4567:89ab:CDEF::11.22.33.44]'],
			['first.last@[IPv6:a1::b2:11.22.33.44]'],
			['first.last@[IPv6:::12.34.56.78]'],
			['first.last@[IPv6:1111:2222:3333::4444:12.34.56.78]'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.56.78]'],
			['first.last@[IPv6:::1111:2222:3333:4444:5555:6666]'],
			['first.last@[IPv6:1111:2222:3333::4444:5555:6666]'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:6666::]'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888]'],
		];
	}

	/**
	 * @dataProvider validAddressProvider2
	 *
	 * @param string $address
	 */
	public function testValidAddresses2(string $address){
		foreach(['php'] as $validator){
			$this->assertTrue(validateAddress($address, $validator));
		}
	}

	public function validAddressProvider3():array{
		// These are invalid according to PHP's filter_var
		// which doesn't allow dotless domains, numeric TLDs or unbracketed IPv4 literals
		return [
			['a@b'],
			['a@bar'],
			['first.last@com'],
			['test@123.123.123.123'],
			['foobar@192.168.0.1'],
			['first.last@example.123'],
			// these are not valid for "html5"
			['first.last@[IPv6:::a2:a3:a4:b1:b2:b3:b4]'],
			['first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3::]'],
			['first.last@[IPv6:a1:a2:a3:a4:b1::11.22.33.44]'],
			['first.last@[IPv6:::a2:a3:a4:b1:ffff:11.22.33.44]'],
			['first.last@[IPv6:1111:2222:3333::4444:5555:12.34.56.78]'],
			['first.last@[IPv6:1111:2222:3333::4444:5555:6666:7777]'],
			//Valid RFC 5322 addresses using quoting and comments
			//Note that these are *not* all valid for RFC5321
			['HM2Kinsists@(that comments are allowed)this.is.ok'],
			['"Doug \"Ace\" L."@example.org'],
			['"[[ test ]]"@example.org'],
			['"Ima Fool"@example.org'],
			['"test blah"@example.org'],
			['(foo)cal(bar)@(baz)example.com(quux)'],
			['cal@example(woo).(yay)com'],
			['cal(woo(yay)hoopla)@example.com'],
			['cal(foo\@bar)@example.com'],
			['cal(foo\)bar)@example.com'],
			['first().last@example.org'],
			['pete(his account)@silly.test(his host)'],
			['c@(Chris\'s host.)public.example'],
			['jdoe@machine(comment). example'],
			['1234 @ local(blah) .machine .example'],
			['first(abc.def).last@example.org'],
			['first(a"bc.def).last@example.org'],
			['first.(")middle.last(")@example.org'],
			['first(abc\(def)@example.org'],
			['first.last@x(1234567890123456789012345678901234567890123456789012345678901234567890).com'],
			['a(a(b(c)d(e(f))g)h(i)j)@example.org'],
			['"hello my name is"@example.com'],
			['"Test \"Fail\" Ing"@example.org'],
			['first.last @example.org'],
		];
	}

	/**
	 * @dataProvider validAddressProvider3
	 *
	 * @param string $address
	 */
	public function testValidAddresses3(string $address){
		$this->assertTrue(validateAddress($address, 'pcre'));
	}

	public function invalidAddressProvider1():array{
		return [
			['first.last@sub.do,com'],
			['first\@last@iana.org'],
			['123456789012345678901234567890123456789012345678901234567890@12345678901234567890123456789012345678901234 [...]'],
			['first.last'],
			['"first"last"@iana.org'],
			['"""@iana.org'],
			['"\"@iana.org'],
			['first\@last@iana.org'],
			['first.last@'],
			['x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23 [...]'],
			['first.last@[.12.34.56.78]'],
			['first.last@[12.34.56.789]'],
			['first.last@[::12.34.56.78]'],
			['first.last@[IPv5:::12.34.56.78]'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:12.34.56.78]'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777]'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]'],
			['first.last@[IPv6:1111:2222::3333::4444:5555:6666]'],
			['first.last@[IPv6:1111:2222:333x::4444:5555]'],
			['first.last@[IPv6:1111:2222:33333::4444:5555]'],
			['first.last@-xample.com'],
			['first.last@exampl-.com'],
			['first.last@x234567890123456789012345678901234567890123456789012345678901234.iana.org'],
			['abc\@def@iana.org'],
			['abc\@iana.org'],
			['Doug\ \"Ace\"\ Lovell@iana.org'],
			['abc@def@iana.org'],
			['abc\@def@iana.org'],
			['abc\@iana.org'],
			['@iana.org'],
			['doug@'],
			['"qu@iana.org'],
			['ote"@iana.org'],
			['"Doug "Ace" L."@iana.org'],
			['Doug\ \"Ace\"\ L\.@iana.org'],
			['hello world@iana.org'],
			['gatsby@f.sc.ot.t.f.i.tzg.era.l.d.'],
			['test.iana.org'],
			['test@test@iana.org'],
			['test@@iana.org'],
			['-- test --@iana.org'],
			['[test]@iana.org'],
			['"test"test"@iana.org'],
			['()[]\;:,><@iana.org'],
			['test@.'],
			['test@example.'],
			['test@.org'],
			['test@1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890 [...]'],
			['test@[123.123.123.123'],
			['test@123.123.123.123]'],
			['NotAnEmail'],
			['@NotAnEmail'],
			['"test"blah"@iana.org'],
			['Ima Fool@iana.org'],
			['phil.h\@\@ck@haacked.com'],
			['foo@[\1.2.3.4]'],
			['first\last@iana.org'],
			['Abc\@def@iana.org'],
			['Fred\ Bloggs@iana.org'],
			['Joe.\Blow@iana.org'],
			['first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.567.89]'],
			['{^c\@**Dog^}@cartoon.com'],
			['cal(foo(bar)@iamcal.com'],
			['cal(foo)bar)@iamcal.com'],
			['cal(foo\)@iamcal.com'],
			['first(12345678901234567890123456789012345678901234567890)last@(123456789012345678901234567890123456789012 [...]'],
			['first(middle)last@iana.org'],
			['first(abc("def".ghi).mno)middle(abc("def".ghi).mno).last@(abc("def".ghi).mno)example(abc("def".ghi).mno). [...]'],
			['a(a(b(c)d(e(f))g)(h(i)j)@iana.org'],
			['.@'],
			['@bar.com'],
			['@@bar.com'],
			['aaa.com'],
			['aaa@.com'],
			['aaa@.123'],
			['aaa@[123.123.123.123]a'],
			['aaa@[123.123.123.333]'],
			['a@bar.com.'],
			['a@-b.com'],
			['a@b-.com'],
			['-@..com'],
			['-@a..com'],
			['invalid@about.museum-'],
			['test@...........com'],
			['"Unicode NULL'.chr(0).'"@char.com'],
			['Unicode NULL'.chr(0).'@char.com'],
			['first.last@[IPv6::]'],
			['first.last@[IPv6::::]'],
			['first.last@[IPv6::b4]'],
			['first.last@[IPv6::::b4]'],
			['first.last@[IPv6::b3:b4]'],
			['first.last@[IPv6::::b3:b4]'],
			['first.last@[IPv6:a1:::b4]'],
			['first.last@[IPv6:a1:]'],
			['first.last@[IPv6:a1:::]'],
			['first.last@[IPv6:a1:a2:]'],
			['first.last@[IPv6:a1:a2:::]'],
			['first.last@[IPv6::11.22.33.44]'],
			['first.last@[IPv6::::11.22.33.44]'],
			['first.last@[IPv6:a1:11.22.33.44]'],
			['first.last@[IPv6:a1:::11.22.33.44]'],
			['first.last@[IPv6:a1:a2:::11.22.33.44]'],
			['first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.xx]'],
			['first.last@[IPv6:0123:4567:89ab:CDEFF::11.22.33.44]'],
			['first.last@[IPv6:a1::a4:b1::b4:11.22.33.44]'],
			['first.last@[IPv6:a1::11.22.33]'],
			['first.last@[IPv6:a1::11.22.33.44.55]'],
			['first.last@[IPv6:a1::b211.22.33.44]'],
			['first.last@[IPv6:a1::b2::11.22.33.44]'],
			['first.last@[IPv6:a1::b3:]'],
			['first.last@[IPv6::a2::b4]'],
			['first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3:]'],
			['first.last@[IPv6::a2:a3:a4:b1:b2:b3:b4]'],
			['first.last@[IPv6:a1:a2:a3:a4::b1:b2:b3:b4]'],
			//This is a valid RFC5322 address, but we don't want to allow it for obvious reasons!
			["(\r\n RCPT TO:user@example.com\r\n DATA \\\nSubject: spam10\\\n\r\n Hello,\r\n this is a spam mail.\\\n.\r\n QUIT\r\n ) a@example.net"],
			// IDNs in Unicode and ASCII forms.
			['first.last@bücher.ch'],
			['first.last@кто.рф'],
			['first.last@phplíst.com'],
		];
	}

	/**
	 * @dataProvider invalidAddressProvider1
	 *
	 * @param string $address
	 */
	public function testInvalidAddresses1(string $address){
		foreach(['pcre', 'html5', 'php'] as $validator){
			$this->assertFalse(validateAddress($address, $validator));
		}
	}

	public function invalidAddressProvider2():array{
		return [
			['12345678901234567890123456789012345678901234567890123456789012345@iana.org'],
			['.first.last@iana.org'],
			['first.last.@iana.org'],
			['first..last@iana.org'],
			['.dot@iana.org'],
			['dot.@iana.org'],
			['two..dot@iana.org'],
			['test.@iana.org'],
			['test..test@iana.org'],
			['.test@iana.org'],
			['.wooly@iana.org'],
			['wo..oly@iana.org'],
			['pootietang.@iana.org'],
			['.@iana.org'],
#			['""@iana.org'],
#			['helloworld@iana .org'],
#			['first."".last@iana.org'],
#			['"foo"(yay)@(hoopla)[1.2.3.4]'],
		];
	}

	/**
	 * @dataProvider invalidAddressProvider2
	 *
	 * @param string $address
	 */
	public function testInvalidAddresses2(string $address){
		foreach(['pcre', 'php'] as $validator){
			$this->assertFalse(validateAddress($address, $validator));
		}
	}

	/**
	 * Test RFC822 address splitting.
	 */
	public function testAddressSplitting(){
		//Test built-in address parser
		$this->assertCount(
			2,
			parseAddresses('Joe User <joe@example.com>, Jill User <jill@example.net>'),
			'Failed to recognise address list (IMAP parser)'
		);
		$this->assertSame(
			[
				['name' => 'Joe User', 'address' => 'joe@example.com'],
				['name' => 'Jill User', 'address' => 'jill@example.net'],
				['name' => '', 'address' => 'frank@example.com'],
			],
			parseAddresses(
				'Joe User <joe@example.com>,'
				.'Jill User <jill@example.net>,'
				.'frank@example.com,'
			),
			'Parsed addresses'
		);
		//Test simple address parser
		$this->assertCount(
			2,
			parseAddresses('Joe User <joe@example.com>, Jill User <jill@example.net>', false),
			'Failed to recognise address list'
		);
		//Test single address
		$this->assertNotEmpty(
			parseAddresses('Joe User <joe@example.com>', false),
			'Failed to recognise single address'
		);
		//Test quoted name IMAP
		$this->assertNotEmpty(
			parseAddresses('Tim "The Book" O\'Reilly <foo@example.com>'),
			'Failed to recognise quoted name (IMAP)'
		);
		//Test quoted name
		$this->assertNotEmpty(
			parseAddresses('Tim "The Book" O\'Reilly <foo@example.com>', false),
			'Failed to recognise quoted name'
		);
		//Test single address IMAP
		$this->assertNotEmpty(
			parseAddresses('Joe User <joe@example.com>'),
			'Failed to recognise single address (IMAP)'
		);
		//Test unnamed address
		$this->assertNotEmpty(
			parseAddresses('joe@example.com', false),
			'Failed to recognise unnamed address'
		);
		//Test unnamed address IMAP
		$this->assertNotEmpty(
			parseAddresses('joe@example.com'),
			'Failed to recognise unnamed address (IMAP)'
		);
		//Test invalid addresses
		$this->assertEmpty(
			parseAddresses('Joe User <joe@example.com.>, Jill User <jill.@example.net>'),
			'Failed to recognise invalid addresses (IMAP)'
		);
		//Test invalid addresses
		$this->assertEmpty(
			parseAddresses('Joe User <joe@example.com.>, Jill User <jill.@example.net>', false),
			'Failed to recognise invalid addresses'
		);
	}

	public function validHostProvider():array{
		return [
			['localhost'],
			['example.com'],
			['smtp.gmail.com'],
			['127.0.0.1'],
			[trim(str_repeat('a0123456789.', 21), '.')],
			['[::1]'],
			['[0:1234:dc0:41:216:3eff:fe67:3e01]'],
		];
	}

	/**
	 * @dataProvider validHostProvider
	 *
	 * @param $host
	 */
	public function testHostValidationValid($host){
		$this->assertTrue(isValidHost($host), 'Good hostname denied: '.$host);
	}

	public function invalidHostProvider():array{
		return [
			[123],
			[1.5],
			[''],
			['999.0.0.0'],
			['[1234]'],
			['[1234:::1]'],
			[trim(str_repeat('a0123456789.', 22), '.')],
			['0:1234:dc0:41:216:3eff:fe67:3e01'],
			['[012q:1234:dc0:41:216:3eff:fe67:3e01]'],
		];
	}

	/**
	 * @dataProvider invalidHostProvider
	 *
	 * @param $host
	 */
	public function testHostValidationInvalid($host){
		$this->assertFalse(isValidHost($host), 'Bad hostname accepted: '.$host);
	}

	public function testMBPathinfo(){
		$a = '/mnt/files/飛兒樂 團光茫.mp3';
		$q = mb_pathinfo($a);
		$this->assertSame($q['dirname'], '/mnt/files', 'UNIX dirname not matched');
		$this->assertSame($q['basename'], '飛兒樂 團光茫.mp3', 'UNIX basename not matched');
		$this->assertSame($q['extension'], 'mp3', 'UNIX extension not matched');
		$this->assertSame($q['filename'], '飛兒樂 團光茫', 'UNIX filename not matched');
		$this->assertSame(
			mb_pathinfo($a, PATHINFO_DIRNAME),
			'/mnt/files',
			'Dirname path element not matched'
		);
		$this->assertSame(
			mb_pathinfo($a, PATHINFO_BASENAME),
			'飛兒樂 團光茫.mp3',
			'Basename path element not matched'
		);
		$this->assertSame(mb_pathinfo($a, 'filename'), '飛兒樂 團光茫', 'Filename path element not matched');
		$a = 'c:\mnt\files\飛兒樂 團光茫.mp3';
		$q = mb_pathinfo($a);
		$this->assertSame($q['dirname'], 'c:\mnt\files', 'Windows dirname not matched');
		$this->assertSame($q['basename'], '飛兒樂 團光茫.mp3', 'Windows basename not matched');
		$this->assertSame($q['extension'], 'mp3', 'Windows extension not matched');
		$this->assertSame($q['filename'], '飛兒樂 團光茫', 'Windows filename not matched');
	}

	public function testFilenameToType(){
		$this->assertSame(
			filenameToType('abc.jpg?xyz=1'),
			'image/jpeg',
			'Query string not ignored in filename'
		);

		$this->assertSame(
			filenameToType('abc.xyzpdq'),
			'application/octet-stream',
			'Default MIME type not applied to unknown extension'
		);
	}

	public function testGetMimeType(){
		$this->assertSame('application/pdf', get_mime_type('pdf'), 'MIME TYPE lookup failed');
	}

	public function qencodeDataProvider():array{
		return [
			['text', "\xa1Hola! Se\xf1or!", '=A1Hola!_Se=F1or!'],
			['comment', "\xa1Hola! Se\xf1or!", '=A1Hola!_Se=F1or!'],
			['phrase', "\xa1Hola! Se\xf1or!", '=A1Hola!_Se=F1or!'],
			['text', "\xc2\xa1Hola! Se\xc3\xb1or!", '=C2=A1Hola!_Se=C3=B1or!'],
			['text', "Nov\xc3\xa1=", 'Nov=C3=A1=3D'],
		];
	}

	/**
	 * @dataProvider qencodeDataProvider
	 */
	public function testEncodeQ($pos, $str, $expected){
		$this->assertSame($expected, encodeQ($str, $pos), 'Q Encoding ('.$pos.') failed');
	}

	/**
	 * DKIM header canonicalization tests.
	 *
	 * @see https://tools.ietf.org/html/rfc6376#section-3.4.2
	 */
	public function testDKIMHeaderCanonicalization(){
		//Example from https://tools.ietf.org/html/rfc6376#section-3.4.5
		$this->assertSame(
			"a:X\r\nb:Y Z\r\n",
			DKIM_HeaderC("A: X\r\nB : Y\t\r\n\tZ  \r\n"),
			'DKIM header canonicalization incorrect'
		);

		//Check that long folded lines with runs of spaces are canonicalised properly
		$preheaders = "Long-Header-1: <https://example.com/somescript.php?".
		              "id=1234567890&name=Abcdefghijklmnopquestuvwxyz&hash=\r\n abc1234".
		              "\r\nLong-Header-2: This  is  a  long  header  value  that  contains  runs  of  spaces and trailing    ".
		              "\r\n and   is   folded   onto   2   lines";
		$postheaders = "long-header-1:<https://example.com/somescript.php?id=1234567890&".
		               "name=Abcdefghijklmnopquestuvwxyz&hash=abc1234\r\nlong-header-2:This is a long".
		               " header value that contains runs of spaces and trailing and is folded onto 2 lines";
		$this->assertEquals(
			$postheaders,
			DKIM_HeaderC($preheaders),
			'DKIM header canonicalization of long lines incorrect'
		);
	}

	public function punycodeAddresProvider():array{
		return [
			['foo', 'foo'], // coverage - no @
			['@françois.ch', '@xn--franois-xxa.ch'],
		];
	}

	/**
	 * @dataProvider punycodeAddresProvider
	 *
	 * @param string $addr
	 * @param string $expected
	 */
	public function testPunyencodeAddress(string $addr, string $expected){
		$addr   = mb_convert_encoding($addr, 'ISO-8859-1', 'UTF-8');
		$result = punyencodeAddress($addr);

		$this->assertSame($expected, $result);
	}

}
