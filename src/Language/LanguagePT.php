<?php
/**
 * Portuguese (European) PHPMailer language file
 *
 * @author Jonadabe <jonadabe@hotmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguagePT extends PHPMailerLanguageAbstract{

	protected $code        = 'pt';
	protected $name        = 'Portuguese';
	protected $native_name = 'Português';

	protected $authenticate         = 'Erro do SMTP: Não foi possível realizar a autenticação.';
	protected $connect_host         = 'Erro do SMTP: Não foi possível realizar ligação com o servidor SMTP.';
	protected $data_not_accepted    = 'Erro do SMTP: Os dados foram rejeitados.';
	protected $empty_message        = 'A mensagem no e-mail está vazia.';
	protected $encoding             = 'Codificação desconhecida: %s';
	protected $execute              = 'Não foi possível executar: %s';
	protected $file_access          = 'Não foi possível aceder o ficheiro: %s';
	protected $file_open            = 'Abertura do ficheiro: Não foi possível abrir o ficheiro: %s';
	protected $from_failed          = 'Ocorreram falhas nos endereços dos seguintes remententes: %s';
	protected $instantiate          = 'Não foi possível iniciar uma instância da função mail.';
	protected $invalid_address      = 'Não foi enviado nenhum e-mail para o endereço de e-mail inválido (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer não é suportado.';
	protected $provide_address      = 'Tem de fornecer pelo menos um endereço como destinatário do e-mail.';
	protected $recipients_failed    = 'Erro do SMTP: O endereço do seguinte destinatário falhou: %s';
	protected $signing              = 'Erro ao assinar: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() falhou.';
	protected $smtp_error           = 'Erro de servidor SMTP: ';
	protected $variable_set         = 'Não foi possível definir ou redefinir a variável: ';
	protected $extension_missing    = 'Extensão em falta: %s';

}
