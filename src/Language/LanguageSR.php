<?php
/**
 * Serbian PHPMailer language file
 *
 * @author Александар Јевремовић <ajevremovic@gmail.com>
 * @author Miloš Milanović <mmilanovic016@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageSR extends PHPMailerLanguageAbstract{

	protected $code        = 'sr';
	protected $name        = 'Serbian';
	protected $native_name = 'српски језик';

	protected $authenticate         = 'SMTP грешка: аутентификација није успела.';
	protected $connect_host         = 'SMTP грешка: повезивање са SMTP сервером није успело.';
	protected $data_not_accepted    = 'SMTP грешка: подаци нису прихваћени.';
	protected $empty_message        = 'Садржај поруке је празан.';
	protected $encoding             = 'Непознато кодирање: %s';
	protected $execute              = 'Није могуће извршити наредбу: %s';
	protected $file_access          = 'Није могуће приступити датотеци: %s';
	protected $file_open            = 'Није могуће отворити датотеку: %s';
	protected $from_failed          = 'SMTP грешка: слање са следећих адреса није успело: %s';
	protected $recipients_failed    = 'SMTP грешка: слање на следеће адресе није успело: %s';
	protected $instantiate          = 'Није могуће покренути mail функцију.';
	protected $invalid_address      = 'Порука није послата. Неисправна адреса (%1$s): %2$s';
	protected $mailer_not_supported = ' мејлер није подржан.';
	protected $provide_address      = 'Дефинишите бар једну адресу примаоца.';
	protected $signing              = 'Грешка приликом пријаве: %s';
	protected $smtp_connect_failed  = 'Повезивање са SMTP сервером није успело.';
	protected $smtp_error           = 'Грешка SMTP сервера: ';
	protected $variable_set         = 'Није могуће задати нити ресетовати променљиву: ';
	protected $extension_missing    = 'Недостаје проширење: %s';

}
