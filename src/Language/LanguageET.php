<?php
/**
 * Estonian PHPMailer language file
 *
 * @author Indrek Päri
 * @author Elan Ruusamäe <glen@delfi.ee>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageET extends PHPMailerLanguageAbstract{

	protected $code        = 'et';
	protected $name        = 'Estonian';
	protected $native_name = 'eesti';

	protected $authenticate         = 'SMTP Viga: Autoriseerimise viga.';
	protected $connect_host         = 'SMTP Viga: Ei õnnestunud luua ühendust SMTP serveriga.';
	protected $data_not_accepted    = 'SMTP Viga: Vigased andmed.';
	protected $empty_message        = 'Tühi kirja sisu';
	protected $encoding             = 'Tundmatu kodeering: %s';
	protected $execute              = 'Tegevus ebaõnnestus: %s';
	protected $file_access          = 'Pole piisavalt õiguseid järgneva faili avamiseks: %s';
	protected $file_open            = 'Faili Viga: Faili avamine ebaõnnestus: %s';
	protected $from_failed          = 'Järgnev saatja e-posti aadress on vigane: %s';
	protected $instantiate          = 'mail funktiooni käivitamine ebaõnnestus.';
	protected $invalid_address      = 'Saatmine peatatud, e-posti address vigane (%1$s): %2$s';
	protected $provide_address      = 'Te peate määrama vähemalt ühe saaja e-posti aadressi.';
	protected $mailer_not_supported = ' maileri tugi puudub.';
	protected $recipients_failed    = 'SMTP Viga: Järgnevate saajate e-posti aadressid on vigased: %s';
	protected $signing              = 'Viga allkirjastamisel: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() ebaõnnestus.';
	protected $smtp_error           = 'SMTP serveri viga: ';
	protected $variable_set         = 'Ei õnnestunud määrata või lähtestada muutujat: ';
	protected $extension_missing    = 'Nõutud laiendus on puudu: %s';

}
