<?php
/**
 * Hungarian PHPMailer language file
 *
 * @author @dominicus-75
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageHU extends PHPMailerLanguageAbstract{

	protected $code        = 'hu';
	protected $name        = 'Hungarian';
	protected $native_name = 'magyar';

	protected $authenticate         = 'SMTP hiba: az azonosítás sikertelen.';
	protected $connect_host         = 'SMTP hiba: nem lehet kapcsolódni az SMTP-szerverhez.';
	protected $data_not_accepted    = 'SMTP hiba: adatok visszautasítva.';
	protected $empty_message        = 'Üres az üzenettörzs.';
	protected $encoding             = 'Ismeretlen kódolás: %s';
	protected $execute              = 'Nem lehet végrehajtani: %s';
	protected $file_access          = 'A következő fájl nem elérhető: %s';
	protected $file_open            = 'Fájl hiba: a következő fájlt nem lehet megnyitni: %s';
	protected $from_failed          = 'A feladóként megadott következő cím hibás: %s';
	protected $instantiate          = 'A PHP mail() függvényt nem sikerült végrehajtani.';
	protected $invalid_address      = 'Érvénytelen cím (%1$s): %2$s';
	protected $mailer_not_supported = ' a mailer-osztály nem támogatott.';
	protected $provide_address      = 'Legalább egy címzettet fel kell tüntetni.';
	protected $recipients_failed    = 'SMTP hiba: a címzettként megadott következő címek hibásak: %s';
	protected $signing              = 'Hibás aláírás: %s';
	protected $smtp_connect_failed  = 'Hiba az SMTP-kapcsolatban.';
	protected $smtp_error           = 'SMTP-szerver hiba: ';
	protected $variable_set         = 'A következő változók beállítása nem sikerült: ';
	protected $extension_missing    = 'Bővítmény hiányzik: %s';

}
