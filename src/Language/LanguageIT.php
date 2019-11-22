<?php
/**
 * Italian PHPMailer language file
 *
 * @author Ilias Bartolini <brain79@inwind.it>
 * @author Stefano Sabatini <sabas88@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageIT extends PHPMailerLanguageAbstract{

	protected $code        = 'it';
	protected $name        = 'Italian';
	protected $native_name = 'Italiano';

	protected $authenticate         = 'SMTP Error: Impossibile autenticarsi.';
	protected $connect_host         = 'SMTP Error: Impossibile connettersi all\'host SMTP.';
	protected $data_not_accepted    = 'SMTP Error: Dati non accettati dal server.';
	protected $empty_message        = 'Il corpo del messaggio è vuoto';
	protected $encoding             = 'Codifica dei caratteri sconosciuta: %s';
	protected $execute              = 'Impossibile eseguire l\'operazione: %s';
	protected $file_access          = 'Impossibile accedere al file: %s';
	protected $file_open            = 'File Error: Impossibile aprire il file: %s';
	protected $from_failed          = 'I seguenti indirizzi mittenti hanno generato errore: %s';
	protected $instantiate          = 'Impossibile istanziare la funzione mail';
	protected $invalid_address      = 'Impossibile inviare, l\'indirizzo email non è valido (%1$s): %2$s';
	protected $provide_address      = 'Deve essere fornito almeno un indirizzo ricevente';
	protected $mailer_not_supported = 'Mailer non supportato';
	protected $recipients_failed    = 'SMTP Error: I seguenti indirizzi destinatari hanno generato un errore: %s';
	protected $signing              = 'Errore nella firma: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() fallita.';
	protected $smtp_error           = 'Errore del server SMTP: ';
	protected $variable_set         = 'Impossibile impostare o resettare la variabile: ';
	protected $extension_missing    = 'Estensione mancante: %s';

}
