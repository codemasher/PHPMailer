<?php
/**
 * Interface PHPMailerLanguageInterface
 *
 * @filesource   PHPMailerLanguageInterface.php
 * @created      20.11.2019
 * @package      PHPMailer\PHPMailer\Language
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer\Language;

interface PHPMailerLanguageInterface{

	/**
	 * Returns a language string for a given key
	 *
	 * @param string $key
	 *
	 * @return string
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function string(string $key):string;

	/**
	 * Returns an array of language strings for the current language object
	 *
	 * @param array|null $keys
	 *
	 * @return array
	 */
	public function strings(array $keys = null):array;

}
