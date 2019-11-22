<?php
/**
 * Interface PHPMailerInterface
 *
 * @filesource   PHPMailerInterface.php
 * @created      14.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface;

interface PHPMailerInterface{
	public function setLanguage(string $langcode):void;
	public function setLanguageInterface(PHPMailerLanguageInterface $language):void;
	public function getLE():string;
	public function postSend():bool;
}
