<?php
/**
 * Danish PHPMailer language file
 *
 * @author Mikael Stokkebro <info@stokkebro.dk>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageDA extends PHPMailerLanguageAbstract{

	protected $code        = 'da';
	protected $name        = 'Danish';
	protected $native_name = 'dansk';

	protected $authenticate         = 'SMTP fejl: Kunne ikke logge på.';
	protected $connect_host         = 'SMTP fejl: Kunne ikke tilslutte SMTP serveren.';
	protected $data_not_accepted    = 'SMTP fejl: Data kunne ikke accepteres.';
	protected $encoding             = 'Ukendt encode-format: %s';
	protected $execute              = 'Kunne ikke køre: %s';
	protected $file_access          = 'Ingen adgang til fil: %s';
	protected $file_open            = 'Fil fejl: Kunne ikke åbne filen: %s';
	protected $from_failed          = 'Følgende afsenderadresse er forkert: %s';
	protected $instantiate          = 'Kunne ikke initialisere email funktionen.';
	protected $mailer_not_supported = ' mailer understøttes ikke.';
	protected $provide_address      = 'Du skal indtaste mindst en modtagers emailadresse.';
	protected $recipients_failed    = 'SMTP fejl: Følgende modtagere er forkerte: %s';

}
