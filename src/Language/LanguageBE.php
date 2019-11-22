<?php
/**
 * Belarusian PHPMailer language file
 *
 * @author Aleksander Maksymiuk <info@setpro.pl>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageBE extends PHPMailerLanguageAbstract{

	protected $code        = 'be';
	protected $name        = 'Belarusian';
	protected $native_name = 'беларуская мова';

	protected $authenticate         = 'Памылка SMTP: памылка ідэнтыфікацыі.';
	protected $connect_host         = 'Памылка SMTP: нельга ўстанавіць сувязь з SMTP-серверам.';
	protected $data_not_accepted    = 'Памылка SMTP: звесткі непрынятыя.';
	protected $empty_message        = 'Пустое паведамленне.';
	protected $encoding             = 'Невядомая кадыроўка тэксту: %s';
	protected $execute              = 'Нельга выканаць каманду: %s';
	protected $file_access          = 'Няма доступу да файла: %s';
	protected $file_open            = 'Нельга адкрыць файл: %s';
	protected $from_failed          = 'Няправільны адрас адпраўніка: %s';
	protected $instantiate          = 'Нельга прымяніць функцыю mail().';
	protected $invalid_address      = 'Нельга даслаць паведамленне, няправільны email атрымальніка (%1$s): %2$s';
	protected $provide_address      = 'Запоўніце, калі ласка, правільны email атрымальніка.';
	protected $mailer_not_supported = ' - паштовы сервер не падтрымліваецца.';
	protected $recipients_failed    = 'Памылка SMTP: няправільныя атрымальнікі: %s';
	protected $signing              = 'Памылка подпісу паведамлення: %s';
	protected $smtp_connect_failed  = 'Памылка сувязі з SMTP-серверам.';
	protected $smtp_error           = 'Памылка SMTP: ';
	protected $variable_set         = 'Нельга ўстанавіць або перамяніць значэнне пераменнай: ';

}
