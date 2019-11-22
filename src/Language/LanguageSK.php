<?php
/**
 * Slovak PHPMailer language file
 *
 * @author Michal Tinka <michaltinka@gmail.com>
 * @author Peter Orlický <pcmanik91@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageSK extends PHPMailerLanguageAbstract{

	protected $code        = 'sk';
	protected $name        = 'Slovak';
	protected $native_name = 'Slovenčina';

	protected $authenticate         = 'SMTP Error: Chyba autentifikácie.';
	protected $connect_host         = 'SMTP Error: Nebolo možné nadviazať spojenie so SMTP serverom.';
	protected $data_not_accepted    = 'SMTP Error: Dáta neboli prijaté';
	protected $empty_message        = 'Prázdne telo správy.';
	protected $encoding             = 'Neznáme kódovanie: %s';
	protected $execute              = 'Nedá sa vykonať: %s';
	protected $file_access          = 'Súbor nebol nájdený: %s';
	protected $file_open            = 'File Error: Súbor sa otvoriť pre čítanie: %s';
	protected $from_failed          = 'Následujúca adresa From je nesprávna: %s';
	protected $instantiate          = 'Nedá sa vytvoriť inštancia emailovej funkcie.';
	protected $invalid_address      = 'Neodoslané, emailová adresa je nesprávna (%1$s): %2$s';
	protected $mailer_not_supported = ' emailový klient nieje podporovaný.';
	protected $provide_address      = 'Musíte zadať aspoň jednu emailovú adresu príjemcu.';
	protected $recipients_failed    = 'SMTP Error: Adresy príjemcov niesu správne: %s';
	protected $signing              = 'Chyba prihlasovania: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() zlyhalo.';
	protected $smtp_error           = 'SMTP chyba serveru: ';
	protected $variable_set         = 'Nemožno nastaviť alebo resetovať premennú: ';
	protected $extension_missing    = 'Chýba rozšírenie: %s';

}
