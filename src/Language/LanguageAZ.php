<?php
/**
 * Azerbaijani PHPMailer language file
 *
 * @author @mirjalal
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageAZ extends PHPMailerLanguageAbstract{

	protected $code        = 'az';
	protected $name        = 'Azerbaijani';
	protected $native_name = 'azərbaycan dili';

	protected $authenticate         = 'SMTP xətası: Giriş uğursuz oldu.';
	protected $connect_host         = 'SMTP xətası: SMTP serverinə qoşulma uğursuz oldu.';
	protected $data_not_accepted    = 'SMTP xətası: Verilənlər qəbul edilməyib.';
	protected $empty_message        = 'Boş mesaj göndərilə bilməz.';
	protected $encoding             = 'Qeyri-müəyyən kodlaşdırma: %s';
	protected $execute              = 'Əmr yerinə yetirilmədi: %s';
	protected $file_access          = 'Fayla giriş yoxdur: %s';
	protected $file_open            = 'Fayl xətası: Fayl açıla bilmədi: %s';
	protected $from_failed          = 'Göstərilən poçtlara göndərmə uğursuz oldu: %s';
	protected $instantiate          = 'Mail funksiyası işə salına bilmədi.';
	protected $invalid_address      = 'Düzgün olmayan e-mail adresi (%1$s): %2$s';
	protected $mailer_not_supported = ' - e-mail kitabxanası dəstəklənmir.';
	protected $provide_address      = 'Ən azı bir e-mail adresi daxil edilməlidir.';
	protected $recipients_failed    = 'SMTP xətası: Aşağıdakı ünvanlar üzrə alıcılara göndərmə uğursuzdur: %s';
	protected $signing              = 'İmzalama xətası: %s';
	protected $smtp_connect_failed  = 'SMTP serverinə qoşulma uğursuz oldu.';
	protected $smtp_error           = 'SMTP serveri xətası: ';
	protected $variable_set         = 'Dəyişənin quraşdırılması uğursuz oldu: ';

}
