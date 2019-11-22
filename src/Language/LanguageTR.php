<?php
/**
 * Turkish PHPMailer language file
 *
 * @author Elçin Özel
 * @author Can Yılmaz
 * @author Mehmet Benlioğlu
 * @author @yasinaydin
 * @author Ogün Karakuş
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageTR extends PHPMailerLanguageAbstract{

	protected $code        = 'tr';
	protected $name        = 'Turkish';
	protected $native_name = 'Türkçe';

	protected $authenticate         = 'SMTP Hatası: Oturum açılamadı.';
	protected $connect_host         = 'SMTP Hatası: SMTP sunucusuna bağlanılamadı.';
	protected $data_not_accepted    = 'SMTP Hatası: Veri kabul edilmedi.';
	protected $empty_message        = 'Mesajın içeriği boş';
	protected $encoding             = 'Bilinmeyen karakter kodlama: %s';
	protected $execute              = 'Çalıştırılamadı: %s';
	protected $file_access          = 'Dosyaya erişilemedi: %s';
	protected $file_open            = 'Dosya Hatası: Dosya açılamadı: %s';
	protected $from_failed          = 'Belirtilen adreslere gönderme başarısız: %s';
	protected $instantiate          = 'Örnek e-posta fonksiyonu oluşturulamadı.';
	protected $invalid_address      = 'Geçersiz e-posta adresi (%1$s): %2$s';
	protected $mailer_not_supported = ' e-posta kütüphanesi desteklenmiyor.';
	protected $provide_address      = 'En az bir alıcı e-posta adresi belirtmelisiniz.';
	protected $recipients_failed    = 'SMTP Hatası: Belirtilen alıcılara ulaşılamadı: %s';
	protected $signing              = 'İmzalama hatası: %s';
	protected $smtp_connect_failed  = 'SMTP connect() fonksiyonu başarısız.';
	protected $smtp_error           = 'SMTP sunucu hatası: ';
	protected $variable_set         = 'Değişken ayarlanamadı ya da sıfırlanamadı: ';
	protected $extension_missing    = 'Eklenti bulunamadı: %s';

}
