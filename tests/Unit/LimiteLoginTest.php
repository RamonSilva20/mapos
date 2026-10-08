<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once APPPATH . 'libraries/Limite_login.php';

/**
 * Limite de tentativas de login (#2870), contra um SQLite em memória com a
 * mesma tabela da migration 20261009000000_create_login_attempts.
 */
final class LimiteLoginTest extends MaposTestCase
{
    private int $agora;

    /** @var list<array{0: string, 1: string}> */
    private array $auditoria = [];

    protected function setUp(): void
    {
        // O MaposTestCase já abre o SQLite em memória em $this->db.
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

        $this->agora = strtotime('2026-10-09 10:00:00');
        $this->auditoria = [];
    }

    private function limite(string $segredo = 'segredo-de-teste'): Limite_login
    {
        return new Limite_login([
            'db' => $this->db,
            'segredo' => $segredo,
            'relogio' => fn () => $this->agora,
            'auditar' => function (string $tarefa, string $ip) {
                $this->auditoria[] = [$tarefa, $ip];
            },
        ]);
    }

    private function falhar(Limite_login $limite, int $vezes, string $email = 'ana@x.com', string $ip = '10.0.0.1', string $escopo = 'usuario'): void
    {
        for ($i = 0; $i < $vezes; $i++) {
            $limite->registrarFalha($escopo, $email, $ip);
        }
    }

    public static function duracoes(): array
    {
        return [
            [0, 0], [4, 0], [5, 60], [6, 120], [7, 240], [8, 480], [9, 960], [10, 1920], [11, 3600], [40, 3600],
        ];
    }

    #[DataProvider('duracoes')]
    public function testBackoffProgressivoDoEmail(int $falhas, int $segundos): void
    {
        $this->assertSame($segundos, Limite_login::duracaoBloqueio($falhas, Limite_login::LIMITE_EMAIL));
    }

