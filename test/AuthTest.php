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

		$this->options->smtp_host       = 'hostname';
		$this->options->smtp_port       = 587;
		$this->options->smtp_username   = 'username';
		$this->options->smtp_password   = 'password';
		$this->options->smtp_auth       = true;
		$this->options->smtp_authtype   = 'CRAM-MD5';
		$this->options->smtp_encryption = $this->mailer::ENCRYPTION_STARTTLS;


		$this->mailer->setOptions($this->options);

		$this->mailer->setMessageBody('Test body');
		$this->mailer->setSubject('Auth CRAM-MD5');
		$this->mailer->setSender('from@example.com');
		$this->mailer->setFrom('from@example.com');
		$this->mailer->clearAllRecipients();
		$this->mailer->addTO('user@example.com');

		$this->assertSentMail();
	}


}
