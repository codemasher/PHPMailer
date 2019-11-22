<?php
/**
 * Galician PHPMailer language file
 *
 * @author by Donato Rouco <donatorouco@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageGL extends PHPMailerLanguageAbstract{

	protected $code        = 'gl';
	protected $name        = 'Galician';
	protected $native_name = 'Galego';

	protected $authenticate         = 'Erro SMTP: Non puido ser autentificado.';
	protected $connect_host         = 'Erro SMTP: Non puido conectar co servidor SMTP.';
	protected $data_not_accepted    = 'Erro SMTP: Datos non aceptados.';
	protected $empty_message        = 'Corpo da mensaxe vacía';
	protected $encoding             = 'Codificación descoñecida: %s';
	protected $execute              = 'Non puido ser executado: %s';
	protected $file_access          = 'Nob puido acceder ó arquivo: %s';
	protected $file_open            = 'Erro de Arquivo: No puido abrir o arquivo: %s';
	protected $from_failed          = 'A(s) seguinte(s) dirección(s) de remitente(s) deron erro: %s';
	protected $instantiate          = 'Non puido crear unha instancia da función Mail.';
	protected $invalid_address      = 'Non puido envia-lo correo: dirección de email inválida (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer non está soportado.';
	protected $provide_address      = 'Debe engadir polo menos unha dirección de email coma destino.';
	protected $recipients_failed    = 'Erro SMTP: Os seguintes destinos fallaron: %s';
	protected $signing              = 'Erro ó firmar: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() fallou.';
	protected $smtp_error           = 'Erro do servidor SMTP: ';
	protected $variable_set         = 'Non puidemos axustar ou reaxustar a variábel: ';

}
