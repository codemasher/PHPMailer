<?php
/**
 * Interface PHPMailerInterface
 *
 * @filesource   PHPMailerInterface.php
 * @created      14.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

interface PHPMailerInterface{
	public function postSend():bool;
}
