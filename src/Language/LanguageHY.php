<?php
/**
 * Armenian PHPMailer language file
 *
 * @author Hrayr Grigoryan <hrayr@bits.am>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageHY extends PHPMailerLanguageAbstract{

	protected $code        = 'hy';
	protected $name        = 'Armenian';
	protected $native_name = 'Հայերեն';

	protected $authenticate         = 'SMTP -ի սխալ: չհաջողվեց ստուգել իսկությունը.';
	protected $connect_host         = 'SMTP -ի սխալ: չհաջողվեց կապ հաստատել SMTP սերվերի հետ.';
	protected $data_not_accepted    = 'SMTP -ի սխալ: տվյալները ընդունված չեն.';
	protected $empty_message        = 'Հաղորդագրությունը դատարկ է';
	protected $encoding             = 'Կոդավորման անհայտ տեսակ: %s';
	protected $execute              = 'Չհաջողվեց իրականացնել հրամանը: %s';
	protected $file_access          = 'Ֆայլը հասանելի չէ: %s';
	protected $file_open            = 'Ֆայլի սխալ: ֆայլը չհաջողվեց բացել: %s';
	protected $from_failed          = 'Ուղարկողի հետևյալ հասցեն սխալ է: %s';
	protected $instantiate          = 'Հնարավոր չէ կանչել mail ֆունկցիան.';
	protected $invalid_address      = 'Հասցեն սխալ է (%1$s): %2$s';
	protected $mailer_not_supported = ' փոստային սերվերի հետ չի աշխատում.';
	protected $provide_address      = 'Անհրաժեշտ է տրամադրել գոնե մեկ ստացողի e-mail հասցե.';
	protected $recipients_failed    = 'SMTP -ի սխալ: չի հաջողվել ուղարկել հետևյալ ստացողների հասցեներին: %s';
	protected $signing              = 'Ստորագրման սխալ: %s';
	protected $smtp_connect_failed  = 'SMTP -ի connect() ֆունկցիան չի հաջողվել';
	protected $smtp_error           = 'SMTP սերվերի սխալ: ';
	protected $variable_set         = 'Չի հաջողվում ստեղծել կամ վերափոխել փոփոխականը: ';
	protected $extension_missing    = 'Հավելվածը բացակայում է: %s';

}
