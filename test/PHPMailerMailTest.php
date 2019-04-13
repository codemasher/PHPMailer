<?php
/**
 * Class PHPMailerMailTest
 *
 * @filesource   PHPMailerMailTest.php
 * @created      13.04.2019
 * @package      PHPMailer\Test
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test;

class PHPMailerMailTest extends MailerTestAbstract{

	protected function setUp():void{
		parent::setUp();

		$this->mailer->setMailerMail();
	}

}
