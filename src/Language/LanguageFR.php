<?php
/**
 * French PHPMailer language file
 *
 * Some French punctuation requires a thin non-breaking space (U+202F) character before it,
 * for example before a colon or exclamation mark.
 * There is one of these characters between these quotes: " "
 * @see http://unicode.org/udhr/n/notes_fra.html
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageFR extends PHPMailerLanguageAbstract{

	protected $code        = 'fr';
	protected $name        = 'French';
	protected $native_name = 'français';

	protected $authenticate         = 'Erreur SMTP : échec de l\'authentification.';
	protected $connect_host         = 'Erreur SMTP : impossible de se connecter au serveur SMTP.';
	protected $data_not_accepted    = 'Erreur SMTP : données incorrectes.';
	protected $empty_message        = 'Corps du message vide.';
	protected $encoding             = 'Encodage inconnu : %s';
	protected $execute              = 'Impossible de lancer l\'exécution : %s';
	protected $file_access          = 'Impossible d\'accéder au fichier : %s';
	protected $file_open            = 'Ouverture du fichier impossible : %s';
	protected $from_failed          = 'L\'adresse d\'expéditeur suivante a échoué : %s';
	protected $instantiate          = 'Impossible d\'instancier la fonction mail.';
	protected $invalid_address      = 'L\'adresse courriel n\'est pas valide (%1$s) : %2$s';
	protected $mailer_not_supported = ' client de messagerie non supporté.';
	protected $provide_address      = 'Vous devez fournir au moins une adresse de destinataire.';
	protected $recipients_failed    = 'Erreur SMTP : les destinataires suivants sont en erreur : %s';
	protected $signing              = 'Erreur de signature : %s';
	protected $smtp_connect_failed  = 'Échec de la connexion SMTP.';
	protected $smtp_error           = 'Erreur du serveur SMTP : ';
	protected $variable_set         = 'Impossible d\'initialiser ou de réinitialiser une variable : ';
	protected $extension_missing    = 'Extension manquante : %s';

}
