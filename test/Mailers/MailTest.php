<?php
/**
 * Class MailTest
 *
 * @filesource   MailTest.php
 * @created      13.04.2019
 * @package      PHPMailer\Test\Mailers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test\Mailers;

use PHPMailer\PHPMailer\MailMailer;

/**
 * @property \PHPMailer\PHPMailer\MailMailer $mailer
 */
class MailTest extends MailerTestAbstract{

	protected $FQCN = MailMailer::class;

	public function testHeaderEncodingFoldedQ(){
		// This should select Q-encoding automatically and should fold
		$exp = '=?UTF-8?Q?eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?Q?eeeeeeeeeeeee=C3=A9?=';
		$act = str_repeat('e', $this->mailer::LINE_LENGTH_STD_MAIL).'é';
		$this->assertSame($exp, $this->callMethod('encodeHeader', [$act]), 'Folded Q-encoded header value incorrect');
	}

	public function testHeaderEncodingFoldedQASCII(){
		//This should Q-encode as ASCII and fold (previously, this did not encode)
		$exp = '=?US-ASCII?Q?eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee?='.
		       $this->mailer->getLE().
		       ' =?US-ASCII?Q?eeeeeeeeeeeeeeeeeeeeeeeeee?=';
		$act = str_repeat('e', $this->mailer::LINE_LENGTH_STD_MAIL + 10);

		$this->assertSame($exp, $this->callMethod('encodeHeader', [$act]), 'Long header value incorrect');
	}

}
