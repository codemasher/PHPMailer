<?php
/**
 * Polish PHPMailer language file
 */

namespace PHPMailer\PHPMailer\Language;

class LanguagePL extends PHPMailerLanguageAbstract{

	protected $code        = 'pl';
	protected $name        = 'Polish';
	protected $native_name = 'język polski';

	protected $authenticate         = 'Błąd SMTP: Nie można przeprowadzić uwierzytelnienia.';
	protected $connect_host         = 'Błąd SMTP: Nie można połączyć się z wybranym hostem.';
	protected $data_not_accepted    = 'Błąd SMTP: Dane nie zostały przyjęte.';
	protected $empty_message        = 'Wiadomość jest pusta.';
	protected $encoding             = 'Nieznany sposób kodowania znaków: %s';
	protected $execute              = 'Nie można uruchomić: %s';
	protected $file_access          = 'Brak dostępu do pliku: %s';
	protected $file_open            = 'Nie można otworzyć pliku: %s';
	protected $from_failed          = 'Następujący adres Nadawcy jest nieprawidłowy: %s';
	protected $instantiate          = 'Nie można wywołać funkcji mail(). Sprawdź konfigurację serwera.';
	protected $invalid_address      = 'Nie można wysłać wiadomości, następujący adres Odbiorcy jest nieprawidłowy (%1$s): %2$s';
	protected $provide_address      = 'Należy podać prawidłowy adres email Odbiorcy.';
	protected $mailer_not_supported = 'Wybrana metoda wysyłki wiadomości nie jest obsługiwana.';
	protected $recipients_failed    = 'Błąd SMTP: Następujący odbiorcy są nieprawidłowi: %s';
	protected $signing              = 'Błąd podpisywania wiadomości: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() zakończone niepowodzeniem.';
	protected $smtp_error           = 'Błąd SMTP: ';
	protected $variable_set         = 'Nie można ustawić lub zmodyfikować zmiennej: ';
	protected $extension_missing    = 'Brakujące rozszerzenie: %s';

}
