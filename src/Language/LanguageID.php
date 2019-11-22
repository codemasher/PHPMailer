<?php
/**
 * Indonesian PHPMailer language file
 *
 * @author Cecep Prawiro <cecep.prawiro@gmail.com>
 * @author @januridp
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageID extends PHPMailerLanguageAbstract{

	protected $code        = 'id';
	protected $name        = 'Indonesian';
	protected $native_name = 'Bahasa Indonesia';

	protected $authenticate         = 'Kesalahan SMTP: Tidak dapat mengotentikasi.';
	protected $connect_host         = 'Kesalahan SMTP: Tidak dapat terhubung ke host SMTP.';
	protected $data_not_accepted    = 'Kesalahan SMTP: Data tidak diterima.';
	protected $empty_message        = 'Isi pesan kosong';
	protected $encoding             = 'Pengkodean karakter tidak dikenali: %s';
	protected $execute              = 'Tidak dapat menjalankan proses : %s';
	protected $file_access          = 'Tidak dapat mengakses berkas : %s';
	protected $file_open            = 'Kesalahan File: Berkas tidak dapat dibuka : %s';
	protected $from_failed          = 'Alamat pengirim berikut mengakibatkan kesalahan : %s';
	protected $instantiate          = 'Tidak dapat menginisialisasi fungsi surel';
	protected $invalid_address      = 'Gagal terkirim, alamat surel tidak benar (%1$s) : %2$s';
	protected $provide_address      = 'Harus disediakan minimal satu alamat tujuan';
	protected $mailer_not_supported = ' mailer tidak didukung';
	protected $recipients_failed    = 'Kesalahan SMTP: Alamat tujuan berikut menghasilkan kesalahan : %s';
	protected $signing              = 'Kesalahan dalam tanda tangan : %s';
	protected $smtp_connect_failed  = 'SMTP Connect() gagal.';
	protected $smtp_error           = 'Kesalahan pada pelayan SMTP : ';
	protected $variable_set         = 'Tidak dapat mengatur atau mengatur ulang variable : ';
	protected $extension_missing    = 'Ekstensi hilang: %s';

}
