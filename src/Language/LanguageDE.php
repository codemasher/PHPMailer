<?php
/**
 * German PHPMailer language file
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageDE extends PHPMailerLanguageAbstract{

	protected $code        = 'de';
	protected $name        = 'German';
	protected $native_name = 'Deutsch';

	protected $authenticate         = 'SMTP-Fehler: Authentifizierung fehlgeschlagen.';
	protected $connect_host         = 'SMTP-Fehler: Konnte keine Verbindung zum SMTP-Host herstellen.';
	protected $data_not_accepted    = 'SMTP-Fehler: Daten werden nicht akzeptiert.';
	protected $empty_message        = 'E-Mail-Inhalt ist leer.';
	protected $encoding             = 'Unbekannte Kodierung: %s';
	protected $execute              = 'Konnte folgenden Befehl nicht ausführen: %s';
	protected $file_access          = 'Zugriff auf folgende Datei fehlgeschlagen: %s';
	protected $file_open            = 'Dateifehler: Konnte folgende Datei nicht öffnen: %s';
	protected $from_failed          = 'Die folgende Absenderadresse ist nicht korrekt: %s';
	protected $instantiate          = 'Mail-Funktion konnte nicht initialisiert werden.';
	protected $invalid_address      = 'Die Adresse ist ungültig (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer wird nicht unterstützt.';
	protected $provide_address      = 'Bitte geben Sie mindestens eine Empfängeradresse an.';
	protected $recipients_failed    = 'SMTP-Fehler: Die folgenden Empfänger sind nicht korrekt: %s';
	protected $signing              = 'Fehler beim Signieren: %s';
	protected $smtp_connect_failed  = 'Verbindung zum SMTP-Server fehlgeschlagen.';
	protected $smtp_error           = 'Fehler vom SMTP-Server: ';
	protected $variable_set         = 'Kann Variable nicht setzen oder zurücksetzen: ';
	protected $extension_missing    = 'Fehlende Erweiterung: %s';

}
