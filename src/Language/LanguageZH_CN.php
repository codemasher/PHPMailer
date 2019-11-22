<?php
/**
 * Simplified Chinese PHPMailer language file
 *
 * @author liqwei <liqwei@liqwei.com>
 * @author young <masxy@foxmail.com>
 * @author Teddysun <i@teddysun.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageZH_CN extends PHPMailerLanguageAbstract{

	protected $code        = 'zh-cn';
	protected $name        = 'Simplified Chinese';
	protected $native_name = '汉语'; // @todo

	protected $authenticate         = 'SMTP 错误：登录失败。';
	protected $connect_host         = 'SMTP 错误：无法连接到 SMTP 主机。';
	protected $data_not_accepted    = 'SMTP 错误：数据不被接受。';
	protected $empty_message        = '邮件正文为空。';
	protected $encoding             = '未知编码：%s';
	protected $execute              = '无法执行：%s';
	protected $file_access          = '无法访问文件：%s';
	protected $file_open            = '文件错误：无法打开文件：%s';
	protected $from_failed          = '发送地址错误：%s';
	protected $instantiate          = '未知函数调用。';
	protected $invalid_address      = '发送失败，电子邮箱地址是无效的：(%1$s): %2$s';
	protected $mailer_not_supported = '发信客户端不被支持。';
	protected $provide_address      = '必须提供至少一个收件人地址。';
	protected $recipients_failed    = 'SMTP 错误：收件人地址错误：%s';
	protected $signing              = '登录失败：%s';
	protected $smtp_connect_failed  = 'SMTP服务器连接失败。';
	protected $smtp_error           = 'SMTP服务器出错：';
	protected $variable_set         = '无法设置或重置变量：';
	protected $extension_missing    = '丢失模块 Extension：%s';

}
