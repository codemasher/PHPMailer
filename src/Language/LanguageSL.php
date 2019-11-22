<?php
/**
 * Slovenian PHPMailer language file
 *
 * @author Klemen Tušar <techouse@gmail.com>
 * @author Filip Š <projects@filips.si>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageSL extends PHPMailerLanguageAbstract{

	protected $code        = 'sl';
	protected $name        = 'Slovenian';
	protected $native_name = 'Slovenski Jezik';

	protected $authenticate         = 'SMTP napaka: Avtentikacija ni uspela.';
	protected $connect_host         = 'SMTP napaka: Vzpostavljanje povezave s SMTP gostiteljem ni uspelo.';
	protected $data_not_accepted    = 'SMTP napaka: Strežnik zavrača podatke.';
	protected $empty_message        = 'E-poštno sporočilo nima vsebine.';
	protected $encoding             = 'Nepoznan tip kodiranja: %s';
	protected $execute              = 'Operacija ni uspela: %s';
	protected $file_access          = 'Nimam dostopa do datoteke: %s';
	protected $file_open            = 'Ne morem odpreti datoteke: %s';
	protected $from_failed          = 'Neveljaven e-naslov pošiljatelja: %s';
	protected $instantiate          = 'Ne morem inicializirati mail funkcije.';
	protected $invalid_address      = 'E-poštno sporočilo ni bilo poslano. E-naslov je neveljaven (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer ni podprt.';
	protected $provide_address      = 'Prosim vnesite vsaj enega naslovnika.';
	protected $recipients_failed    = 'SMTP napaka: Sledeči naslovniki so neveljavni: %s';
	protected $signing              = 'Napaka pri podpisovanju: %s';
	protected $smtp_connect_failed  = 'Ne morem vzpostaviti povezave s SMTP strežnikom.';
	protected $smtp_error           = 'Napaka SMTP strežnika: ';
	protected $variable_set         = 'Ne morem nastaviti oz. ponastaviti spremenljivke: ';
	protected $extension_missing    = 'Manjkajoča razširitev: %s';

}
