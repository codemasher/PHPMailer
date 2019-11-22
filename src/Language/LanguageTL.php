<?php
/**
 * Tagalog PHPMailer language file
 *
 * @author Adriane Justine Tan <adrianetan12@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageTL extends PHPMailerLanguageAbstract{

	protected $code        = 'tl';
	protected $name        = 'Tagalog';
	protected $native_name = 'Wikang Tagalog';

	protected $authenticate         = 'SMTP Error: Hindi mapatotohanan.';
	protected $connect_host         = 'SMTP Error: Hindi makakonekta sa SMTP host.';
	protected $data_not_accepted    = 'SMTP Error: Ang datos ay hindi maaaring matatanggap.';
	protected $empty_message        = 'Walang laman ang mensahe';
	protected $encoding             = 'Hindi alam ang encoding: %s';
	protected $execute              = 'Hindi maisasagawa: %s';
	protected $file_access          = 'Hindi ma-access ang file: %s';
	protected $file_open            = 'Hindi mabuksan ang file: %s';
	protected $from_failed          = 'Ang sumusunod na address ay nabigo: %s';
	protected $instantiate          = 'Hindi maaaring magbigay ng institusyon ang mail';
	protected $invalid_address      = 'Hindi wasto ang address na naibigay (%1$s): %2$s';
	protected $mailer_not_supported = 'Ang mailer ay hindi suportado';
	protected $provide_address      = 'Kailangan mong magbigay ng kahit isang email address na tatanggap';
	protected $recipients_failed    = 'SMTP Error: Ang mga sumusunod na tatanggap ay nabigo: %s';
	protected $signing              = 'Hindi ma-sign: %s';
	protected $smtp_connect_failed  = 'Ang SMTP connect() ay nabigo';
	protected $smtp_error           = 'Ang server ng SMTP ay nabigo';
	protected $variable_set         = 'Hindi matatakda ang mga variables: ';
	protected $extension_missing    = 'Nawawala ang extension: %s';

}
