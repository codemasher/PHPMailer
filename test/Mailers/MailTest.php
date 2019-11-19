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
		$this->mailer->CharSet = 'UTF-8';

		// This should select Q-encoding automatically and should fold
		$exp = '=?UTF-8?Q?eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee?='.
		       $this->mailer->getLE().
		       ' =?UTF-8?Q?eeeeeeeeeeeee=C3=A9?=';
		$act = str_repeat('e', $this->mailer::LINE_LENGTH_STD_MAIL).'é';
		$this->assertSame($exp, $this->mailer->encodeHeader($act), 'Folded Q-encoded header value incorrect');
	}

	public function testHeaderEncodingFoldedQASCII(){
		$this->mailer->CharSet = 'UTF-8';
		//This should Q-encode as ASCII and fold (previously, this did not encode)
		$exp = '=?us-ascii?Q?eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee?='.
		       $this->mailer->getLE().
		       ' =?us-ascii?Q?eeeeeeeeeeeeeeeeeeeeeeeeee?=';
		$act = str_repeat('e', $this->mailer::LINE_LENGTH_STD_MAIL + 10);

		$this->assertSame($exp, $this->mailer->encodeHeader($act), 'Long header value incorrect');
	}

}
