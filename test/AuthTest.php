<?php
/**
 * Class AuthTest
 *
 * @filesource   AuthTest.php
 * @created      11.04.2019
 * @package      PHPMailer\Test
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailerOAuthInterface;

class AuthTest extends TestAbstract{

	/**
	 * Test OAuth method
	 */
	public function testOAuth(){

		$options = [
			'provider'     => 'dummyprovider',
			'userName'     => 'dummyusername',
			'clientSecret' => 'dummyclientsecret',
			'clientId'     => 'dummyclientid',
			'refreshToken' => 'dummyrefreshtoken',
		];

		$oauth = new OAuth($options);
		$this->assertInstanceOf(PHPMailerOAuthInterface::class, $oauth);
		$this->mailer->setOAuth($oauth);
		$this->assertInstanceOf(PHPMailerOAuthInterface::class, $this->mailer->getOAuth());
	}

	/**
	 * Test CRAM-MD5 authentication.
	 */
	public function testAuthCRAMMD5(){
		$this->markTestIncomplete('Needs a connection to a server that supports this auth mechanism, so disabled out by default.');

		$this->mailer->host       = 'hostname';
		$this->mailer->port       = 587;
		$this->mailer->SMTPAuth   = true;
		$this->mailer->SMTPSecure = $this->mailer::ENCRYPTION_STARTTLS;
		$this->mailer->AuthType   = 'CRAM-MD5';
		$this->mailer->username   = 'username';
		$this->mailer->password   = 'password';
		$this->mailer->Body       = 'Test body';
		$this->mailer->Subject    .= ': Auth CRAM-MD5';
		$this->mailer->From       = 'from@example.com';
		$this->mailer->Sender     = 'from@example.com';
		$this->mailer->clearAllRecipients();
		$this->mailer->addTO('user@example.com');

		$this->assertSentMail();
	}


}
