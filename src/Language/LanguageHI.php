<?php
/**
 * Hindi PHPMailer language file
 *
 * @author Yash Karanke <mr.karanke@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageHI extends PHPMailerLanguageAbstract{

	protected $code        = 'hi';
	protected $name        = 'Hindi';
	protected $native_name = 'हिन्दी';

	protected $authenticate         = 'SMTP त्रुटि: प्रामाणिकता की जांच नहीं हो सका। ';
	protected $connect_host         = 'SMTP त्रुटि: SMTP सर्वर से कनेक्ट नहीं हो सका। ';
	protected $data_not_accepted    = 'SMTP त्रुटि: डेटा स्वीकार नहीं किया जाता है। ';
	protected $empty_message        = 'संदेश खाली है। ';
	protected $encoding             = 'अज्ञात एन्कोडिंग प्रकार। %s';
	protected $execute              = 'आदेश को निष्पादित करने में विफल। %s';
	protected $file_access          = 'फ़ाइल उपलब्ध नहीं है। %s';
	protected $file_open            = 'फ़ाइल त्रुटि: फाइल को खोला नहीं जा सका। %s';
	protected $from_failed          = 'प्रेषक का पता गलत है। %s';
	protected $instantiate          = 'मेल फ़ंक्शन कॉल नहीं कर सकता है।';
	protected $invalid_address      = 'पता गलत है। (%1$s): %2$s';
	protected $mailer_not_supported = 'मेल सर्वर के साथ काम नहीं करता है। ';
	protected $provide_address      = 'आपको कम से कम एक प्राप्तकर्ता का ई-मेल पता प्रदान करना होगा।';
	protected $recipients_failed    = 'SMTP त्रुटि: निम्न प्राप्तकर्ताओं को पते भेजने में विफल। %s';
	protected $signing              = 'साइनअप त्रुटि:। %s';
	protected $smtp_connect_failed  = 'SMTP का connect () फ़ंक्शन विफल हुआ। ';
	protected $smtp_error           = 'SMTP सर्वर त्रुटि। ';
	protected $variable_set         = 'चर को बना या संशोधित नहीं किया जा सकता। ';
	protected $extension_missing    = 'एक्सटेन्षन गायब है: %s';

}
