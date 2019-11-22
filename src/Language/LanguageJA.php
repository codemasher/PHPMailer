<?php
/**
 * Japanese PHPMailer language file
 *
 * @author Mitsuhiro Yoshida <http://mitstek.com/>
 * @author Yoshi Sakai <http://bluemooninc.jp/>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageJA extends PHPMailerLanguageAbstract{

	protected $code        = 'ja';
	protected $name        = 'Japanese';
	protected $native_name = '日本語';

	protected $authenticate         = 'SMTPエラー: 認証できませんでした。';
	protected $connect_host         = 'SMTPエラー: SMTPホストに接続できませんでした。';
	protected $data_not_accepted    = 'SMTPエラー: データが受け付けられませんでした。';
	protected $encoding             = '不明なエンコーディング: %s';
	protected $execute              = '実行できませんでした: %s';
	protected $file_access          = 'ファイルにアクセスできません: %s';
	protected $file_open            = 'ファイルエラー: ファイルを開けません: %s';
	protected $from_failed          = 'Fromアドレスを登録する際にエラーが発生しました: %s';
	protected $instantiate          = 'メール関数が正常に動作しませんでした。';
	protected $provide_address      = '少なくとも1つメールアドレスを 指定する必要があります。';
	protected $mailer_not_supported = ' メーラーがサポートされていません。';
	protected $recipients_failed    = 'SMTPエラー: 次の受信者アドレスに 間違いがあります: %s';

}
