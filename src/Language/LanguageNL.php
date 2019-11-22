<?php
/**
 * Dutch PHPMailer language file: refer to PHPMailer.php for definitive list.
 *
 * @author Tuxion <team@tuxion.nl>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageNL extends PHPMailerLanguageAbstract{

	protected $code        = 'nl';
	protected $name        = 'Dutch';
	protected $native_name = 'Nederlands';

	protected $authenticate         = 'SMTP-fout: authenticatie mislukt.';
	protected $connect_host         = 'SMTP-fout: kon niet verbinden met SMTP-host.';
	protected $data_not_accepted    = 'SMTP-fout: data niet geaccepteerd.';
	protected $empty_message        = 'Berichttekst is leeg';
	protected $encoding             = 'Onbekende codering: %s';
	protected $execute              = 'Kon niet uitvoeren: %s';
	protected $file_access          = 'Kreeg geen toegang tot bestand: %s';
	protected $file_open            = 'Bestandsfout: kon bestand niet openen: %s';
	protected $from_failed          = 'Het volgende afzendersadres is mislukt: %s';
	protected $instantiate          = 'Kon mailfunctie niet initialiseren.';
	protected $invalid_address      = 'Ongeldig adres (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer wordt niet ondersteund.';
	protected $provide_address      = 'Er moet minstens één ontvanger worden opgegeven.';
	protected $recipients_failed    = 'SMTP-fout: de volgende ontvangers zijn mislukt: %s';
	protected $signing              = 'Signeerfout: %s';
	protected $smtp_connect_failed  = 'SMTP Verbinding mislukt.';
	protected $smtp_error           = 'SMTP-serverfout: ';
	protected $variable_set         = 'Kan de volgende variabele niet instellen of resetten: ';
	protected $extension_missing    = 'Extensie afwezig: %s';

}
