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
use PHPMailer\PHPMailer\PHPMailerOptions;
use PHPUnit\Framework\TestCase;
use Psr\Log\{AbstractLogger, LoggerInterface, NullLogger};
use Closure, DirectoryIterator, ReflectionClass, ReflectionMethod, ReflectionProperty;

use function count, date, defined, dirname, file_get_contents, mb_strlen, sprintf, str_repeat,
	str_replace, strlen, strpos, substr, trim, unlink;

use const PHP_VERSION;

abstract class TestAbstract extends TestCase{

	/**
	 * @var string
	 */
	protected $FQCN = null;

	/**
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * @var \PHPMailer\PHPMailer\PHPMailerInterface
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

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var int
	 */
	protected $port;

	/**
	 * @var string
	 */
	protected $from;
	/**
	 * @var \PHPMailer\PHPMailer\PHPMailerOptions
	 */
	protected $options;

	protected function setUp():void{
		$this->IS_CI       = defined('TEST_IS_CI') && TEST_IS_CI === true;
		$this->INCLUDE_DIR = dirname(__DIR__); //Default to the dir above the test dir, i.e. the project home dir

		$this->reflection  = $this->FQCN !== null
			? new ReflectionClass($this->FQCN)
			: new ReflectionClass(new class () extends PHPMailer{});

		$this->logger  = $this->getDebugLogger();
		$this->options = new PHPMailerOptions;

		$this->host = defined('TEST_MAIL_HOST') ? TEST_MAIL_HOST : 'localhost';
		$this->port = defined('TEST_MAIL_PORT') && !empty(TEST_MAIL_PORT) ? intval(TEST_MAIL_PORT) : 2500;
		$this->from = defined('TEST_MAIL_FROM') ? TEST_MAIL_FROM : 'unit_test@phpmailer.example.com';


		$this->options->smtp_host     = $this->host;
		$this->options->smtp_port     = $this->port;
		$this->options->smtp_username = '';
		$this->options->smtp_password = '';
		$this->options->hostname = 'localhost.localdomain';

		$this->mailer = $this->reflection->newInstanceArgs([$this->options, $this->logger]);

		$this->mailer
			->setFrom($this->from, 'Unit Tester')
			->setSender('unit_test@phpmailer.example.com')
			->setSubject('Unit Test')
			->setPriority(3);

		// for mail()
		// @todo: PHPMailer should do this internally
		$this->iniSet('SMTP', $this->host); // windows only
		$this->iniSet('smtp_port', $this->port);
		$this->iniSet('sendmail_from', $this->from);
	}

	protected function tearDown():void{
		unset($this->mailer);
		$this->changelog = [];
		$this->notelog   = [];
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
	 * @param string $method
	 * @param array  $args
	 *
	 * @return mixed
	 */
	protected function callMethod(string $method, array $args){
		return $this->getMethod($method)->invokeArgs($this->mailer, $args);
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
	 * @param string $property
	 *
	 * @return mixed
	 */
	protected function getPropertyValue(string $property){
		return $this->getProperty($property)->getValue($this->mailer);
	}

	/**
	 * @param object $object
	 * @param string $property
	 * @param mixed  $value
	 *
	 * @return void
	 */
	protected function setProperty(object $object, string $property, $value):void{
		$this->getProperty($property)->setValue($object, $value);
	}

	/**
	 * A simple PSR-3 logger implementation for console output
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	protected function getDebugLogger():LoggerInterface{

		if($this->IS_CI){
			return new NullLogger;
		}

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
	 * @param string $body
	 * @param string $subject
	 * @param string $to
	 *
	 * @return $this
	 */
	protected function setMessage(string $body, string $subject, string $to = 'user@example.com'){
		$this->mailer
			->setSubject($this->FQCN.'::'.$subject)
			->setMessageBody($this->buildBody($body))
			->addTO($to);

		return $this;
	}

	/**
	 * Sends an email to the test server and asserts the expected result
	 *
	 * @param \Closure|null $assertFunc
	 */
	protected function assertSentMail(Closure $assertFunc = null){
		$this->assertTrue($this->mailer->send());

		$id       = $this->mailer->getLastMessageID();
		$sent     = $this->mailer->getSentMIMEMessage();
		$received = [];

		$this->logger->info($id);

		foreach(new DirectoryIterator($this->INCLUDE_DIR.'/logs') as $fileinfo){
			if(!$fileinfo->isDot()){
				$content = file_get_contents($fileinfo->getPathname());

				if(strpos($content, $id) !== false){
					$received[] = trim($content);

					if(defined('TEST_CLEANUP_MAIL_LOG') && TEST_CLEANUP_MAIL_LOG === true){
						unlink($fileinfo->getPathname());
					}
				}

			}
		}

		if($assertFunc instanceof Closure){
			$assertFunc->call($this, trim($sent), $received);
		}

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
		$contentType = $this->getPropertyValue('contentType');

		// Determine line endings for message
		$eol          = "\r\n";
		$bullet_start = ' - ';
		$bullet_end   = "\r\n";
		$list_start   = '';
		$list_end     = '';

		if($contentType === 'text/html' || strlen($this->getPropertyValue('altBody')) > 0){
			$eol          = "<br>\r\n";
			$bullet_start = '<li>';
			$bullet_end   = "</li>\r\n";
			$list_start   = "<ul>\r\n";
			$list_end     = "</ul>\r\n";
		}

		$report = ''
			.'-----------------------'.$eol
			.' Unit Test Information '.$eol
			.'-----------------------'.$eol.$eol
			.'phpmailer version: '.$this->mailer::VERSION.$eol
			.'php version: '.PHP_VERSION.$eol
			.'Content Type: '.$contentType.$eol
			.'CharSet: '.$this->options->charSet.$eol
			.'above body length: '.strlen($body).' (multibyte: '.mb_strlen($body).')'.$eol
		;

		if(strlen($this->options->smtp_host) > 0){
			$report .= 'Host: '.$this->options->smtp_host.$eol;
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
					.'Type: '.$attachment->mimeType.$bullet_end;
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
		$priority = $this->getPropertyValue('priority');
		if($priority !== 3){
			$this->addChange('Priority', $priority);
		}

		$encoding = $this->getPropertyValue('encoding');
		if($encoding !== $this->mailer::ENCODING_8BIT){
			$this->addChange('Encoding', $encoding);
		}

		if($this->options->charSet !== $this->mailer::CHARSET_ISO88591){
			$this->addChange('CharSet', $this->options->charSet);
		}

		$sender = $this->getPropertyValue('sender');
		if(!empty($sender)){
			$this->addChange('Sender', $sender);
		}

		if($this->options->wordWrap !== 0){
			$this->addChange('WordWrap', $this->options->wordWrap);
		}

		if($this->options->smtp_port !== 25){
			$this->addChange('Port', $this->options->smtp_port);
		}

		if($this->options->hostname !== 'localhost.localdomain'){
			$this->addChange('Helo', $this->options->hostname);
		}

		if($this->options->smtp_auth){
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
