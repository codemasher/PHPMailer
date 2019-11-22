<?php
/**
 * Korean PHPMailer language file
 *
 * @author ChalkPE <amato0617@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguageKO extends PHPMailerLanguageAbstract{

	protected $code        = 'ko';
	protected $name        = 'Korean';
	protected $native_name = '한국어';

	protected $authenticate         = 'SMTP 오류: 인증할 수 없습니다.';
	protected $connect_host         = 'SMTP 오류: SMTP 호스트에 접속할 수 없습니다.';
	protected $data_not_accepted    = 'SMTP 오류: 데이터가 받아들여지지 않았습니다.';
	protected $empty_message        = '메세지 내용이 없습니다';
	protected $encoding             = '알 수 없는 인코딩: %s';
	protected $execute              = '실행 불가: %s';
	protected $file_access          = '파일 접근 불가: %s';
	protected $file_open            = '파일 오류: 파일을 열 수 없습니다: %s';
	protected $from_failed          = '다음 From 주소에서 오류가 발생했습니다: %s';
	protected $instantiate          = 'mail 함수를 인스턴스화할 수 없습니다';
	protected $invalid_address      = '잘못된 주소 (%1$s): %2$s';
	protected $mailer_not_supported = ' 메일러는 지원되지 않습니다.';
	protected $provide_address      = '적어도 한 개 이상의 수신자 메일 주소를 제공해야 합니다.';
	protected $recipients_failed    = 'SMTP 오류: 다음 수신자에서 오류가 발생했습니다: %s';
	protected $signing              = '서명 오류: %s';
	protected $smtp_connect_failed  = 'SMTP 연결을 실패하였습니다.';
	protected $smtp_error           = 'SMTP 서버 오류: ';
	protected $variable_set         = '변수 설정 및 초기화 불가: ';
	protected $extension_missing    = '확장자 없음: %s';

}
