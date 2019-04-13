<?php
/**
 * Class TestAbstract
 *
 * @filesource   TestAbstract.php
 * @created      09.04.2019
 * @package      PHPMailer\Test
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use ReflectionClass, ReflectionMethod, ReflectionProperty;

abstract class TestAbstract extends TestCase{

	/**
	 * @var string
	 */
	protected $FQCN = PHPMailer::class;

	/**
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * @var \PHPMailer\PHPMailer\PHPMailer
	 */
	protected $mailer;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * Holds the change log.
	 *
	 * @var string[]
	 */
	protected $changelog = [];

	/**
	 * Holds the note log.
	 *
	 * @var string[]
	 */
	protected $notelog = [];

	/**
	 * determines whether the tests run on a CI environment (Travis, Scrunitizer etc.)
	 *
	 * @var bool
	 */
	protected $IS_CI;

	/**
	 * Default include path.
	 *
	 * @var string
	 */
	protected $INCLUDE_DIR = '..';

	protected function setUp():void{
		$this->IS_CI       = defined('TEST_IS_CI') && TEST_IS_CI === true;
		$this->INCLUDE_DIR = dirname(__DIR__); //Default to the dir above the test dir, i.e. the project home dir

		$this->reflection  = new ReflectionClass($this->FQCN);
		$this->mailer      = $this->getInstance();
		$this->logger      = $this->getDebugLogger();

		if(!$this->IS_CI){
			$this->mailer->setLogger($this->logger);
		}
	}

	protected function tearDown():void{
		unset($this->mailer);
		$this->changelog = [];
		$this->notelog   = [];
	}

	/**
	 * @param mixed ...$params
	 *
	 * @return object
	 */
	protected function getInstance(...$params):object{
		return $this->reflection->newInstanceArgs($params);
	}

	/**
	 * @param string $method
	 *
	 * @return \ReflectionMethod
	 */
	protected function getMethod(string $method):ReflectionMethod{
		$method = $this->reflection->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	/**
	 * @param string $property
	 *
	 * @return \ReflectionProperty
	 */
	protected function getProperty(string $property):ReflectionProperty{
		$property = $this->reflection->getProperty($property);
		$property->setAccessible(true);

		return $property;
	}

	/**
	 * @param        $object
	 * @param string $property
	 * @param        $value
	 *
	 * @return void
	 */
	protected function setProperty(object $object, string $property, $value):void{
		$property = $this->getProperty($property);
		$property->setAccessible(true);
		$property->setValue($object, $value);
	}

	/**
	 * Simple instance test
	 */
	public function testInstance(){
		$this->assertInstanceOf($this->FQCN, $this->getInstance());
	}

	/**
	 * A simple PSR-3 logger implementation for console output
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	protected function getDebugLogger():LoggerInterface{
		return new class () extends AbstractLogger{
			public function log($level, $message, array $context = []){
				echo sprintf(
					'[%s][%s] %s',
					date('Y-m-d H:i:s'),
					substr($level, 0, 4),
					str_replace("\n", "\n".str_repeat(' ', 40), trim($message))
				)."\n";
			}
		};
	}

	/**
	 * @return $this
	 */
	protected function setupMailer(){
		$host = defined('TEST_MAIL_HOST') ? TEST_MAIL_HOST : 'localhost';
		$port = defined('TEST_MAIL_PORT') && !empty(TEST_MAIL_PORT) ? intval(TEST_MAIL_PORT) : 2500;
		$from = defined('TEST_MAIL_FROM') ? TEST_MAIL_FROM : 'unit_test@phpmailer.example.com';

		$this->mailer->host     = $host;
		$this->mailer->port     = $port;
		$this->mailer->username = '';
		$this->mailer->password = '';
		$this->mailer->From     = $from;
		$this->mailer->Sender   = 'unit_test@phpmailer.example.com';
		$this->mailer->FromName = 'Unit Tester';
		$this->mailer->Subject  = 'Unit Test';
		$this->mailer->Helo     = 'localhost.localdomain';
		$this->mailer->Priority = 3;
		$this->mailer->loglevel = $this->mailer::DEBUG_CONNECTION; //Full debug output

		// for mail()
		// @todo: PHPMailer should do this internally
		$this->iniSet('SMTP', $host);
		$this->iniSet('smtp_port', $port);
		$this->iniSet('sendmail_from', $from);

		return $this;
	}

	/**
	 * @param string $body
	 * @param string $subject
	 * @param string $to
	 *
	 * @return $this
	 */
	protected function setMessage(string $body, string $subject, string $to = 'user@example.com'){
		$this->mailer->Subject = $this->mailer->getMailer().': '.$subject;
		$this->mailer->Body    = $this->buildBody($body);
		$this->mailer->addTO($to);

		return $this;
	}

	/**
	 * Build the body of the message in the appropriate format.
	 *
	 * @param string $body
	 *
	 * @return string
	 */
	protected function buildBody(string $body):string{
		$this->checkChanges();

		// Determine line endings for message
		if($this->mailer->ContentType === 'text/html' || strlen($this->mailer->AltBody) > 0){
			$eol          = "<br>\r\n";
			$bullet_start = '<li>';
			$bullet_end   = "</li>\r\n";
			$list_start   = "<ul>\r\n";
			$list_end     = "</ul>\r\n";
		}
		else{
			$eol          = "\r\n";
			$bullet_start = ' - ';
			$bullet_end   = "\r\n";
			$list_start   = '';
			$list_end     = '';
		}

		$report = ''
			.'-----------------------'.$eol
			.' Unit Test Information '.$eol
			.'-----------------------'.$eol.$eol
			.'phpmailer version: '.$this->mailer::VERSION.$eol
			.'php version: '.PHP_VERSION.$eol
			.'Content Type: '.$this->mailer->ContentType.$eol
			.'CharSet: '.$this->mailer->CharSet.$eol
			.'above body length: '.strlen($body).' (multibyte: '.mb_strlen($body).')'.$eol
		;

		if(strlen($this->mailer->host) > 0){
			$report .= 'Host: '.$this->mailer->host.$eol;
		}

		// If attachments then create an attachment list
		$attachments = $this->mailer->getAttachments();
		if(count($attachments) > 0){
			$report .= 'Attachments:'.$eol;
			$report .= $list_start;
			/** @var \PHPMailer\PHPMailer\Attachment $attachment */
			foreach($attachments as $attachment){
				$report .= $bullet_start.'Name: '.$attachment->name.', '
					.'Encoding: '.$attachment->encoding.', '
					.'Type: '.$attachment->type.$bullet_end;
			}
			$report .= $list_end.$eol;
		}

		// If there are changes then list them
		if(count($this->changelog) > 0){
			$report .= $eol.'Changes'.$eol.'-------'.$eol.$list_start;

			for($i = 0; $i < count($this->changelog); ++$i){
				$report .= $bullet_start.$this->changelog[$i][0].' was changed to ['.$this->changelog[$i][1].']'.$bullet_end;
			}

			$report .= $list_end.$eol.$eol;
		}

		// If there are notes then list them
		if(count($this->notelog) > 0){
			$report .= 'Notes'.$eol.'-----'.$eol;

			$report .= $list_start;
			for($i = 0; $i < count($this->notelog); ++$i){
				$report .= $bullet_start.$this->notelog[$i].$bullet_end;
			}
			$report .= $list_end;
		}

		// Re-attach the original body
		return $body.$eol.$eol.$report;
	}

	/**
	 * Check which default settings have been changed for the report.
	 */
	protected function checkChanges(){
		if($this->mailer->Priority !== 3){
			$this->addChange('Priority', $this->mailer->Priority);
		}
		if($this->mailer->Encoding !== '8bit'){
			$this->addChange('Encoding', $this->mailer->Encoding);
		}
		if($this->mailer->CharSet !== 'iso-8859-1'){
			$this->addChange('CharSet', $this->mailer->CharSet);
		}
		if($this->mailer->Sender !== ''){
			$this->addChange('Sender', $this->mailer->Sender);
		}
		if($this->mailer->WordWrap !== 0){
			$this->addChange('WordWrap', $this->mailer->WordWrap);
		}
		if($this->mailer->getMailer() !== $this->mailer::MAILER_MAIL){
			$this->addChange('Mailer', $this->mailer->getMailer());
		}
		if($this->mailer->port !== 25){
			$this->addChange('Port', $this->mailer->port);
		}
		if($this->mailer->Helo !== 'localhost.localdomain'){
			$this->addChange('Helo', $this->mailer->Helo);
		}
		if($this->mailer->SMTPAuth){
			$this->addChange('SMTPAuth', 'true');
		}
	}

	/**
	 * Add a changelog entry.
	 *
	 * @param string $sName
	 * @param string $sNewValue
	 */
	protected function addChange($sName, $sNewValue){
		$this->changelog[] = [$sName, $sNewValue];
	}

	/**
	 * Adds a simple note to the message.
	 *
	 * @param string $sValue
	 */
	protected function addNote($sValue){
		$this->notelog[] = $sValue;
	}

}
