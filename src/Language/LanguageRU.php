<?php
/**
 * Russian PHPMailer language file
 *
 * @author Alexey Chumakov <alex@chumakov.ru>
 * @author Foster Snowhill <i18n@forstwoof.ru>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageRU extends PHPMailerLanguageAbstract{

	protected $code        = 'ru';
	protected $name        = 'Russian';
	protected $native_name = 'русский';

	protected $authenticate         = 'Ошибка SMTP: ошибка авторизации.';
	protected $connect_host         = 'Ошибка SMTP: не удается подключиться к серверу SMTP.';
	protected $data_not_accepted    = 'Ошибка SMTP: данные не приняты.';
	protected $encoding             = 'Неизвестный вид кодировки: %s';
	protected $execute              = 'Невозможно выполнить команду: %s';
	protected $file_access          = 'Нет доступа к файлу: %s';
	protected $file_open            = 'Файловая ошибка: не удается открыть файл: %s';
	protected $from_failed          = 'Неверный адрес отправителя: %s';
	protected $instantiate          = 'Невозможно запустить функцию mail.';
	protected $provide_address      = 'Пожалуйста, введите хотя бы один адрес e-mail получателя.';
	protected $mailer_not_supported = ' — почтовый сервер не поддерживается.';
	protected $recipients_failed    = 'Ошибка SMTP: отправка по следующим адресам получателей не удалась: %s';
	protected $empty_message        = 'Пустое сообщение';
	protected $invalid_address      = 'Не отослано, неправильный формат email адреса (%1$s): %2$s';
	protected $signing              = 'Ошибка подписи: %s';
	protected $smtp_connect_failed  = 'Ошибка соединения с SMTP-сервером';
	protected $smtp_error           = 'Ошибка SMTP-сервера: ';
	protected $variable_set         = 'Невозможно установить или переустановить переменную: ';
	protected $extension_missing    = 'Расширение отсутствует: %s';

}
