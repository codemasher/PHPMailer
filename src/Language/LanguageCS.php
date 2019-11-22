<?php
/**
 * Czech PHPMailer language file
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageCS extends PHPMailerLanguageAbstract{

	protected $code        = 'cs';
	protected $name        = 'Czech';
	protected $native_name = 'čeština';

	protected $authenticate         = 'Chyba SMTP: Autentizace selhala.';
	protected $connect_host         = 'Chyba SMTP: Nelze navázat spojení se SMTP serverem.';
	protected $data_not_accepted    = 'Chyba SMTP: Data nebyla přijata.';
	protected $empty_message        = 'Prázdné tělo zprávy';
	protected $encoding             = 'Neznámé kódování: %s';
	protected $execute              = 'Nelze provést: %s';
	protected $file_access          = 'Nelze získat přístup k souboru: %s';
	protected $file_open            = 'Chyba souboru: Nelze otevřít soubor pro čtení: %s';
	protected $from_failed          = 'Následující adresa odesílatele je nesprávná: %s';
	protected $instantiate          = 'Nelze vytvořit instanci emailové funkce.';
	protected $invalid_address      = 'Neplatná adresa (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer není podporován.';
	protected $provide_address      = 'Musíte zadat alespoň jednu emailovou adresu příjemce.';
	protected $recipients_failed    = 'Chyba SMTP: Následující adresy příjemců nejsou správně: %s';
	protected $signing              = 'Chyba přihlašování: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() selhal.';
	protected $smtp_error           = 'Chyba SMTP serveru: ';
	protected $variable_set         = 'Nelze nastavit nebo změnit proměnnou: ';
	protected $extension_missing    = 'Chybí rozšíření: %s';

}
