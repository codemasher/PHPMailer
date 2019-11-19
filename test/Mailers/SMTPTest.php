<?php
/**
 * Class SMTPTest
 *
 * @filesource   SMTPTest.php
 * @created      11.04.2019
 * @package      PHPMailer\Test\Mailers
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test\Mailers;

use PHPMailer\PHPMailer\{POP3, SMTPMailer};

use function escapeshellarg, shell_exec, sleep;

use const PHP_OS_FAMILY;

/**
 * @property \PHPMailer\PHPMailer\SMTPMailer $mailer
 */
class SMTPTest extends MailerTestAbstract{

	protected $FQCN = SMTPMailer::class;

	/**
	 * PIDs of any processes we need to kill.
	 *
	 * @var array
	 */
	protected $pids = [];

	protected function setUp():void{

		if(PHP_OS_FAMILY !== 'Windows'){
			$this->markTestSkipped('Linux skipped for now until timeout is fixed');
			return;
		}

		parent::setUp();

		$this->pids = [];
	}

	protected function tearDown():void{

		foreach($this->pids as $pid){
			$p = escapeshellarg($pid);
			shell_exec("ps $p && kill -TERM $p");
		}

		parent::tearDown();
	}

	public function testBadCommand(){
		$this->mailer->smtpConnect();
		$smtp = $this->mailer->getSMTP();
		$this->assertFalse($smtp->mail("somewhere\nbad"), 'Bad SMTP command containing breaks accepted');
	}

	/**
	 * @group slow
	 */
	public function testConnect(){
		$this->assertTrue($this->mailer->smtpConnect(), 'SMTP single connect failed');
		$this->mailer->closeSMTP();
	}

	/**
	 * @group slow
	 */
	public function testConnectInvalidHosts(){
		// All these hosts are expected to fail
		$this->mailer->host = 'xyz://bogus:25;tls://[bogus]:25;ssl://localhost:12345;tls://localhost:587;10.10.10.10:54321;localhost:12345;10.10.10.10'.TEST_MAIL_HOST.' ';
		$this->assertFalse($this->mailer->smtpConnect());
		$this->mailer->closeSMTP();
	}

	/**
	 * @group slow
	 */
	public function testConnectIPv6(){
		$this->mailer->host = '[::1]:'.$this->mailer->port.';'.TEST_MAIL_HOST;
		$this->assertTrue($this->mailer->smtpConnect(), 'SMTP IPv6 literal multi-connect failed');
		$this->mailer->closeSMTP();
	}

	/**
	 * @group slow
	 */
	public function testMultiConnect(){
		$this->mailer->host = 'localhost:12345;10.10.10.10:54321;'.TEST_MAIL_HOST;
		$this->assertTrue($this->mailer->smtpConnect(), 'SMTP multi-connect failed');
		$this->mailer->closeSMTP();
	}

	/**
	 * @group slow
	 */
	public function testConnectHostWithSpaces(){
		$this->mailer->host = ' localhost:12345 ; '.TEST_MAIL_HOST.' ';
		$this->assertTrue($this->mailer->smtpConnect(), 'SMTP hosts with stray spaces failed');
		$this->mailer->closeSMTP();
	}

	public function testConnectWithTLS(){
		// Need to pick a harmless option so as not cause problems of its own! socket:bind doesn't work with Travis-CI
		$this->mailer->host = TEST_MAIL_HOST;
		$this->assertTrue($this->mailer->smtpConnect(['ssl' => ['verify_depth' => 10]]));

		$smtp = $this->mailer->getSMTP();
		$this->assertFalse($smtp->startTLS(), 'SMTP connect with options failed');
		$this->assertFalse($this->mailer->SMTPAuth);
		$this->mailer->closeSMTP();
	}

	/**
	 * Test keepalive (sending multiple messages in a single connection).
	 */
	public function testKeepAlive(){
		$this->mailer->SMTPKeepAlive = true;
		$this->setMessage('SMTP keep-alive test.', __FUNCTION__.': SMTP keep-alive 1');
		$this->assertSentMail();
		$this->setMessage('SMTP keep-alive test.', __FUNCTION__.': SMTP keep-alive 2');
		$this->assertSentMail();
		$this->mailer->closeSMTP();
	}

	/**
	 * Test sending multiple messages with separate connections.
	 */
	public function testNoKeepAlive(){
		$this->setMessage('SMTP no-keep-alive test.', __FUNCTION__.': SMTP no-keep-alive 1');
		$this->assertSentMail();
		$this->setMessage('SMTP no-keep-alive test.', __FUNCTION__.': SMTP no-keep-alive 2');
		$this->assertSentMail();
		$this->mailer->closeSMTP();
	}

	/**
	 * Use a fake POP3 server to test POP-before-SMTP auth with a known-good login.
	 *
	 * @group pop3
	 */
	public function testPopBeforeSmtpGood(){

		if(PHP_OS_FAMILY !== 'Linux'){
			$this->markTestSkipped('Linux only');

			return;
		}

		//Start a fake POP server
		$pid = shell_exec(
			'/usr/bin/nohup '.
			$this->INCLUDE_DIR.
			'/scripts/runfakepopserver.sh 1100 >/dev/null 2>/dev/null & printf "%u" $!'
		);

		$this->pids[] = $pid;

		sleep(1);
		//Test a known-good login
		$this->assertTrue(
			(new POP3)->authorise('localhost', 1100, 10, 'user', 'test', $this->mailer->loglevel),
			'POP before SMTP failed'
		);

		//Kill the fake server, don't care if it fails
		@shell_exec('kill -TERM '.escapeshellarg($pid));
		sleep(2);
	}

	/**
	 * Use a fake POP3 server to test POP-before-SMTP auth
	 * with a known-bad login.
	 *
	 * @group pop3
	 */
	public function testPopBeforeSmtpBad(){

		if(PHP_OS_FAMILY !== 'Linux'){
			$this->markTestSkipped('Linux only');

			return;
		}

		//Start a fake POP server on a different port
		//so we don't inadvertently connect to the previous instance
		$pid = shell_exec(
			'/usr/bin/nohup '.
			$this->INCLUDE_DIR.
			'/scripts/runfakepopserver.sh 1101 >/dev/null 2>/dev/null & printf "%u" $!'
		);

		$this->pids[] = $pid;

		sleep(2);

		//Test a known-bad login
		$this->assertFalse(
			(new POP3)->authorise('localhost', 1101, 10, 'user', 'xxx', $this->mailer->loglevel),
			'POP before SMTP should have failed'
		);

		//Kill the fake server, don't care if it fails
		@shell_exec('kill -TERM '.escapeshellarg($pid));
		sleep(2);
	}

}