    public function testBloqueiaOEmailNaQuintaFalhaPorUmMinuto(): void
    {
        $limite = $this->limite();

        $this->falhar($limite, 4);
        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));

        $this->assertTrue($limite->registrarFalha('usuario', 'ana@x.com', '10.0.0.1'));
        // Mesmo e-mail com outro IP e outra grafia: continua bloqueado.
        $this->assertTrue($limite->bloqueado('usuario', '  ANA@X.com ', '10.9.9.9'));

        $this->agora += 61;
        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.9.9.9'));
    }

    public function testCadaFalhaDepoisDoLimiteDobraOBloqueio(): void
    {
        $limite = $this->limite();
        $this->falhar($limite, 5);

        $this->agora += 61;
        $limite->registrarFalha('usuario', 'ana@x.com', '10.0.0.1');

        $this->agora += 119;
        $this->assertTrue($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));
        $this->agora += 2;
        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));
    }

    public function testEmailInexistenteTambemBloqueia(): void
    {
        $limite = $this->limite();
        $this->falhar($limite, 5, 'ninguem@x.com');

        $this->assertTrue($limite->bloqueado('usuario', 'ninguem@x.com', '10.1.1.1'));
    }

    public function testIpBloqueiaAoTentarVariasContas(): void
    {
        $limite = $this->limite();
        for ($i = 0; $i < Limite_login::LIMITE_IP; $i++) {
            $limite->registrarFalha('usuario', "conta{$i}@x.com", '10.0.0.7');
        }

        $this->assertTrue($limite->bloqueado('usuario', 'outra@x.com', '10.0.0.7'));
        $this->assertFalse($limite->bloqueado('usuario', 'outra@x.com', '10.0.0.8'));
    }

    public function testSucessoZeraOEmailMasNaoOIp(): void
    {
        $limite = $this->limite();
        $this->falhar($limite, 4);
        $limite->registrarSucesso('usuario', 'ANA@x.com');

        // O contador do e-mail recomeçou: mais 4 falhas não bloqueiam.
        $this->falhar($limite, 4);
        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));

        $linhaIp = $this->db->where('tipo', 'ip')->get('login_attempts')->row();
        $this->assertSame(8, (int) $linhaIp->falhas);
    }

    public function testEscoposSaoIndependentes(): void
    {
        $limite = $this->limite();
        $this->falhar($limite, 5, 'ana@x.com', '10.0.0.1', 'cliente');

        $this->assertTrue($limite->bloqueado('cliente', 'ana@x.com', '10.0.0.1'));
        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));
    }

    public function testContadorRecomecaDepoisDaJanela(): void
    {
        $limite = $this->limite();
        $this->falhar($limite, 4);

        $this->agora += Limite_login::JANELA_SEGUNDOS + 1;
        $limite->registrarFalha('usuario', 'ana@x.com', '10.0.0.1');

        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));
    }

    public function testNaoGuardaEmailNemIpEmClaro(): void
    {
        $this->falhar($this->limite(), 1, 'ana@x.com', '10.0.0.1');

        $dump = json_encode($this->db->get('login_attempts')->result_array());
        $this->assertStringNotContainsString('ana@x.com', $dump);
        $this->assertStringNotContainsString('10.0.0.1', $dump);
        $this->assertMatchesRegularExpression('/"chave":"[0-9a-f]{64}"/', $dump);
    }

    /**
     * A chave é um HMAC com a encryption_key: sem o segredo, não dá para
     * conferir um e-mail candidato contra a tabela.
     */
    public function testChaveDependeDoSegredo(): void
    {
        $this->falhar($this->limite('segredo-a'), 1, 'ana@x.com', '10.0.0.1');
        $chave = $this->db->where('tipo', 'email')->get('login_attempts')->row()->chave;

        $this->assertSame(hash_hmac('sha256', 'email|ana@x.com', 'segredo-a'), $chave);
        $this->assertNotSame(hash('sha256', 'email|ana@x.com'), $chave);

        // Com outro segredo, o mesmo e-mail é outra chave.
        $this->falhar($this->limite('segredo-b'), 1, 'ana@x.com', '10.0.0.9');
        $this->assertSame(2, $this->db->where('tipo', 'email')->count_all_results('login_attempts'));
    }

    public function testSegundosRestantesSeguemOBloqueioMaisLongo(): void
    {
        $limite = $this->limite();
        $this->assertSame(0, $limite->segundosRestantes('usuario', 'ana@x.com', '10.0.0.1'));

        $this->falhar($limite, 5);
        $this->assertSame(60, $limite->segundosRestantes('usuario', 'ana@x.com', '10.0.0.1'));

        $this->agora += 61;
        $limite->registrarFalha('usuario', 'ana@x.com', '10.0.0.1');
        $this->agora += 20;
        $this->assertSame(100, $limite->segundosRestantes('usuario', 'ana@x.com', '10.0.0.1'));

        $this->agora += 101;
        $this->assertSame(0, $limite->segundosRestantes('usuario', 'ana@x.com', '10.0.0.1'));
    }

    public function testBloqueioVaiParaAAuditoriaSemRevelarOEmail(): void
    {
        $this->falhar($this->limite(), 5, 'ana@x.com', '10.0.0.1');

        $this->assertCount(1, $this->auditoria);
        [$tarefa, $ip] = $this->auditoria[0];
        $this->assertSame('10.0.0.1', $ip);
        $this->assertStringContainsString('Login bloqueado por 1 min após 5 tentativas', $tarefa);
        $this->assertStringContainsString('painel, por e-mail', $tarefa);
        $this->assertStringNotContainsString('ana@x.com', $tarefa);
    }

    public function testLinhasAntigasSaoApagadas(): void
    {
        $limite = $this->limite();
        $this->falhar($limite, 1, 'velho@x.com', '10.0.0.2');

        $this->agora += 2 * 86400;
        $this->falhar($limite, 1, 'novo@x.com', '10.0.0.3');

        $this->assertSame(2, $this->db->count_all_results('login_attempts'));
    }

    public function testSemATabelaOLimiteFicaDesligado(): void
    {
        $this->db->query('DROP TABLE login_attempts');
        $limite = $this->limite();

        $this->assertFalse($limite->registrarFalha('usuario', 'ana@x.com', '10.0.0.1'));
        $this->assertFalse($limite->bloqueado('usuario', 'ana@x.com', '10.0.0.1'));
    }

    public function testEscopoDesconhecidoFalha(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->limite()->bloqueado('admin', 'ana@x.com', '10.0.0.1');
    }

    /**
     * Os quatro pontos de login usam o limite, com a mensagem genérica e sem
     * "Usuário não encontrado".
     */
    public function testOsQuatroLoginsUsamOLimite(): void
    {
        $logins = [
            ['application/controllers/Login.php', 'verificarLogin', 'usuario'],
            ['application/controllers/Mine.php', 'login', 'cliente'],
            ['application/controllers/api/v1/UsuariosController.php', 'login_post', 'usuario'],
            ['application/controllers/api/v1/client/ClientLoginController.php', 'login_post', 'cliente'],
        ];

        foreach ($logins as [$arquivo, $metodo, $escopo]) {
            // Só o corpo do método de login, até o próximo método.
            $fonte = (string) file_get_contents(MAPOS_ROOT . '/' . $arquivo);
            $this->assertSame(1, preg_match('/function ' . $metodo . '\(\).*?(?=\n    (?:public|private|protected) function |\n}\s*$)/s', $fonte, $corpo), $arquivo);
            $codigo = $corpo[0];
            $this->assertMatchesRegularExpression("/->(bloqueado|segundosRestantes)\\('{$escopo}'/", $codigo, $arquivo);
            $this->assertStringContainsString("->registrarFalha('{$escopo}'", $codigo, $arquivo);
            $this->assertStringContainsString("->registrarSucesso('{$escopo}'", $codigo, $arquivo);
            $this->assertStringContainsString('Limite_login::MENSAGEM', $codigo, $arquivo);
            $this->assertStringNotContainsString('Usuário não encontrado', $codigo, $arquivo);
        }
    }
}
