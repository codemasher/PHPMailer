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

use PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface;

use function strtolower;

/**
 * @internal
 */
class Attachment{

	public $content;
	public $filename;
	public $name;
	public $encoding;
	public $mimeType;
	public $isStringAttachment = false;
	public $disposition;
	public $cid;

	/**
	 * @var \PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface
	 */
	private $lang;

	/**
	 * Attachment constructor.
	 *
	 * @param \PHPMailer\PHPMailer\Language\PHPMailerLanguageInterface $lang
	 */
	public function __construct(PHPMailerLanguageInterface $lang){
		$this->lang = $lang;
	}

	/**
	 * @param string $name
	 * @param string $filename
	 *
	 * @return \PHPMailer\PHPMailer\Attachment
	 */
	public function setName(string $name, string $filename):Attachment{
		$this->name     = $name;
		$this->filename = $filename;

		return $this;
	}

	/**
	 * @param string      $content
	 * @param string      $mimeType
	 * @param string      $encoding
	 * @param string      $disposition
	 * @param string|null $cid
	 *
	 * @return \PHPMailer\PHPMailer\Attachment
	 * @throws \PHPMailer\PHPMailer\PHPMailerException
	 */
	public function setContent(
		string $content,
		string $mimeType,
		string $encoding = null,
		string $disposition = null,
		string $cid = null
	):Attachment{

		if($encoding !== null && !in_array($encoding, PHPMailerInterface::ENCODINGS, true)){
			throw new PHPMailerException(sprintf($this->lang->string('encoding'), $encoding));
		}

		$disposition = strtolower($disposition ?? 'attachment');

#		if(!\in_array($disposition, ['attachment', 'inline'], true)){} // @todo

		$this->content     = $content;
		$this->encoding    = $encoding ?? PHPMailerInterface::ENCODING_BASE64;
		$this->mimeType    = $mimeType;
		$this->disposition = $disposition;
		$this->cid         = $cid;

		return $this;
	}

	/**
	 * @param bool $isStringAttachment
	 *
	 * @return \PHPMailer\PHPMailer\Attachment
	 */
	public function isStringAttachment(bool $isStringAttachment):Attachment{
		$this->isStringAttachment = $isStringAttachment;

		return $this;
	}

}
