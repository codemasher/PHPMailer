<?php
/**
 * Malagasy PHPMailer language file
 *
 * @author Hackinet <piyushjha8164@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageMG extends PHPMailerLanguageAbstract{

	protected $code        = 'mg';
	protected $name        = 'Malagasy';
	protected $native_name = 'fiteny malagasy';

	protected $authenticate         = 'Hadisoana SMTP: Tsy nahomby ny fanamarinana.';
	protected $connect_host         = 'SMTP Error: Tsy afaka mampifandray amin\'ny mpampiantrano SMTP.';
	protected $data_not_accepted    = 'SMTP diso: tsy voarakitra ny angona.';
	protected $empty_message        = 'Tsy misy ny votoaty mailaka.';
	protected $encoding             = 'Tsy fantatra encoding: %s';
	protected $execute              = 'Tsy afaka manatanteraka ity baiko manaraka ity: %s';
	protected $file_access          = 'Tsy nahomby ny fidirana amin\'ity rakitra ity: %s';
	protected $file_open            = 'Hadisoana diso: Tsy afaka nanokatra ity file manaraka ity: %s';
	protected $from_failed          = 'Ny adiresy iraka manaraka dia diso: %s';
	protected $instantiate          = 'Tsy afaka nanomboka ny hetsika mail.';
	protected $invalid_address      = 'Tsy mety ny adiresy (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer tsy manohana.';
	protected $provide_address      = 'Alefaso azafady iray adiresy iray farafahakeliny.';
	protected $recipients_failed    = 'SMTP Error: Tsy mety ireo mpanaraka ireto: %s';
	protected $signing              = 'Error nandritra ny sonia: %s';
	protected $smtp_connect_failed  = 'Tsy nahomby ny fifandraisana tamin\'ny server SMTP.';
	protected $smtp_error           = 'Fahadisoana tamin\'ny server SMTP: ';
	protected $variable_set         = 'Tsy azo atao ny mametraka na mamerina ny variable: ';
	protected $extension_missing    = 'Tsy hita ny ampahany: %s';

}
