<?php
/**
 * Traditional Chinese PHPMailer language file
 *
 * @author liqwei <liqwei@liqwei.com>
 * @author Peter Dave Hello <@PeterDaveHello/>
 * @author Jason Chiang <xcojad@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageZH extends PHPMailerLanguageAbstract{

	protected $code        = 'zh';
	protected $name        = 'Traditional Chinese';
	protected $native_name = '漢語'; // @todo

	protected $authenticate         = 'SMTP 錯誤：登入失敗。';
	protected $connect_host         = 'SMTP 錯誤：無法連線到 SMTP 主機。';
	protected $data_not_accepted    = 'SMTP 錯誤：無法接受的資料。';
	protected $empty_message        = '郵件內容為空';
	protected $encoding             = '未知編碼: %s';
	protected $execute              = '無法執行：%s';
	protected $file_access          = '無法存取檔案：%s';
	protected $file_open            = '檔案錯誤：無法開啟檔案：%s';
	protected $from_failed          = '發送地址錯誤：%s';
	protected $instantiate          = '未知函數呼叫。';
	protected $invalid_address      = '因為電子郵件地址無效，無法傳送 (%1$s): %2$s';
	protected $mailer_not_supported = '不支援的發信客戶端。';
	protected $provide_address      = '必須提供至少一個收件人地址。';
	protected $recipients_failed    = 'SMTP 錯誤：以下收件人地址錯誤：%s';
	protected $signing              = '電子簽章錯誤: %s';
	protected $smtp_connect_failed  = 'SMTP 連線失敗';
	protected $smtp_error           = 'SMTP 伺服器錯誤: ';
	protected $variable_set         = '無法設定或重設變數: ';
	protected $extension_missing    = '遺失模組 Extension: %s';

}
