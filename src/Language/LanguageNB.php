<?php
/**
 * Norwegian Bokmål PHPMailer language file
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageNB extends PHPMailerLanguageAbstract{

	protected $code        = 'nb';
	protected $name        = 'Norwegian Bokmål';
	protected $native_name = 'Norsk Bokmål';

	protected $authenticate         = 'SMTP Feil: Kunne ikke autentisere.';
	protected $connect_host         = 'SMTP Feil: Kunne ikke koble til SMTP tjener.';
	protected $data_not_accepted    = 'SMTP Feil: Datainnhold ikke akseptert.';
	protected $empty_message        = 'Meldingsinnhold mangler';
	protected $encoding             = 'Ukjent koding: %s';
	protected $execute              = 'Kunne ikke utføre: %s';
	protected $file_access          = 'Får ikke tilgang til filen: %s';
	protected $file_open            = 'Fil Feil: Kunne ikke åpne filen: %s';
	protected $from_failed          = 'Følgende Frå adresse feilet: %s';
	protected $instantiate          = 'Kunne ikke initialisere post funksjon.';
	protected $invalid_address      = 'Ugyldig adresse (%1$s): %2$s';
	protected $mailer_not_supported = ' sender er ikke støttet.';
	protected $provide_address      = 'Du må opppgi minst en mottakeradresse.';
	protected $recipients_failed    = 'SMTP Feil: Følgende mottakeradresse feilet: %s';
	protected $signing              = 'Signering Feil: %s';
	protected $smtp_connect_failed  = 'SMTP connect() feilet.';
	protected $smtp_error           = 'SMTP server feil: ';
	protected $variable_set         = 'Kan ikke skrive eller omskrive variabel: ';
	protected $extension_missing    = 'Utvidelse mangler: %s';

}
