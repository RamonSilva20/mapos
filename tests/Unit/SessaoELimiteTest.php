<?php

require_once APPPATH . 'libraries/Limite_login.php';
require_once APPPATH . 'models/Mapos_model.php';
require_once APPPATH . 'models/Usuarios_model.php';

/**
 * Hardening da sessão do painel e das confirmações de senha (#2879), contra um
 * SQLite em memória: o que roda aqui é o código real (model, biblioteca e
 * funções de ajuda), não só o texto dele.
 */
final class SessaoELimiteTest extends MaposTestCase
{
    private Mapos_model $modelo;

    private int $agora;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db->query('CREATE TABLE login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            escopo VARCHAR(20) NOT NULL,
            tipo VARCHAR(10) NOT NULL,
            chave CHAR(64) NOT NULL,
            falhas INTEGER NOT NULL DEFAULT 0,
            bloqueado_ate DATETIME NULL,
            ultima_falha DATETIME NOT NULL
        )');
        $this->db->query('CREATE UNIQUE INDEX login_attempts_chave ON login_attempts (escopo, tipo, chave)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, senha TEXT, situacao INTEGER DEFAULT 1, dataExpiracao TEXT NULL, permissoes_id INTEGER, totp_ativado_em TEXT NULL, sessao_versao INTEGER NOT NULL DEFAULT 0)');
        $this->db->query('CREATE TABLE permissoes (idPermissao INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, situacao INTEGER DEFAULT 1, exigir_2fa INTEGER DEFAULT 0)');
        $this->db->query("INSERT INTO permissoes (nome, situacao, exigir_2fa) VALUES ('Livre', 1, 0), ('Exige', 1, 1), ('Desligado', 0, 0)");
        $this->db->query("INSERT INTO usuarios (nome, email, senha, permissoes_id) VALUES ('Ana', 'ana@x.com', 'h', 1)");

        $this->modelo = $this->makeInstance(Mapos_model::class);
        $this->setPrivateProperty($this->modelo, 'db', $this->db);

