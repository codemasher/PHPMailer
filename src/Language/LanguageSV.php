<?php
/**
 * Swedish PHPMailer language file
 *
 * @author Johan Linnér <johan@linner.biz>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageSV extends PHPMailerLanguageAbstract{

	protected $code        = 'sv';
	protected $name        = 'Swedish';
	protected $native_name = 'Svenska';

	protected $authenticate         = 'SMTP fel: Kunde inte autentisera.';
	protected $connect_host         = 'SMTP fel: Kunde inte ansluta till SMTP-server.';
	protected $data_not_accepted    = 'SMTP fel: Data accepterades inte.';
	protected $encoding             = 'Okänt encode-format: %s';
	protected $execute              = 'Kunde inte köra: %s';
	protected $file_access          = 'Ingen åtkomst till fil: %s';
	protected $file_open            = 'Fil fel: Kunde inte öppna fil: %s';
	protected $from_failed          = 'Följande avsändaradress är felaktig: %s';
	protected $instantiate          = 'Kunde inte initiera e-postfunktion.';
	protected $invalid_address      = 'Felaktig adress (%1$s): %2$s';
	protected $provide_address      = 'Du måste ange minst en mottagares e-postadress.';
	protected $mailer_not_supported = ' mailer stöds inte.';
	protected $recipients_failed    = 'SMTP fel: Följande mottagare är felaktig: %s';
	protected $signing              = 'Signerings fel: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() misslyckades.';
	protected $smtp_error           = 'SMTP server fel: ';
	protected $variable_set         = 'Kunde inte definiera eller återställa variabel: ';
	protected $extension_missing    = 'Tillägg ej tillgängligt: %s';

}
