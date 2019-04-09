<?php
/**
 * Interface PHPMailerOAuthInterface
 *
 * @filesource   PHPMailerOAuthInterface.php
 * @created      09.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

interface PHPMailerOAuthInterface{

	const AUTH_XOAUTH2 = "user=%s\001auth=Bearer %s\001\001";

	/**
	 * Generate a base64-encoded OAuth token.
	 *
	 * @return string
	 */
	public function getAuthString():string;

}
