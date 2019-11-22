<?php
/**
 * Greek PHPMailer language file
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageEL extends PHPMailerLanguageAbstract{

	protected $code        = 'el';
	protected $name        = 'Greek';
	protected $native_name = 'ελληνικά';

	protected $authenticate         = 'SMTP Σφάλμα: Αδυναμία πιστοποίησης (authentication).';
	protected $connect_host         = 'SMTP Σφάλμα: Αδυναμία σύνδεσης στον SMTP-Host.';
	protected $data_not_accepted    = 'SMTP Σφάλμα: Τα δεδομένα δεν έγιναν αποδεκτά.';
	protected $empty_message        = 'Το E-Mail δεν έχει περιεχόμενο .';
	protected $encoding             = 'Αγνωστο Encoding-Format: %s';
	protected $execute              = 'Αδυναμία εκτέλεσης ακόλουθης εντολής: %s';
	protected $file_access          = 'Αδυναμία προσπέλασης του αρχείου: %s';
	protected $file_open            = 'Σφάλμα Αρχείου: Δεν είναι δυνατό το άνοιγμα του ακόλουθου αρχείου: %s';
	protected $from_failed          = 'Η παρακάτω διεύθυνση αποστολέα δεν είναι σωστή: %s';
	protected $instantiate          = 'Αδυναμία εκκίνησης Mail function.';
	protected $invalid_address      = 'Το μήνυμα δεν εστάλη, η διεύθυνση δεν είναι έγκυρη (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer δεν υποστηρίζεται.';
	protected $provide_address      = 'Παρακαλούμε δώστε τουλάχιστον μια e-mail διεύθυνση παραλήπτη.';
	protected $recipients_failed    = 'SMTP Σφάλμα: Οι παρακάτω διευθύνσεις παραλήπτη δεν είναι έγκυρες: %s';
	protected $signing              = 'Σφάλμα υπογραφής: %s';
	protected $smtp_connect_failed  = 'Αποτυχία σύνδεσης στον SMTP Server.';
	protected $smtp_error           = 'Σφάλμα από τον SMTP Server: ';
	protected $variable_set         = 'Αδυναμία ορισμού ή αρχικοποίησης μεταβλητής: ';

}
