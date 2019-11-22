<?php
/**
 * Spanish PHPMailer language file
 *
 * @author Matt Sturdy <matt.sturdy@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageES extends PHPMailerLanguageAbstract{

	protected $code        = 'es';
	protected $name        = 'Spanish';
	protected $native_name = 'Español';

	protected $authenticate         = 'Error SMTP: Imposible autentificar.';
	protected $connect_host         = 'Error SMTP: Imposible conectar al servidor SMTP.';
	protected $data_not_accepted    = 'Error SMTP: Datos no aceptados.';
	protected $empty_message        = 'El cuerpo del mensaje está vacío.';
	protected $encoding             = 'Codificación desconocida: %s';
	protected $execute              = 'Imposible ejecutar: %s';
	protected $file_access          = 'Imposible acceder al archivo: %s';
	protected $file_open            = 'Error de Archivo: Imposible abrir el archivo: %s';
	protected $from_failed          = 'La(s) siguiente(s) direcciones de remitente fallaron: %s';
	protected $instantiate          = 'Imposible crear una instancia de la función Mail.';
	protected $invalid_address      = 'Imposible enviar: dirección de email inválido (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer no está soportado.';
	protected $provide_address      = 'Debe proporcionar al menos una dirección de email de destino.';
	protected $recipients_failed    = 'Error SMTP: Los siguientes destinos fallaron: %s';
	protected $signing              = 'Error al firmar: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() falló.';
	protected $smtp_error           = 'Error del servidor SMTP: ';
	protected $variable_set         = 'No se pudo configurar la variable: ';
	protected $extension_missing    = 'Extensión faltante: %s';

}
