<?php
/**
 * Brazilian Portuguese PHPMailer language file
 *
 * @author Paulo Henrique Garcia <paulo@controllerweb.com.br>
 * @author Lucas Guimarães <lucas@lucasguimaraes.com>
 * @author Phelipe Alves <phelipealvesdesouza@gmail.com>
 * @author Fabio Beneditto <fabiobeneditto@gmail.com>
 */

namespace PHPMailer\PHPMailer\Language;

class LanguagePT_BR extends PHPMailerLanguageAbstract{

	protected $code        = 'pt-br';
	protected $name        = 'Brazilian Portuguese';
	protected $native_name = 'Português (Brasil)';

	protected $authenticate         = 'Erro de SMTP: Não foi possível autenticar.';
	protected $connect_host         = 'Erro de SMTP: Não foi possível conectar ao servidor SMTP.';
	protected $data_not_accepted    = 'Erro de SMTP: Dados rejeitados.';
	protected $empty_message        = 'Mensagem vazia';
	protected $encoding             = 'Codificação desconhecida: %s';
	protected $execute              = 'Não foi possível executar: %s';
	protected $file_access          = 'Não foi possível acessar o arquivo: %s';
	protected $file_open            = 'Erro de Arquivo: Não foi possível abrir o arquivo: %s';
	protected $from_failed          = 'Os seguintes remetentes falharam: %s';
	protected $instantiate          = 'Não foi possível instanciar a função mail.';
	protected $invalid_address      = 'Endereço de e-mail inválido (%1$s): %2$s';
	protected $mailer_not_supported = ' mailer não é suportado.';
	protected $provide_address      = 'Você deve informar pelo menos um destinatário.';
	protected $recipients_failed    = 'Erro de SMTP: Os seguintes destinatários falharam: %s';
	protected $signing              = 'Erro de Assinatura: %s';
	protected $smtp_connect_failed  = 'SMTP Connect() falhou.';
	protected $smtp_error           = 'Erro de servidor SMTP: ';
	protected $variable_set         = 'Não foi possível definir ou redefinir a variável: ';
	protected $extension_missing    = 'Extensão não existe: %s';

}
