<?php
/**
 * Persian/Farsi PHPMailer language file
 *
 * @author Ali Jazayeri <jaza.ali@gmail.com>
 * @author Mohammad Hossein Mojtahedi <mhm5000@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageFA extends PHPMailerLanguageAbstract{

	protected $code        = 'fa';
	protected $dir         = 'RTL';
	protected $name        = 'Persian/Farsi';
	protected $native_name = 'فارسی';

	protected $authenticate         = 'خطای SMTP: احراز هویت با شکست مواجه شد.';
	protected $connect_host         = 'خطای SMTP: اتصال به سرور SMTP برقرار نشد.';
	protected $data_not_accepted    = 'خطای SMTP: داده‌ها نا‌درست هستند.';
	protected $empty_message        = 'بخش متن پیام خالی است.';
	protected $encoding             = 'کد‌گذاری نا‌شناخته: s%';
	protected $execute              = 'امکان اجرا وجود ندارد: s%';
	protected $file_access          = 'امکان دسترسی به فایل وجود ندارد: s%';
	protected $file_open            = 'خطای File: امکان بازکردن فایل وجود ندارد: s%';
	protected $from_failed          = 'آدرس فرستنده اشتباه است: s%';
	protected $instantiate          = 'امکان معرفی تابع ایمیل وجود ندارد.';
	protected $invalid_address      = '%2$s :(%1$s) آدرس ایمیل معتبر نیست';
	protected $mailer_not_supported = ' mailer پشتیبانی نمی‌شود.';
	protected $provide_address      = 'باید حداقل یک آدرس گیرنده وارد کنید.';
	protected $recipients_failed    = 'خطای SMTP: ارسال به آدرس گیرنده با خطا مواجه شد: s%';
	protected $signing              = 'خطا در امضا: s%';
	protected $smtp_connect_failed  = 'خطا در اتصال به SMTP.';
	protected $smtp_error           = 'خطا در SMTP Server: ';
	protected $variable_set         = 'امکان ارسال یا ارسال مجدد متغیر‌ها وجود ندارد: ';

}
