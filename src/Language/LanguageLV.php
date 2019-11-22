<?php
/**
 * Latvian PHPMailer language file
 *
 * @author Eduards M. <e@npd.lv>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageLV extends PHPMailerLanguageAbstract{

	protected $code        = 'lv';
	protected $name        = 'Latvian';
	protected $native_name = 'latviešu valoda';

	protected $authenticate         = 'SMTP kļūda: Autorizācija neizdevās.';
	protected $connect_host         = 'SMTP Kļūda: Nevar izveidot savienojumu ar SMTP serveri.';
	protected $data_not_accepted    = 'SMTP Kļūda: Nepieņem informāciju.';
	protected $empty_message        = 'Ziņojuma teksts ir tukšs';
	protected $encoding             = 'Neatpazīts kodējums: %s';
	protected $execute              = 'Neizdevās izpildīt komandu: %s';
	protected $file_access          = 'Fails nav pieejams: %s';
	protected $file_open            = 'Faila kļūda: Nevar atvērt failu: %s';
	protected $from_failed          = 'Nepareiza sūtītāja adrese: %s';
	protected $instantiate          = 'Nevar palaist sūtīšanas funkciju.';
	protected $invalid_address      = 'Nepareiza adrese (%1$s): %2$s';
	protected $mailer_not_supported = ' sūtītājs netiek atbalstīts.';
	protected $provide_address      = 'Lūdzu, norādiet vismaz vienu adresātu.';
	protected $recipients_failed    = 'SMTP kļūda: neizdevās nosūtīt šādiem saņēmējiem: %s';
	protected $signing              = 'Autorizācijas kļūda: %s';
	protected $smtp_connect_failed  = 'SMTP savienojuma kļūda';
	protected $smtp_error           = 'SMTP servera kļūda: ';
	protected $variable_set         = 'Nevar piešķirt mainīgā vērtību: ';

}
