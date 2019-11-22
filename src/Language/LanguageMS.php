<?php
/**
 * Malay PHPMailer language file
 *
 * @author Nawawi Jamili <nawawi@rutweb.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageMS extends PHPMailerLanguageAbstract{

	protected $code        = 'ms';
	protected $name        = 'Malay';
	protected $native_name = 'Bahasa Melayu';

	protected $authenticate         = 'Ralat SMTP: Tidak dapat pengesahan.';
	protected $connect_host         = 'Ralat SMTP: Tidak dapat menghubungi hos pelayan SMTP.';
	protected $data_not_accepted    = 'Ralat SMTP: Data tidak diterima oleh pelayan.';
	protected $empty_message        = 'Tiada isi untuk mesej';
	protected $encoding             = 'Pengekodan tidak diketahui: %s';
	protected $execute              = 'Tidak dapat melaksanakan: %s';
	protected $file_access          = 'Tidak dapat mengakses fail: %s';
	protected $file_open            = 'Ralat Fail: Tidak dapat membuka fail: %s';
	protected $from_failed          = 'Berikut merupakan ralat dari alamat e-mel: %s';
	protected $instantiate          = 'Tidak dapat memberi contoh fungsi e-mel.';
	protected $invalid_address      = 'Alamat emel tidak sah (%1$s): %2$s';
	protected $mailer_not_supported = ' jenis penghantar emel tidak disokong.';
	protected $provide_address      = 'Anda perlu menyediakan sekurang-kurangnya satu alamat e-mel penerima.';
	protected $recipients_failed    = 'Ralat SMTP: Penerima e-mel berikut telah gagal: %s';
	protected $signing              = 'Ralat pada tanda tangan: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() telah gagal.';
	protected $smtp_error           = 'Ralat pada pelayan SMTP: ';
	protected $variable_set         = 'Tidak boleh menetapkan atau menetapkan semula pembolehubah: ';
	protected $extension_missing    = 'Sambungan hilang: %s';

}
