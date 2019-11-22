<?php
/**
 * Hebrew PHPMailer language file
 *
 * @author Ronny Sherer <ronny@hoojima.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageHE extends PHPMailerLanguageAbstract{

	protected $code        = 'he';
	protected $dir         = 'RTL';
	protected $name        = 'Hebrew';
	protected $native_name = 'עברית';

	protected $authenticate         = 'שגיאת SMTP: פעולת האימות נכשלה.';
	protected $connect_host         = 'שגיאת SMTP: לא הצלחתי להתחבר לשרת SMTP.';
	protected $data_not_accepted    = 'שגיאת SMTP: מידע לא התקבל.';
	protected $empty_message        = 'גוף ההודעה ריק';
	protected $invalid_address      = '%2$s :(%1$s) כתובת שגויה';
	protected $encoding             = '%s :קידוד לא מוכר';
	protected $execute              = 'לא הצלחתי להפעיל את: s%';
	protected $file_access          = 'לא ניתן לגשת לקובץ: s%';
	protected $file_open            = 'שגיאת קובץ: לא ניתן לגשת לקובץ: s%';
	protected $from_failed          = 'כתובות הנמענים הבאות נכשלו: s%';
	protected $instantiate          = 'לא הצלחתי להפעיל את פונקציית המייל.';
	protected $mailer_not_supported = ' אינה נתמכת.';
	protected $provide_address      = 'חובה לספק לפחות כתובת אחת של מקבל המייל.';
	protected $recipients_failed    = 'שגיאת SMTP: הנמענים הבאים נכשלו: s%';
	protected $signing              = 'שגיאת חתימה: s%';
	protected $smtp_error           = 'שגיאת שרת SMTP: ';
	protected $variable_set         = 'לא ניתן לקבוע או לשנות את המשתנה: ';

}
