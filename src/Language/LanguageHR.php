<?php
/**
 * Croatian PHPMailer language file
 *
 * @author Hrvoj3e <hrvoj3e@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageHR extends PHPMailerLanguageAbstract{

	protected $code        = 'hr';
	protected $name        = 'Croatian';
	protected $native_name = 'hrvatski';

	protected $authenticate         = 'SMTP Greška: Neuspjela autentikacija.';
	protected $connect_host         = 'SMTP Greška: Ne mogu se spojiti na SMTP poslužitelj.';
	protected $data_not_accepted    = 'SMTP Greška: Podatci nisu prihvaćeni.';
	protected $empty_message        = 'Sadržaj poruke je prazan.';
	protected $encoding             = 'Nepoznati encoding: %s';
	protected $execute              = 'Nije moguće izvršiti naredbu: %s';
	protected $file_access          = 'Nije moguće pristupiti datoteci: %s';
	protected $file_open            = 'Nije moguće otvoriti datoteku: %s';
	protected $from_failed          = 'SMTP Greška: Slanje s navedenih e-mail adresa nije uspjelo: %s';
	protected $recipients_failed    = 'SMTP Greška: Slanje na navedenih e-mail adresa nije uspjelo: %s';
	protected $instantiate          = 'Ne mogu pokrenuti mail funkcionalnost.';
	protected $invalid_address      = 'E-mail nije poslan. Neispravna e-mail adresa (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer nije podržan.';
	protected $provide_address      = 'Definirajte barem jednu adresu primatelja.';
	protected $signing              = 'Greška prilikom prijave: %s';
	protected $smtp_connect_failed  = 'Spajanje na SMTP poslužitelj nije uspjelo.';
	protected $smtp_error           = 'Greška SMTP poslužitelja: ';
	protected $variable_set         = 'Ne mogu postaviti varijablu niti ju vratiti nazad: ';
	protected $extension_missing    = 'Nedostaje proširenje: %s';

}
