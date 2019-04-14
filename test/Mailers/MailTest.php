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

class MailTest extends MailerTestAbstract{

	protected function setUp():void{
		parent::setUp();

		$this->mailer->setMailerMail();
	}

}