        $this->agora = strtotime('2026-10-14 10:00:00');
    }

    private function limite(): Limite_login
    {
        return new Limite_login([
            'db' => $this->db,
            'segredo' => 'segredo-de-teste',
            'relogio' => fn () => $this->agora,
            'auditar' => function (string $tarefa, string $ip) {
            },
        ]);
    }

    // --- Achado 11: confirmações de senha com limite ----------------------

    public function testConfirmacaoDeSenhaBloqueiaNaQuintaFalhaSemConferirMais(): void
    {
        $limite = $this->limite();
        $hash = password_hash('certa-123', PASSWORD_BCRYPT, ['cost' => 4]);

        for ($i = 0; $i < 5; $i++) {
            $this->assertSame(Limite_login::SENHA_ERRADA, $limite->confirmarSenha('ana@x.com', '10.0.0.1', 'errada-' . $i, $hash));
        }

        // Bloqueada: nem a senha certa passa, e o outro e-mail não é afetado.
        $this->assertSame(Limite_login::SENHA_BLOQUEADA, $limite->confirmarSenha('ana@x.com', '10.0.0.1', 'certa-123', $hash));
        $this->assertSame(Limite_login::SENHA_OK, $limite->confirmarSenha('bia@x.com', '10.0.0.2', 'certa-123', $hash));

        // Passado o bloqueio, a senha certa volta a valer e zera o contador do e-mail.
        $this->agora += 61;
        $this->assertSame(Limite_login::SENHA_OK, $limite->confirmarSenha('ana@x.com', '10.0.0.1', 'certa-123', $hash));
        $this->assertFalse($limite->bloqueado('confirmacao', 'ana@x.com', '10.0.0.9'));
    }

    public function testConfirmacaoNaoSomaComOLoginDoPainel(): void
    {
        $limite = $this->limite();
        $hash = password_hash('certa-123', PASSWORD_BCRYPT, ['cost' => 4]);

        for ($i = 0; $i < 5; $i++) {
            $limite->confirmarSenha('ana@x.com', '10.0.0.1', 'errada', $hash);
        }

        $this->assertTrue($limite->bloqueado('confirmacao', 'ana@x.com', '10.0.0.1'));
        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));
    }

    public function testPedidosDeResetContamPorEmailEIpNoEscopoProprio(): void
    {
        $limite = $this->limite();

        for ($i = 0; $i < 4; $i++) {
            $this->assertFalse($limite->bloqueado('reset', 'cli@x.com', '10.0.0.1'));
            $limite->registrarFalha('reset', 'cli@x.com', '10.0.0.1');
        }

        $this->assertFalse($limite->bloqueado('reset', 'cli@x.com', '10.0.0.1'));
        $limite->registrarFalha('reset', 'cli@x.com', '10.0.0.1');
        $this->assertTrue($limite->bloqueado('reset', 'cli@x.com', '10.0.0.1'));
        // Não trava o login do cliente nem o de outro e-mail.
        $this->assertFalse($limite->bloqueado('cliente', 'cli@x.com', '10.0.0.9'));
        $this->assertFalse($limite->bloqueado('reset', 'outro@x.com', '10.0.0.9'));
    }

    public function testEscopoDesconhecidoContinuaRecusado(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->limite()->bloqueado('qualquer', 'ana@x.com', '10.0.0.1');
    }

    // --- Achado 11: senha conferida mesmo sem conta ------------------------

    public function testSenhaConfereComEmSemConta(): void
    {
        $hash = password_hash('certa-123', PASSWORD_DEFAULT);

        $this->assertTrue(senhaConfere('certa-123', $hash));
        $this->assertFalse(senhaConfere('errada', $hash));
        $this->assertFalse(senhaConfere('certa-123', null));
        $this->assertFalse(senhaConfere('certa-123', ''));
    }

    public function testSemContaGastaOTempoDeUmBcryptReal(): void
    {
        $hash = password_hash('certa-123', PASSWORD_DEFAULT);
        senhaConfere('aquece', null); // o hash fictício nasce na primeira chamada

        $inicio = hrtime(true);
        senhaConfere('errada', $hash);
        $comConta = hrtime(true) - $inicio;

        $inicio = hrtime(true);
        senhaConfere('errada', null);
        $semConta = hrtime(true) - $inicio;

        // Sem a verificação fictícia o caminho sem conta levava microssegundos.
        $this->assertGreaterThan($comConta * 0.3, $semConta);
    }

    public function testContaExpirada(): void
    {
        $this->assertFalse(contaExpirada(null));
        $this->assertFalse(contaExpirada('   '));
        $this->assertTrue(contaExpirada('2000-01-01'));
        $this->assertFalse(contaExpirada('2999-01-01'));
    }

    // --- Achado 9: sessão revalidada no banco ------------------------------

    public function testEstadoDaSessaoLeAContaDoBanco(): void
    {
        $estado = $this->modelo->estadoDaSessao(1);

        $this->assertSame(1, $estado['situacao']);
        $this->assertFalse($estado['expirada']);
        $this->assertTrue($estado['grupo_ativo']);
        $this->assertSame(1, $estado['permissoes_id']);
        $this->assertSame(0, $estado['versao']);
        $this->assertFalse($estado['exige_2fa_cadastro']);
        $this->assertNull($this->modelo->estadoDaSessao(99));
    }

    public function testEstadoAcompanhaMudancasFeitasDepoisDoLogin(): void
    {
        $this->db->query("UPDATE usuarios SET dataExpiracao = '2000-01-01', permissoes_id = 2 WHERE idUsuarios = 1");
        $estado = $this->modelo->estadoDaSessao(1);

        $this->assertTrue($estado['expirada']);
        $this->assertSame(2, $estado['permissoes_id']);
        $this->assertTrue($estado['exige_2fa_cadastro']);

        $this->db->query("UPDATE permissoes SET situacao = 0 WHERE idPermissao = 2");
        $this->assertFalse($this->modelo->estadoDaSessao(1)['grupo_ativo']);
    }

    public function testInvalidarSessoesSomaUmEDevolveAVersaoNova(): void
    {
        $this->assertSame(1, $this->modelo->invalidarSessoes(1));
        $this->assertSame(2, $this->modelo->invalidarSessoes(1));
        $this->assertSame(2, $this->modelo->estadoDaSessao(1)['versao']);
    }

    public function testSemAColunaDaMigrationNadaEInvalidadoENadaQuebra(): void
    {
        $this->db->query('DROP TABLE usuarios');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, senha TEXT, situacao INTEGER DEFAULT 1, dataExpiracao TEXT NULL, permissoes_id INTEGER)');
        $this->db->query("INSERT INTO usuarios (nome, email, senha, permissoes_id) VALUES ('Ana', 'ana@x.com', 'h', 1)");

        $this->assertNull($this->modelo->invalidarSessoes(1));
        $estado = $this->modelo->estadoDaSessao(1);
        $this->assertSame(0, $estado['versao']);
        $this->assertFalse($estado['exige_2fa_cadastro']);
    }

    private function motivo(?array $estado, int $versaoDaSessao): ?string
    {
        $controller = $this->makeInstance(MY_Controller::class);

        return $this->invokeMethod($controller, 'motivoDeEncerramento', [$estado, $versaoDaSessao]);
    }

    public function testSessaoValidaNaoTemMotivoParaEncerrar(): void
    {
        $this->assertNull($this->motivo($this->modelo->estadoDaSessao(1), 0));
    }

    public function testSessaoCaiQuandoAContaMudaNoBanco(): void
    {
        $this->assertStringContainsString('desativada', (string) $this->motivo(null, 0));

        $this->db->query('UPDATE usuarios SET situacao = 0 WHERE idUsuarios = 1');
        $this->assertStringContainsString('desativada', (string) $this->motivo($this->modelo->estadoDaSessao(1), 0));

        $this->db->query("UPDATE usuarios SET situacao = 1, dataExpiracao = '2000-01-01' WHERE idUsuarios = 1");
        $this->assertStringContainsString('expirada', (string) $this->motivo($this->modelo->estadoDaSessao(1), 0));

        $this->db->query('UPDATE usuarios SET dataExpiracao = NULL, permissoes_id = 3 WHERE idUsuarios = 1');
        $this->assertStringContainsString('grupo de permissão', (string) $this->motivo($this->modelo->estadoDaSessao(1), 0));
    }

    public function testTrocaDeSenhaDerrubaAsOutrasSessoesMasNaoAAtual(): void
    {
        // Duas sessões abertas na versão 0; uma delas troca a senha.
        $versaoNova = $this->modelo->invalidarSessoes(1);
        $estado = $this->modelo->estadoDaSessao(1);

        $this->assertStringContainsString('senha', (string) $this->motivo($estado, 0));
        $this->assertNull($this->motivo($estado, $versaoNova));
    }

    // --- Ressalvas: flag do 2FA e confirmação sem senha --------------------

    private function controllerComSessao(array $dados): object
    {
        $sessao = new class($dados) {
            public function __construct(public array $dados)
            {
            }

            public function userdata(string $chave)
            {
                return $this->dados[$chave] ?? null;
            }

            public function set_userdata($chave, $valor = null): void
            {
                $this->dados[$chave] = $valor;
            }

            public function unset_userdata($chave): void
            {
                unset($this->dados[$chave]);
            }
        };

        $controller = new class() extends MY_Controller {
            public $session;

            public $mapos_model;

            public $load;

            public function __construct()
            {
            }

            public function validar(): void
            {
                $this->validarSessaoDoUsuario();
            }
        };
        $controller->session = $sessao;
        $controller->mapos_model = $this->modelo;
        $controller->load = new class() {
            public function model($nome): void
            {
            }
        };

        return $controller;
    }

    public function testFlagDoDoisFatoresLigaEDesligaConformeOBanco(): void
    {
        $controller = $this->controllerComSessao(['id_admin' => 1, 'permissao' => 1, 'sessao_versao' => 0]);

        // Grupo passa a exigir 2FA: a flag liga e a permissão acompanha o banco.
        $this->db->query('UPDATE usuarios SET permissoes_id = 2 WHERE idUsuarios = 1');
        $controller->validar();
        $this->assertTrue($controller->session->userdata('exigir_2fa_cadastro'));
        $this->assertSame(2, $controller->session->userdata('permissao'));

        // Voltou a um grupo sem exigência: a flag desliga, sem novo login.
        $this->db->query('UPDATE usuarios SET permissoes_id = 1 WHERE idUsuarios = 1');
        $controller->validar();
        $this->assertNull($controller->session->userdata('exigir_2fa_cadastro'));
        $this->assertSame(1, $controller->session->userdata('permissao'));

        // Ativou o 2FA no grupo que exige: também solta.
        $this->db->query("UPDATE usuarios SET permissoes_id = 2, totp_ativado_em = '2026-10-14 10:00:00' WHERE idUsuarios = 1");
        $controller->session->set_userdata('exigir_2fa_cadastro', true);
        $controller->validar();
        $this->assertNull($controller->session->userdata('exigir_2fa_cadastro'));
    }

    public function testConfirmacaoSemSenhaNaoContaFalhaNemLibera(): void
    {
        $limite = $this->limite();
        $hash = password_hash('certa-123', PASSWORD_BCRYPT, ['cost' => 4]);

        for ($i = 0; $i < 20; $i++) {
            $this->assertSame(Limite_login::SENHA_ERRADA, $limite->confirmarSenha('ana@x.com', '10.0.0.1', '', $hash));
        }

        $this->assertFalse($limite->bloqueado('confirmacao', 'ana@x.com', '10.0.0.1'));
        $this->assertSame(0, (int) $this->db->count_all_results('login_attempts'));
        $this->assertSame(Limite_login::SENHA_OK, $limite->confirmarSenha('ana@x.com', '10.0.0.1', 'certa-123', $hash));
    }

    public function testSenhaEmBrancoNuncaConfere(): void
    {
        $this->assertFalse(senhaConfere('', password_hash('certa-123', PASSWORD_BCRYPT, ['cost' => 4])));
    }

    public function testTodosOsLoginsGastamOBcryptAntesDeTestarAConta(): void
    {
        $entradas = [
            'controllers/Login.php' => ['verificarLogin', '$user'],
            'controllers/Mine.php' => ['login', '$cliente'],
            'controllers/api/v1/UsuariosController.php' => ['login_post', '$user'],
            'controllers/api/v1/client/ClientLoginController.php' => ['login_post', '$cliente'],
        ];

        foreach ($entradas as $arquivo => [$metodo, $variavel]) {
            $inteiro = (string) file_get_contents(APPPATH . $arquivo);
            $inicio = strpos($inteiro, 'function ' . $metodo . '(');
            $this->assertNotFalse($inicio, $arquivo . ' tem ' . $metodo . '().');
            $codigo = substr($inteiro, $inicio);
            $chamada = strpos($codigo, 'senhaConfere(');
            $teste = preg_match('/if \(\s*!?\s*' . preg_quote($variavel, '/') . '\b/', $codigo, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : false;

            $this->assertNotFalse($chamada, $arquivo . ' usa senhaConfere().');
            $this->assertNotFalse($teste, $arquivo . ' testa a conta.');
            $this->assertLessThan($teste, $chamada, $arquivo . ': senhaConfere() vem antes do teste da conta.');
            $this->assertDoesNotMatchRegularExpression('/' . preg_quote($variavel, '/') . ' && senhaConfere|! ?' . preg_quote($variavel, '/') . ' \|\| ! ?senhaConfere|password_verify\(/', $codigo, $arquivo . ' não pode pular o bcrypt sem conta.');
        }
    }

    public function testAlterarSenhaESoPorPostEAsOutrasConfirmacoesTambem(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');

        foreach (['alterarSenha', 'novosCodigosDoisFatores', 'desativarDoisFatores'] as $metodo) {
            preg_match('/function ' . $metodo . '\(\).*?\n    }\n/s', $codigo, $trecho);
            $this->assertNotEmpty($trecho, $metodo);
            $posPost = strpos($trecho[0], "input->method() !== 'post'");
            $posSenha = strpos($trecho[0], 'confirmarSenhaDoUsuario(');
            $this->assertNotFalse($posPost, $metodo . ' exige POST.');
            $this->assertNotFalse($posSenha, $metodo . ' confere a senha.');
            $this->assertLessThan($posSenha, $posPost, $metodo . ': o POST é exigido antes de conferir a senha.');
        }
    }

    private function usuariosModelo(): Usuarios_model
    {
        $biblioteca = new class() {
            public function incrementarVersao(string $tabela, string $campoId, int $id): void
            {
            }

            public function revogarDoUsuario(int $id): void
            {
            }
        };

        $model = new class() extends Usuarios_model {
            public $load;

            public $refresh_token;

            public $authorization_token;

            public function __construct()
            {
            }
        };
        $model->db = $this->db;
        // Só library(): edit() não pode carregar models nem depender da sessão.
        $model->load = new class() {
            public function library(string $nome): void
            {
            }
        };
        $model->refresh_token = $biblioteca;
        $model->authorization_token = $biblioteca;

        return $model;
    }

    public function testTrocarASenhaPeloAdminDerrubaAsSessoesDoPainelDoUsuario(): void
    {
        $usuarios = $this->usuariosModelo();

        $this->assertTrue($usuarios->edit('usuarios', ['nome' => 'Ana B'], 'idUsuarios', 1));
        $this->assertSame(0, $this->modelo->estadoDaSessao(1)['versao'], 'Sem trocar a senha a sessão não cai.');

        $this->assertTrue($usuarios->edit('usuarios', ['senha' => password_hash('nova-123', PASSWORD_BCRYPT, ['cost' => 4])], 'idUsuarios', 1));
        $this->assertSame(1, $this->modelo->estadoDaSessao(1)['versao']);
    }

    public function testEditarUsuarioSemAMigrationDaSessaoNaoQuebra(): void
    {
        $this->db->query('DROP TABLE usuarios');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, email TEXT, senha TEXT, situacao INTEGER DEFAULT 1, permissoes_id INTEGER)');
        $this->db->query("INSERT INTO usuarios (nome, email, senha, permissoes_id) VALUES ('Ana', 'ana@x.com', 'h', 1)");

        $this->assertTrue($this->usuariosModelo()->edit('usuarios', ['senha' => 'novo-hash'], 'idUsuarios', 1));
    }
}
