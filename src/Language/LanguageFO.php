<?php
/**
 * Faroese PHPMailer language file
 *
 * @author Dávur Sørensen <http://www.profo-webdesign.dk>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageFO extends PHPMailerLanguageAbstract{

	protected $code        = 'fo';
	protected $name        = 'Faroese';
	protected $native_name = 'føroyskt';

	protected $authenticate         = 'SMTP feilur: Kundi ikki góðkenna.';
	protected $connect_host         = 'SMTP feilur: Kundi ikki knýta samband við SMTP vert.';
	protected $data_not_accepted    = 'SMTP feilur: Data ikki góðkent.';
	protected $encoding             = 'Ókend encoding: %s';
	protected $execute              = 'Kundi ikki útføra: %s';
	protected $file_access          = 'Kundi ikki tilganga fílu: %s';
	protected $file_open            = 'Fílu feilur: Kundi ikki opna fílu: %s';
	protected $from_failed          = 'fylgjandi Frá/From adressa miseydnaðist: %s';
	protected $instantiate          = 'Kuni ikki instantiera mail funktión.';
	protected $mailer_not_supported = ' er ikki supporterað.';
	protected $provide_address      = 'Tú skal uppgeva minst móttakara-emailadressu(r).';
	protected $recipients_failed    = 'SMTP Feilur: Fylgjandi móttakarar miseydnaðust: %s';

}
