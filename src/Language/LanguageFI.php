<?php
/**
 * Finnish PHPMailer language file
 *
 * @author Jyry Kuukanen
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageFI extends PHPMailerLanguageAbstract{

	protected $code        = 'fi';
	protected $name        = 'Finnish';
	protected $native_name = 'suomi';

	protected $authenticate         = 'SMTP-virhe: käyttäjätunnistus epäonnistui.';
	protected $connect_host         = 'SMTP-virhe: yhteys palvelimeen ei onnistu.';
	protected $data_not_accepted    = 'SMTP-virhe: data on virheellinen.';
	protected $encoding             = 'Tuntematon koodaustyyppi: %s';
	protected $execute              = 'Suoritus epäonnistui: %s';
	protected $file_access          = 'Seuraavaan tiedostoon ei ole oikeuksia: %s';
	protected $file_open            = 'Tiedostovirhe: Ei voida avata tiedostoa: %s';
	protected $from_failed          = 'Seuraava lähettäjän osoite on virheellinen: %s';
	protected $instantiate          = 'mail-funktion luonti epäonnistui.';
	protected $mailer_not_supported = 'postivälitintyyppiä ei tueta.';
	protected $provide_address      = 'Aseta vähintään yksi vastaanottajan sähk&ouml;postiosoite.';
	protected $recipients_failed    = 'SMTP-virhe: seuraava vastaanottaja osoite on virheellinen: %s';

}
