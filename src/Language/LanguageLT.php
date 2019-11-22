<?php
/**
 * Lithuanian PHPMailer language file
 *
 * @author Dainius Kaupaitis <dk@sum.lt>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageLT extends PHPMailerLanguageAbstract{

	protected $code        = 'lt';
	protected $name        = 'Lithuanian';
	protected $native_name = 'lietuvių kalba';

	protected $authenticate         = 'SMTP klaida: autentifikacija nepavyko.';
	protected $connect_host         = 'SMTP klaida: nepavyksta prisijungti prie SMTP stoties.';
	protected $data_not_accepted    = 'SMTP klaida: duomenys nepriimti.';
	protected $empty_message        = 'Laiško turinys tuščias';
	protected $encoding             = 'Neatpažinta koduotė: %s';
	protected $execute              = 'Nepavyko įvykdyti komandos: %s';
	protected $file_access          = 'Byla nepasiekiama: %s';
	protected $file_open            = 'Bylos klaida: Nepavyksta atidaryti: %s';
	protected $from_failed          = 'Neteisingas siuntėjo adresas: %s';
	protected $instantiate          = 'Nepavyko paleisti mail funkcijos.';
	protected $invalid_address      = 'Neteisingas adresas (%1$s): %2$s';
	protected $mailer_not_supported = ' pašto stotis nepalaikoma.';
	protected $provide_address      = 'Nurodykite bent vieną gavėjo adresą.';
	protected $recipients_failed    = 'SMTP klaida: nepavyko išsiųsti šiems gavėjams: %s';
	protected $signing              = 'Prisijungimo klaida: %s';
	protected $smtp_connect_failed  = 'SMTP susijungimo klaida';
	protected $smtp_error           = 'SMTP stoties klaida: ';
	protected $variable_set         = 'Nepavyko priskirti reikšmės kintamajam: ';

}
