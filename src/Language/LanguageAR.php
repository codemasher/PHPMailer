<?php
/**
 * Arabic PHPMailer language file
 *
 * @author bahjat al mostafa <bahjat983@hotmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageAR extends PHPMailerLanguageAbstract{

	protected $code        = 'ar';
	protected $dir         = 'RTL';
	protected $name        = 'Arabic';
	protected $native_name = 'العربية';

	protected $authenticate         = 'خطأ SMTP : لا يمكن تأكيد الهوية.';
	protected $connect_host         = 'خطأ SMTP: لا يمكن الاتصال بالخادم SMTP.';
	protected $data_not_accepted    = 'خطأ SMTP: لم يتم قبول المعلومات .';
	protected $empty_message        = 'نص الرسالة فارغ';
	protected $encoding             = 'ترميز غير معروف: s%';
	protected $execute              = 'لا يمكن تنفيذ : s%';
	protected $file_access          = 'لا يمكن الوصول للملف: s%';
	protected $file_open            = 'خطأ في الملف: لا يمكن فتحه: s%';
	protected $from_failed          = 'خطأ على مستوى عنوان المرسل : s%';
	protected $instantiate          = 'لا يمكن توفير خدمة البريد.';
	protected $invalid_address      = '%2$s :(%1$s) الإرسال غير ممكن لأن عنوان البريد الإلكتروني غير صالح';
	protected $mailer_not_supported = ' برنامج الإرسال غير مدعوم.';
	protected $provide_address      = 'يجب توفير عنوان البريد الإلكتروني لمستلم واحد على الأقل.';
	protected $recipients_failed    = 'خطأ SMTP: الأخطاء التالية فشل في الارسال لكل من : s%';
	protected $signing              = 'خطأ في التوقيع: s%';
	protected $smtp_connect_failed  = 'SMTP Connect() غير ممكن.';
	protected $smtp_error           = 'خطأ على مستوى الخادم SMTP: ';
	protected $variable_set         = 'لا يمكن تعيين أو إعادة تعيين متغير: ';
	protected $extension_missing    = 'الإضافة غير موجودة: s%';

}
