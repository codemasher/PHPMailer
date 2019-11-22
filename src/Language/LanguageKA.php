<?php
/**
 * Georgian PHPMailer language file
 *
 * @author Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageKA extends PHPMailerLanguageAbstract{

	protected $code        = 'ka';
	protected $name        = 'Georgian';
	protected $native_name = 'ქართული';

	protected $authenticate         = 'SMTP შეცდომა: ავტორიზაცია შეუძლებელია.';
	protected $connect_host         = 'SMTP შეცდომა: SMTP სერვერთან დაკავშირება შეუძლებელია.';
	protected $data_not_accepted    = 'SMTP შეცდომა: მონაცემები არ იქნა მიღებული.';
	protected $encoding             = 'კოდირების უცნობი ტიპი: %s';
	protected $execute              = 'შეუძლებელია შემდეგი ბრძანების შესრულება: %s';
	protected $file_access          = 'შეუძლებელია წვდომა ფაილთან: %s';
	protected $file_open            = 'ფაილური სისტემის შეცდომა: არ იხსნება ფაილი: %s';
	protected $from_failed          = 'გამგზავნის არასწორი მისამართი: %s';
	protected $instantiate          = 'mail ფუნქციის გაშვება ვერ ხერხდება.';
	protected $provide_address      = 'გთხოვთ მიუთითოთ ერთი ადრესატის e-mail მისამართი მაინც.';
	protected $mailer_not_supported = ' - საფოსტო სერვერის მხარდაჭერა არ არის.';
	protected $recipients_failed    = 'SMTP შეცდომა: შემდეგ მისამართებზე გაგზავნა ვერ მოხერხდა: %s';
	protected $empty_message        = 'შეტყობინება ცარიელია';
	protected $invalid_address      = 'არ გაიგზავნა, e-mail მისამართის არასწორი ფორმატი (%1$s): %2$s';
	protected $signing              = 'ხელმოწერის შეცდომა: %s';
	protected $smtp_connect_failed  = 'შეცდომა SMTP სერვერთან დაკავშირებისას';
	protected $smtp_error           = 'SMTP სერვერის შეცდომა: ';
	protected $variable_set         = 'შეუძლებელია შემდეგი ცვლადის შექმნა ან შეცვლა: ';
	protected $extension_missing    = 'ბიბლიოთეკა არ არსებობს: %s';

}
