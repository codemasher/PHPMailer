<?php
/**
 * Esperanto PHPMailer language file
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageEO extends PHPMailerLanguageAbstract{

	protected $code        = 'eo';
	protected $name        = 'Esperanto';
	protected $native_name = 'Esperanto';

	protected $authenticate         = 'Eraro de servilo SMTP : aŭtentigo malsukcesis.';
	protected $connect_host         = 'Eraro de servilo SMTP : konektado al servilo malsukcesis.';
	protected $data_not_accepted    = 'Eraro de servilo SMTP : neĝustaj datumoj.';
	protected $empty_message        = 'Teksto de mesaĝo mankas.';
	protected $encoding             = 'Nekonata kodoprezento: %s';
	protected $execute              = 'Lanĉi rulumadon ne eblis: %s';
	protected $file_access          = 'Aliro al dosiero ne sukcesis: %s';
	protected $file_open            = 'Eraro de dosiero: malfermo neeblas: %s';
	protected $from_failed          = 'Jena adreso de sendinto malsukcesis: %s';
	protected $instantiate          = 'Genero de retmesaĝa funkcio neeblis.';
	protected $invalid_address      = 'Retadreso ne validas (%1$s): %2$s';
	protected $mailer_not_supported = ' mesaĝilo ne subtenata.';
	protected $provide_address      = 'Vi devas tajpi almenaŭ unu recevontan retadreson.';
	protected $recipients_failed    = 'Eraro de servilo SMTP : la jenaj poŝtrecivuloj kaŭzis eraron: %s';
	protected $signing              = 'Eraro de subskribo: %s';
	protected $smtp_connect_failed  = 'SMTP konektado malsukcesis.';
	protected $smtp_error           = 'Eraro de servilo SMTP : ';
	protected $variable_set         = 'Variablo ne pravalorizeblas aŭ ne repravalorizeblas: ';
	protected $extension_missing    = 'Mankas etendo: %s';

}
