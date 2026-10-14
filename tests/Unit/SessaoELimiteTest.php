<?php

require_once APPPATH . 'libraries/Limite_login.php';
require_once APPPATH . 'models/Mapos_model.php';

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
}
