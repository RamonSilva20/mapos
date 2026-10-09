<?php

require_once APPPATH . 'models/Email_model.php';
require_once APPPATH . 'models/Audit_model.php';
require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'validation_helper.php';

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Configurações, Emitente, Fila de e-mails e Auditoria migrados (#2846).
 */
final class ConfiguracoesTest extends MaposTestCase
{
    // --- Configurações ------------------------------------------------------

    /** Toda configuração que a v4 gravava continua editável em alguma aba. */
    public function testTodasAsConfiguracoesDaV4TemCampo(): void
    {
        $campos = [];
        foreach (array_keys(CONFIG_ABAS) as $aba) {
            $campos += configuracaoCampos($aba);
        }

        $v4 = ['app_name', 'per_page', 'os_notification', 'email_automatico', 'control_estoque', 'notifica_whats', 'control_baixa', 'control_editos', 'control_edit_vendas',
            'control_datatable', 'os_status_list', 'control_2vias', 'pix_key', 'IMPRIMIR_ANEXOS', 'API_ENABLED', 'API_TOKEN_EXPIRE_TIME', 'API_JWT_KEY',
            'EMAIL_PROTOCOL', 'EMAIL_SMTP_HOST', 'EMAIL_SMTP_CRYPTO', 'EMAIL_SMTP_PORT', 'EMAIL_SMTP_USER', 'EMAIL_SMTP_PASS',
            'PAYMENT_GATEWAYS_EFI_PRODUCTION', 'PAYMENT_GATEWAYS_EFI_CREDENTIAIS_CLIENT_ID', 'PAYMENT_GATEWAYS_EFI_CREDENTIAIS_CLIENT_SECRET', 'PAYMENT_GATEWAYS_EFI_BOLETO_EXPIRATION',
            'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_PUBLIC_KEY', 'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_ACCESS_TOKEN', 'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_CLIENT_ID',
            'PAYMENT_GATEWAYS_MERCADO_PAGO_CREDENTIALS_CLIENT_SECRET', 'PAYMENT_GATEWAYS_MERCADO_PAGO_BOLETO_EXPIRATION', 'PAYMENT_GATEWAYS_ASAAS_PRODUCTION',
            'PAYMENT_GATEWAYS_ASAAS_NOTIFY', 'PAYMENT_GATEWAYS_ASAAS_CREDENTIAIS_API_KEY', 'PAYMENT_GATEWAYS_ASAAS_BOLETO_EXPIRATION'];

        $this->assertSame([], array_values(array_diff($v4, array_keys($campos))));
        $this->assertArrayHasKey('app_tema_modo', $campos, 'O tema da v5 substitui o seletor de temas da v4.');
    }

    public function testAbaGravaSoOsCamposDelaEConverteOsTipos(): void
    {
        [$config, $env, $erros] = configuracaoDadosDoFormulario('geral', [
            'app_name' => ' Minha Oficina ', 'per_page' => '20', 'app_tema_modo' => 'escuro', 'control_datatable' => '1', 'pix_key' => 'invasor', 'API_ENABLED' => 'true',
        ]);

        $this->assertSame([], $erros);
        $this->assertSame([], $env);
        $this->assertSame(['app_name' => 'Minha Oficina', 'per_page' => '20', 'app_tema_modo' => 'escuro', 'control_datatable' => '1', 'app_theme' => 'puredark'], $config, 'Só os campos da aba; app_theme acompanha o modo para as telas antigas.');

        [$config] = configuracaoDadosDoFormulario('geral', ['app_name' => 'X', 'per_page' => '10', 'app_tema_modo' => 'sistema']);
        $this->assertSame('0', $config['control_datatable'], 'Switch desmarcado não vem no POST: vale 0.');
        $this->assertSame('white', $config['app_theme']);
    }

    public function testErrosPorCampo(): void
    {
        [, , $erros] = configuracaoDadosDoFormulario('geral', ['app_name' => '  ', 'per_page' => '7', 'app_tema_modo' => 'roxo']);
        $this->assertSame(['app_name', 'per_page', 'app_tema_modo'], array_keys($erros));

        [, , $erros] = configuracaoDadosDoFormulario('os', ['os_notification' => 'todos', 'os_status_list' => ['Inventado']]);
        $this->assertSame('Marque ao menos um status.', $erros['os_status_list']);

        [, , $erros] = configuracaoDadosDoFormulario('email', ['EMAIL_PROTOCOL' => 'smtp', 'EMAIL_SMTP_CRYPTO' => 'tls', 'EMAIL_SMTP_PORT' => '58a']);
        $this->assertArrayHasKey('EMAIL_SMTP_PORT', $erros);
    }

    public function testStatusDaOsEmJson(): void
    {
        [$config, , $erros] = configuracaoDadosDoFormulario('os', ['os_notification' => 'cliente', 'os_status_list' => ['Faturado', 'Aberto', 'Hack']]);

        $this->assertSame([], $erros);
        $this->assertSame('["Aberto","Faturado"]', $config['os_status_list'], 'Na ordem do fluxo e só os status de OS.');
        $this->assertSame(['Aberto', 'Faturado'], osStatusVisiveis($config['os_status_list']));
    }

    public function testSegredoEmBrancoMantemEJwtSoQuandoPedido(): void
    {
        [, $env] = configuracaoDadosDoFormulario('email', ['EMAIL_PROTOCOL' => 'smtp', 'EMAIL_SMTP_CRYPTO' => 'ssl', 'EMAIL_SMTP_PASS' => '']);
        $this->assertArrayNotHasKey('EMAIL_SMTP_PASS', $env);
        $this->assertSame('smtp', $env['EMAIL_PROTOCOL']);

        [, $env] = configuracaoDadosDoFormulario('email', ['EMAIL_PROTOCOL' => 'smtp', 'EMAIL_SMTP_CRYPTO' => 'ssl', 'EMAIL_SMTP_PASS' => 'nova senha']);
        $this->assertSame('nova senha', $env['EMAIL_SMTP_PASS']);

        [, $env] = configuracaoDadosDoFormulario('api', ['API_ENABLED' => 'true', 'API_TOKEN_EXPIRE_TIME' => '3600'], static fn () => 'chave-nova');
        $this->assertArrayNotHasKey('API_JWT_KEY', $env);
        $this->assertSame('true', $env['API_ENABLED']);

        [, $env] = configuracaoDadosDoFormulario('api', ['API_TOKEN_EXPIRE_TIME' => '3600', 'API_JWT_KEY' => '1'], static fn () => 'chave-nova');
        $this->assertSame('chave-nova', $env['API_JWT_KEY']);
        $this->assertSame('false', $env['API_ENABLED']);
    }

    public function testValoresDaTelaNuncaTrazemSegredos(): void
    {
        $valores = configuracaoValores('email', null, [], ['EMAIL_SMTP_PASS' => 'segredo', 'EMAIL_SMTP_HOST' => 'smtp.x', 'EMAIL_PROTOCOL' => 'smtp']);

        $this->assertSame(['definido' => true], $valores['EMAIL_SMTP_PASS']);
        $this->assertSame('smtp.x', $valores['EMAIL_SMTP_HOST']);
        $this->assertSame(['definido' => false], configuracaoValores('email', null, [], [])['EMAIL_SMTP_PASS']);

        $os = configuracaoValores('os', null, ['os_status_list' => '["Aberto"]', 'control_editos' => '1'], ['IMPRIMIR_ANEXOS' => 'true']);
        $this->assertSame(['Aberto'], $os['os_status_list']);
        $this->assertTrue($os['control_editos']);
        $this->assertTrue($os['IMPRIMIR_ANEXOS']);

        $geral = configuracaoValores('geral', null, ['app_theme' => 'puredark'], []);
        $this->assertSame('escuro', $geral['app_tema_modo'], 'Sem app_tema_modo (migration não rodou), vem do tema antigo.');
    }

    public function testValorDoEnvVoltaIgualNoPhpdotenv(): void
    {
        $valores = ['simples' => 'smtp.gmail.com', 'espaco' => 'Minha senha', 'aspas' => "it's \"ok\" \\ fim", 'cifrao' => 'a$b${X}', 'vazio' => '', 'quebra' => "linha1\nlinha2"];

        $conteudo = '';
        foreach ($valores as $chave => $valor) {
            $conteudo .= 'K_' . strtoupper($chave) . '=' . configuracaoValorEnv($valor) . "\n";
        }
        $lido = Dotenv\Dotenv::parse($conteudo);

        $this->assertSame('smtp.gmail.com', $lido['K_SIMPLES']);
        $this->assertSame('Minha senha', $lido['K_ESPACO']);
        $this->assertSame("it's \"ok\" \\ fim", $lido['K_ASPAS']);
        $this->assertSame('a$b${X}', $lido['K_CIFRAO'], 'Entre aspas simples o $ é literal.');
        $this->assertSame('', $lido['K_VAZIO']);
        $this->assertSame('linha1linha2', $lido['K_QUEBRA'], 'Quebra de linha não cria outra variável.');
        $this->assertCount(6, $lido);
    }

    public function testEnvAtualizadoTrocaALinhaInteiraOuAcrescenta(): void
    {
        $antes = "# comentário\nEMAIL_SMTP_HOST=\"smtp.antigo\"\nEMAIL_SMTP_PASS='velha'\nOUTRA=1\n";

        $depois = configuracaoEnvAtualizado($antes, ['EMAIL_SMTP_HOST' => 'smtp.novo', 'EMAIL_SMTP_PASS' => 'nova senha', 'API_ENABLED' => 'true', 'chave ruim' => 'x']);
        $lido = Dotenv\Dotenv::parse($depois);

        $this->assertStringStartsWith("# comentário\n", $depois);
        $this->assertSame('smtp.novo', $lido['EMAIL_SMTP_HOST'], 'Na v4 o valor entre aspas não era trocado.');
        $this->assertSame('nova senha', $lido['EMAIL_SMTP_PASS']);
        $this->assertSame('1', $lido['OUTRA']);
        $this->assertSame('true', $lido['API_ENABLED']);
        $this->assertCount(4, $lido, 'Chave com caractere inválido é ignorada.');
        $this->assertSame(1, substr_count($depois, 'EMAIL_SMTP_HOST='));
    }

    // --- Emitente -----------------------------------------------------------

    public function testDadosDoEmitente(): void
    {
        $post = ['nome' => 'Oficina', 'cnpj' => '11.222.333/0001-81', 'cep' => '01001-000', 'rua' => 'Praça da Sé', 'numero' => '1', 'bairro' => 'Sé', 'cidade' => 'São Paulo', 'estado' => 'sp', 'telefone' => '1133334444', 'email' => 'a@b.com', 'id' => '9'];

        [$dados, $erros] = emitenteDadosDoFormulario($post);
        $this->assertSame([], $erros);
        $this->assertSame('SP', $dados['uf'], 'O campo da tela é "estado" (o módulo do CEP o preenche); a coluna é uf.');
        $this->assertArrayNotHasKey('estado', $dados);
        $this->assertArrayNotHasKey('id', $dados, 'O id nunca vem do POST.');

        [, $erros] = emitenteDadosDoFormulario(['cnpj' => '123', 'estado' => 'XX', 'email' => 'x'] + $post);
        $this->assertEqualsCanonicalizing(['cnpj', 'estado', 'email'], array_keys($erros));

        [, $erros] = emitenteDadosDoFormulario(['nome' => ''] + $post);
        $this->assertArrayHasKey('nome', $erros);
    }

    public function testArquivoDoLogoSoDentroDaPastaDeUploads(): void
    {
        $raiz = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-logo-' . bin2hex(random_bytes(4));
        mkdir($raiz . '/uploads', 0777, true);
        file_put_contents($raiz . '/uploads/logo.png', 'x');
        file_put_contents($raiz . '/segredo.txt', 'x');

        try {
            $this->assertSame(realpath($raiz . '/uploads/logo.png'), emitenteArquivoDoLogo('http://mapos.test/assets/uploads/logo.png', $raiz . '/uploads'));
            $this->assertNull(emitenteArquivoDoLogo('http://mapos.test/assets/uploads/../segredo.txt', $raiz . '/uploads'), 'Só o nome do fim da URL conta.');
            $this->assertNull(emitenteArquivoDoLogo('http://mapos.test/assets/uploads/', $raiz . '/uploads'));
            $this->assertNull(emitenteArquivoDoLogo(null, $raiz . '/uploads'));
            $this->assertNull(emitenteArquivoDoLogo('http://x/nao-existe.png', $raiz . '/uploads'));
        } finally {
            @unlink($raiz . '/uploads/logo.png');
            @unlink($raiz . '/segredo.txt');
            @rmdir($raiz . '/uploads');
            @rmdir($raiz);
        }
    }

    // --- Fila de e-mails e auditoria ---------------------------------------

    public function testSituacaoEAssuntoDosEmails(): void
    {
        $this->assertSame(['label' => 'Enviado', 'variant' => 'success'], emailFilaPill('sent'));
        $this->assertSame('warning', emailFilaPill('failed')['variant'], 'Falha de envio não é erro do usuário: warning.');
        $this->assertSame('Sem status', emailFilaPill(null)['label']);
        $this->assertSame('Ordem de Serviço', emailFilaAssunto('{"From":"a@b","Subject":"Ordem de Serviço"}'));
        $this->assertSame('', emailFilaAssunto('lixo'));
        $this->assertSame('Ordem de Serviço', emailFilaAssunto((string) json_encode(['Subject' => '=?UTF-8?Q?Ordem=20de=20Servi=C3=A7o?='])), 'O CI_Email grava o assunto codificado.');
    }

    public function testListagensDaFilaEDaAuditoria(): void
    {
        $this->db->query('CREATE TABLE email_queue (id INTEGER PRIMARY KEY AUTOINCREMENT, "to" TEXT, cc TEXT, bcc TEXT, message TEXT, status TEXT, date TEXT, headers TEXT)');
        $this->db->query("INSERT INTO email_queue (\"to\", message, status, date) VALUES ('a@x', '<p>1</p>', 'pending', '2026-10-01'), ('b@x', '<p>2</p>', 'sent', '2026-10-02'), ('c@x', '<p>3</p>', 'sent', '2026-10-03')");
        $this->db->query('CREATE TABLE logs (idLogs INTEGER PRIMARY KEY AUTOINCREMENT, usuario TEXT, tarefa TEXT, data TEXT, hora TEXT, ip TEXT)');
        $this->db->query("INSERT INTO logs (usuario, tarefa, data, hora, ip) VALUES ('Ana', 'Adicionou uma OS', '2026-09-01', '10:00:00', '10.0.0.1'), ('Bruno', 'Login', '2026-10-05', '11:00:00', '10.0.0.2'), ('Ana', 'Login', '2026-10-09', '12:00:00', '10.0.0.1')");

        $email = $this->makeInstance(Email_model::class);
        $email->db = $this->db;
        $this->assertSame(['3', '2'], array_map('strval', array_column($email->listar(['status' => 'sent'], 10, 0), 'id')));
        $this->assertObjectNotHasProperty('message', $email->listar([], 10, 0)[0], 'O HTML da mensagem não entra na listagem.');
        $this->assertSame(3, $email->contar([]));

        $audit = $this->makeInstance(Audit_model::class);
        $audit->db = $this->db;
        $this->assertSame(['Ana', 'Ana'], array_column($audit->listar(['pesquisa' => 'Ana'], 10, 0), 'usuario'));
        $this->assertSame(1, $audit->contar(['pesquisa' => '10.0.0.2']));
        $this->assertSame(['Login', 'Login'], array_column($audit->listar(['de' => '2026-10-01', 'ate' => '2026-10-09'], 10, 0), 'tarefa'));
        $this->assertSame(['Bruno'], array_column($audit->listar([], 1, 1), 'usuario'), 'Mais recentes primeiro.');
    }

    // --- Controllers e views -----------------------------------------------

    public function testAcoesQueMudamDadosSoPorPost(): void
    {
        $mapos = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        $auditoria = (string) file_get_contents(APPPATH . 'controllers/Auditoria.php');

        preg_match('/public function atualizarBanco\(\).*?\n    }\n/s', $mapos, $banco);
        preg_match('/public function atualizarMapos\(\).*?\n    }\n/s', $mapos, $atualizar);
        preg_match('/public function clean\(\).*?\n    }\n/s', $auditoria, $limpar);
        foreach ([$banco, $atualizar, $limpar] as $trecho) {
            $this->assertNotEmpty($trecho);
            $this->assertStringContainsString("\$this->input->method() !== 'post'", $trecho[0]);
        }

        $this->assertStringNotContainsString('print_r', $mapos, 'Erro de upload não é mais impresso cru.');
        $this->assertStringNotContainsString('delete_files(', $mapos, 'Trocar o logo não apaga a pasta de uploads.');
        $this->assertStringNotContainsString('function editDontEnv', $mapos);
        $this->assertStringNotContainsString("post('id')", substr($mapos, (int) strpos($mapos, 'public function emitente()'), 3000), 'O emitente editado não vem do POST.');
    }

    public function testTelaDeConfiguracoesRenderizaTodasAsAbasSemSegredos(): void
    {
        foreach (array_keys(CONFIG_ABAS) as $aba) {
            $carregador = new class() {
                public object $security;

                public function __construct()
                {
                    $this->security = new class() {
                        public function get_csrf_token_name()
                        {
                            return 'MAPOS_TOKEN';
                        }

                        public function get_csrf_hash()
                        {
                            return 'hash-csrf';
                        }
                    };
                }

                public function render(array $variaveis): string
                {
                    extract($variaveis);
                    ob_start();
                    include APPPATH . 'views/mapos/configurar.php';

                    return (string) ob_get_clean();
                }
            };

            $env = ['EMAIL_SMTP_PASS' => 'senha-secreta', 'PAYMENT_GATEWAYS_ASAAS_CREDENTIAIS_API_KEY' => 'chave-secreta', 'EMAIL_PROTOCOL' => 'smtp'];
            $html = $carregador->render([
                'aba' => $aba,
                'campos' => configuracaoCampos($aba),
                'valores' => configuracaoValores($aba, null, ['app_name' => 'Map <b>OS</b>', 'per_page' => '10', 'os_status_list' => '["Aberto"]'], $env),
                'erros' => [],
                'pode_backup' => true,
            ]);

            $this->assertStringNotContainsString('senha-secreta', $html, $aba);
            $this->assertStringNotContainsString('chave-secreta', $html, $aba);
            $this->assertStringNotContainsString('<b>OS</b>', $html, $aba);
            $this->assertStringNotContainsString('<script', $html, $aba);
            $this->assertStringContainsString('hash-csrf', $html, $aba);
        }
    }
}
