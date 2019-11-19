<?php
/**
 *
 * @filesource   SendmailTest.php
 * @created      19.11.19
 * @package      PHPMailer\Test\Mailers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test\Mailers;

use PHPMailer\PHPMailer\SendmailMailer;

use const PHP_OS_FAMILY;

/**
 * Class SendmailTest
 */
class SendmailTest extends MailerTestAbstract{

	protected $FQCN = SendmailMailer::class;

	protected function setUp():void{

		if(PHP_OS_FAMILY !== 'Linux'){
			$this->markTestSkipped('Linux only');
			return;
		}

		parent::setUp();
	}

}
