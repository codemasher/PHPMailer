<?php
/**
 * Romanian PHPMailer language file
 *
 * @author Alex Florea <alecz.fia@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageRO extends PHPMailerLanguageAbstract{

	protected $code        = 'ro';
	protected $name        = 'Romanian';
	protected $native_name = 'Română';

	protected $authenticate         = 'Eroare SMTP: Autentificarea a eșuat.';
	protected $connect_host         = 'Eroare SMTP: Conectarea la serverul SMTP a eșuat.';
	protected $data_not_accepted    = 'Eroare SMTP: Datele nu au fost acceptate.';
	protected $empty_message        = 'Mesajul este gol.';
	protected $encoding             = 'Encodare necunoscută: %s';
	protected $execute              = 'Nu se poate executa următoarea comandă: %s';
	protected $file_access          = 'Nu se poate accesa următorul fișier: %s';
	protected $file_open            = 'Eroare fișier: Nu se poate deschide următorul fișier: %s';
	protected $from_failed          = 'Următoarele adrese From au dat eroare: %s';
	protected $instantiate          = 'Funcția mail nu a putut fi inițializată.';
	protected $invalid_address      = 'Adresa de email nu este validă (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer nu este suportat.';
	protected $provide_address      = 'Trebuie să adăugați cel puțin o adresă de email.';
	protected $recipients_failed    = 'Eroare SMTP: Următoarele adrese de email au eșuat: %s';
	protected $signing              = 'A aparut o problemă la semnarea emailului. %s';
	protected $smtp_connect_failed  = 'Conectarea la serverul SMTP a eșuat.';
	protected $smtp_error           = 'Eroare server SMTP: ';
	protected $variable_set         = 'Nu se poate seta/reseta variabila. ';
	protected $extension_missing    = 'Lipsește extensia: %s';

}
