<?php
/**
 * Class Attachment
 *
 * @filesource   Attachment.php
 * @created      10.04.2019
 * @package      PHPMailer\PHPMailer
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace PHPMailer\PHPMailer;

/**
 * @internal
 */
class Attachment{
	public $content;
	public $filename;
	public $name;
	public $encoding;
	public $type;
	public $isStringAttachment;
	public $disposition;
	public $cid;
}
