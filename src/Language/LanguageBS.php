<?php
/**
 * Bosnian PHPMailer language file
 *
 * @author Ermin Islamagić <ermin@islamagic.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageBS extends PHPMailerLanguageAbstract{

	protected $code        = 'bs';
	protected $name        = 'Bosnian';
	protected $native_name = 'bosanski jezik';

	protected $authenticate         = 'SMTP Greška: Neuspjela prijava.';
	protected $connect_host         = 'SMTP Greška: Nije moguće spojiti se sa SMTP serverom.';
	protected $data_not_accepted    = 'SMTP Greška: Podatci nisu prihvaćeni.';
	protected $empty_message        = 'Sadržaj poruke je prazan.';
	protected $encoding             = 'Nepoznata kriptografija: %s';
	protected $execute              = 'Nije moguće izvršiti naredbu: %s';
	protected $file_access          = 'Nije moguće pristupiti datoteci: %s';
	protected $file_open            = 'Nije moguće otvoriti datoteku: %s';
	protected $from_failed          = 'SMTP Greška: Slanje sa navedenih e-mail adresa nije uspjelo: %s';
	protected $recipients_failed    = 'SMTP Greška: Slanje na navedene e-mail adrese nije uspjelo: %s';
	protected $instantiate          = 'Ne mogu pokrenuti mail funkcionalnost.';
	protected $invalid_address      = 'E-mail nije poslan. Neispravna e-mail adresa (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer nije podržan.';
	protected $provide_address      = 'Definišite barem jednu adresu primaoca.';
	protected $signing              = 'Greška prilikom prijave: %s';
	protected $smtp_connect_failed  = 'Spajanje na SMTP server nije uspjelo.';
	protected $smtp_error           = 'SMTP greška: ';
	protected $variable_set         = 'Nije moguće postaviti varijablu ili je vratiti nazad: ';
	protected $extension_missing    = 'Nedostaje ekstenzija: %s';

}
