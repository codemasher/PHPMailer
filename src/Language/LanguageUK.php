<?php
/**
 * Ukrainian PHPMailer language file
 *
 * @author Yuriy Rudyy <yrudyy@prs.net.ua>
 * @author Boris Yurchenko <boris@yurchenko.pp.ua>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageUK extends PHPMailerLanguageAbstract{

	protected $code        = 'uk';
	protected $name        = 'Ukrainian';
	protected $native_name = 'Українська';

	protected $authenticate         = 'Помилка SMTP: помилка авторизації.';
	protected $connect_host         = 'Помилка SMTP: не вдається під\'єднатися до серверу SMTP.';
	protected $data_not_accepted    = 'Помилка SMTP: дані не прийняті.';
	protected $encoding             = 'Невідомий тип кодування: %s';
	protected $execute              = 'Неможливо виконати команду: %s';
	protected $file_access          = 'Немає доступу до файлу: %s';
	protected $file_open            = 'Помилка файлової системи: не вдається відкрити файл: %s';
	protected $from_failed          = 'Невірна адреса відправника: %s';
	protected $instantiate          = 'Неможливо запустити функцію mail.';
	protected $provide_address      = 'Будь-ласка, введіть хоча б одну адресу e-mail отримувача.';
	protected $mailer_not_supported = ' - поштовий сервер не підтримується.';
	protected $recipients_failed    = 'Помилка SMTP: відправлення наступним отримувачам не вдалося: %s';
	protected $empty_message        = 'Пусте тіло повідомлення';
	protected $invalid_address      = 'Не відправлено, невірний формат адреси e-mail (%1$s): %2$s';
	protected $signing              = 'Помилка підпису: %s';
	protected $smtp_connect_failed  = 'Помилка з\'єднання із SMTP-сервером';
	protected $smtp_error           = 'Помилка SMTP-сервера: ';
	protected $variable_set         = 'Неможливо встановити або перевстановити змінну: ';
	protected $extension_missing    = 'Не знайдено розширення: %s';

}
