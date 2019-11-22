<?php
/**
 * Catalan PHPMailer language file
 *
 * @author Ivan <web AT microstudi DOT com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageCA extends PHPMailerLanguageAbstract{

	protected $code        = 'ca';
	protected $name        = 'Catalan';
	protected $native_name = 'català';

	protected $authenticate         = 'Error SMTP: No s’ha pogut autenticar.';
	protected $connect_host         = 'Error SMTP: No es pot connectar al servidor SMTP.';
	protected $data_not_accepted    = 'Error SMTP: Dades no acceptades.';
	protected $empty_message        = 'El cos del missatge està buit.';
	protected $encoding             = 'Codificació desconeguda: %s';
	protected $execute              = 'No es pot executar: %s';
	protected $file_access          = 'No es pot accedir a l’arxiu: %s';
	protected $file_open            = 'Error d’Arxiu: No es pot obrir l’arxiu: %s';
	protected $from_failed          = 'La(s) següent(s) adreces de remitent han fallat: %s';
	protected $instantiate          = 'No s’ha pogut crear una instància de la funció Mail.';
	protected $invalid_address      = 'Adreça d’email invalida (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer no està suportat';
	protected $provide_address      = 'S’ha de proveir almenys una adreça d’email com a destinatari.';
	protected $recipients_failed    = 'Error SMTP: Els següents destinataris han fallat: %s';
	protected $signing              = 'Error al signar: %s';
	protected $smtp_connect_failed  = 'Ha fallat el SMTP Connect().';
	protected $smtp_error           = 'Error del servidor SMTP: ';
	protected $variable_set         = 'No s’ha pogut establir o restablir la variable: ';

}
