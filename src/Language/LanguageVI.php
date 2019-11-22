<?php
/**
 * Vietnamese PHPMailer language file
 *
 * @author VINADES.,JSC <contact@vinades.vn>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageVI extends PHPMailerLanguageAbstract{

	protected $code        = 'vi';
	protected $name        = 'Vietnamese';
	protected $native_name = 'Tiếng Việt';

	protected $authenticate         = 'Lỗi SMTP: Không thể xác thực.';
	protected $connect_host         = 'Lỗi SMTP: Không thể kết nối máy chủ SMTP.';
	protected $data_not_accepted    = 'Lỗi SMTP: Dữ liệu không được chấp nhận.';
	protected $empty_message        = 'Không có nội dung';
	protected $encoding             = 'Mã hóa không xác định: %s';
	protected $execute              = 'Không thực hiện được: %s';
	protected $file_access          = 'Không thể truy cập tệp tin %s';
	protected $file_open            = 'Lỗi Tập tin: Không thể mở tệp tin: %s';
	protected $from_failed          = 'Lỗi địa chỉ gửi đi: %s';
	protected $instantiate          = 'Không dùng được các hàm gửi thư.';
	protected $invalid_address      = 'Đại chỉ emai không đúng (%1$s): %2$s';
	protected $mailer_not_supported = ' trình gửi thư không được hỗ trợ.';
	protected $provide_address      = 'Bạn phải cung cấp ít nhất một địa chỉ người nhận.';
	protected $recipients_failed    = 'Lỗi SMTP: lỗi địa chỉ người nhận: %s';
	protected $signing              = 'Lỗi đăng nhập: %s';
	protected $smtp_connect_failed  = 'Lỗi kết nối với SMTP';
	protected $smtp_error           = 'Lỗi máy chủ smtp ';
	protected $variable_set         = 'Không thể thiết lập hoặc thiết lập lại biến: ';

}
