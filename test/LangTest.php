<?php
/**
 * Class LangTest
 *
 * @filesource   LangTest.php
 * @created      22.11.2019
 * @package      PHPMailer\Test
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\Language\{LanguageEN, PHPMailerLanguageInterface};
use PHPUnit\Framework\TestCase;

use function array_diff, array_diff_key, array_keys, print_r, sprintf, strpos;

use function PHPMailer\PHPMailer\getLanguages;

/**
 * Check language files for missing or excess translations.
 */
class LangTest extends TestCase{

	/**
	 * @var \PHPMailer\PHPMailer\Language\LanguageEN
	 */
	protected $en;

	protected function setUp():void{
		$this->en = new LanguageEN;
	}

	public function languageProvider():array{
		$languages = [];

		foreach(getLanguages() as $fqcn => $info){
			$languages[$info['name']] = [$fqcn, $info['name'], $info['dir'], $info['code']];
		}

		return $languages;
	}

	/**
	 * @dataProvider languageProvider
	 *
	 * @param string $fqcn
	 * @param string $name
	 * @param string $dir
	 * @param string $code
	 *
	 * @return void
	 */
	public function testTranslations(string $fqcn, string $name, string $dir, string $code):void{
		/** @var \PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface $lang */
		$lang = new $fqcn;

		$this->assertInstanceOf(PHPMailerLanguageInterface::class, $lang);

		// check position of the link in RTL languages
		if($dir === 'RTL'){
			$this->assertSame(strpos($lang->string('smtp_connect_failed'), 'https'), 0);
		}

		$existing = array_diff($lang->strings(), $this->en->strings());
		$missing  = array_diff_key($this->en->strings(), $existing);

		unset($missing['dir']); // this key is intentionally (not) set

		print_r([
			sprintf('missing translations in language "%s" (%s):', $name, $code),
			array_keys($missing),
		]);
	}

}
