<?php
/**
 * Bulgarian PHPMailer language file
 *
 * @author Mikhail Kyosev <mialygk@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageBG extends PHPMailerLanguageAbstract{

	protected $code        = 'bg';
	protected $name        = 'Bulgarian';
	protected $native_name = 'български език';

	protected $authenticate         = 'SMTP грешка: Не може да се удостовери пред сървъра.';
	protected $connect_host         = 'SMTP грешка: Не може да се свърже с SMTP хоста.';
	protected $data_not_accepted    = 'SMTP грешка: данните не са приети.';
	protected $empty_message        = 'Съдържанието на съобщението е празно';
	protected $encoding             = 'Неизвестно кодиране: %s';
	protected $execute              = 'Не може да се изпълни: %s';
	protected $file_access          = 'Няма достъп до файл: %s';
	protected $file_open            = 'Файлова грешка: Не може да се отвори файл: %s';
	protected $from_failed          = 'Следните адреси за подател са невалидни: %s';
	protected $instantiate          = 'Не може да се инстанцира функцията mail.';
	protected $invalid_address      = 'Невалиден адрес (%1$s): %2$s';
	protected $mailer_not_supported = ' - пощенски сървър не се поддържа.';
	protected $provide_address      = 'Трябва да предоставите поне един email адрес за получател.';
	protected $recipients_failed    = 'SMTP грешка: Следните адреси за Получател са невалидни: %s';
	protected $signing              = 'Грешка при подписване: %s';
	protected $smtp_connect_failed  = 'SMTP провален connect().';
	protected $smtp_error           = 'SMTP сървърна грешка: ';
	protected $variable_set         = 'Не може да се установи или възстанови променлива: ';
	protected $extension_missing    = 'Липсва разширение: %s';

}
