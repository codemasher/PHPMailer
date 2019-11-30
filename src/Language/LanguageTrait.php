<?php
/**
 * Trait LanguageTrait
 *
 * @filesource   LanguageTrait.php
 * @created      22.11.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer\Language;

use PHPMailer\PHPMailer\PHPMailerException;

use function class_exists, preg_match, sprintf, str_replace, strtoupper;

trait LanguageTrait{

	/**
	 * The array of available languages.
	 *
	 * @var  \PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface
	 */
	protected $lang;

	/**
	 * Set the language for error messages.
	 * The default language is English.
	 *
	 * @param string $langcode ISO 639-1 2-character language code (e.g. French is "fr")
	 *
	 * @return void
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setLanguage(string $langcode):void{

		//Validate $langcode
		if(!preg_match('/^[a-z]{2}(?:-_[a-z]{2})?$/i', $langcode)){
			$langcode = 'en';
		}

		$class = 'Language'.strtoupper(str_replace('-', '_', $langcode));
		$fqcn = __NAMESPACE__.'\\'.$class;

		if(!class_exists($fqcn)){
			throw new PHPMailerException(sprintf('Language class does not exist: %s', $class));
		}

		$this->lang = new $fqcn;
	}

	/**
	 * @param \PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface $language
	 *
	 * @return void
	 */
	public function setLanguageInterface(PHPMailerLanguageInterface $language):void{
		$this->lang = $language;
	}

}
