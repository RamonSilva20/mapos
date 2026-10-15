<?php

require_once APPPATH . 'helpers/manutencao_helper.php';

if (! function_exists('base_url')) {
    function base_url($uri = '')
    {
        return 'http://mapos.test/' . ltrim((string) $uri, '/');
    }
}

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Banco com migrations pendentes (#3006): o painel mostra a tela de
 * atualização no lugar da rota pedida. Os testes executam o
 * MY_Controller::exigirBancoAtualizado() com o banco em SQLite e a view real.
 */
final class BancoDesatualizadoTest extends MaposTestCase
{
    private function ultimaMigration(): string
    {
        $arquivos = glob(APPPATH . 'database/migrations/*_*.php') ?: [];
        sort($arquivos, SORT_STRING);

        return substr(basename((string) end($arquivos)), 0, 14);
    }

    private function bancoNaVersao(string $versao): void
    {
        $this->db->query('CREATE TABLE migrations (version TEXT)');
        $this->db->query('INSERT INTO migrations (version) VALUES (?)', [$versao]);
    }

    private function controller(string $classe, string $metodo, bool $cSistema, bool $ajax = false): BancoDesatualizadoControllerTestavel
    {
        $c = $this->makeInstance(BancoDesatualizadoControllerTestavel::class);
        $c->db = $this->db;
        $c->router = (object) ['class' => $classe, 'method' => $metodo];
        $c->permission = new class($cSistema) {
            public function __construct(private bool $cSistema)
            {
            }

            public function checkPermission($id, $atividade)
            {
                return $atividade === 'cSistema' && $this->cSistema;
            }
        };
        $c->session = new class() {
            public ?string $erro = null;

            public function userdata($chave)
            {
                return 1;
            }

            public function flashdata($chave)
            {
                return $chave === 'error' ? $this->erro : null;
            }
        };
        $c->security = new class() {
            public function get_csrf_token_name()
            {
                return 'MAPOS_CSRF_TOKEN';
            }

            public function get_csrf_hash()
            {
                return 'hash-csrf';
            }
        };
        $c->input = new class($ajax) {
            public function __construct(private bool $ajax)
            {
            }

            public function is_ajax_request()
            {
                return $this->ajax;
            }
        };
        $c->load = new class() {
            public function helper($nome)
            {
            }

            public function view($arquivo, array $dados)
            {
                $render = static function (array $__dados, string $__view) {
                    extract($__dados);
                    ob_start();
                    include APPPATH . 'views/' . $__view . '.php';

                    return ob_get_clean();
                };
                $this->html = $render($dados, $arquivo);
            }

            public string $html = '';
        };
        $c->output = new class($c->load) {
            public ?int $status = null;

            public string $corpo = '';

            public bool $enviado = false;

            public function __construct(private object $load)
            {
            }

            public function set_status_header($status)
            {
                $this->status = $status;

                return $this;
            }

            public function set_content_type($tipo, $charset = null)
            {
                return $this;
            }

            public function set_output($corpo)
            {
                $this->corpo = $corpo;

                return $this;
            }

            public function _display()
            {
                $this->enviado = true;
                $this->corpo = $this->corpo !== '' ? $this->corpo : $this->load->html;
            }
        };

        return $c;
    }

    /**
     * Roda o portão do construtor; se ele deixar passar, o "método da rota" executa.
     */
    private function requisitar(BancoDesatualizadoControllerTestavel $c): bool
    {
        try {
            $this->invokeMethod($c, 'exigirBancoAtualizado');
        } catch (BancoDesatualizadoInterrompido) {
            return false;
        }

        $c->metodoExecutou = true;

        return true;
    }

    public function testComPendenciaBloqueiaARotaENaoRodaOMetodo(): void
    {
        $this->bancoNaVersao('20240503170400');
        $c = $this->controller('os', 'gerenciar', true);

        $this->assertFalse($this->requisitar($c));

        $this->assertFalse($c->metodoExecutou);
        $this->assertSame(503, $c->output->status);
        $this->assertTrue($c->output->enviado);
        $this->assertStringContainsString('Banco de dados desatualizado', $c->output->corpo);
    }

    public function testAdministradorVeOBotaoDeAtualizarComCsrfEPost(): void
    {
        $this->bancoNaVersao('20240503170400');
        $c = $this->controller('os', 'gerenciar', true);
        $this->requisitar($c);

        $html = $c->output->corpo;
        $this->assertStringContainsString('Atualizar banco de dados', $html);
        $this->assertStringContainsString('method="post" action="http://mapos.test/index.php/mapos/atualizarBanco"', $html);
        $this->assertStringContainsString('name="MAPOS_CSRF_TOKEN" value="hash-csrf"', $html);
        $this->assertStringContainsString('http://mapos.test/index.php/login/sair', $html);
        $this->assertStringContainsString('Faça um backup', $html);
        $this->assertStringNotContainsString('<script>', $html, 'Sem script inline.');
    }

    public function testQuemNaoTemCSistemaNaoVeOBotaoMasVeSair(): void
    {
        $this->bancoNaVersao('20240503170400');
        $c = $this->controller('clientes', 'gerenciar', false);

        $this->assertFalse($this->requisitar($c));

        $html = $c->output->corpo;
        $this->assertStringNotContainsString('Atualizar banco de dados', $html);
        $this->assertStringNotContainsString('atualizarBanco', $html);
        $this->assertStringContainsString('Avise o administrador', $html);
        $this->assertStringContainsString('login/sair', $html);
        $this->assertSame(503, $c->output->status);
    }

    public function testMostraOErroDaAtualizacaoQueFalhou(): void
    {
        $this->bancoNaVersao('20240503170400');
        $c = $this->controller('mapos', 'painel', true);
        $c->session->erro = 'A atualização do banco parou: <b>falha</b>';
        $this->requisitar($c);

        $this->assertStringContainsString('A atualização do banco parou: &lt;b&gt;falha&lt;/b&gt;', $c->output->corpo);
    }

    public function testAjaxRecebe503Json(): void
    {
        $this->bancoNaVersao('20240503170400');
        $c = $this->controller('os', 'gerenciar', true, true);

        $this->assertFalse($this->requisitar($c));

        $this->assertFalse($c->metodoExecutou);
        $this->assertSame(503, $c->output->status);
        $dados = json_decode($c->output->corpo, true);
        $this->assertFalse($dados['result']);
        $this->assertNotSame('', $dados['message']);
    }

    public function testAtualizarBancoPassaComPendencia(): void
    {
        $this->bancoNaVersao('20240503170400');
        $c = $this->controller('Mapos', 'atualizarBanco', true);

        $this->assertTrue($this->requisitar($c));

        $this->assertTrue($c->metodoExecutou);
        $this->assertNull($c->output->status);
    }

    public function testBancoSemTabelaDeMigrationsTambemBloqueia(): void
    {
        $c = $this->controller('os', 'gerenciar', true);

        $this->assertFalse($this->requisitar($c));
        $this->assertSame(503, $c->output->status);
    }

    public function testSemPendenciaNadaMuda(): void
    {
        $this->bancoNaVersao($this->ultimaMigration());
        $c = $this->controller('os', 'gerenciar', false);

        $this->assertTrue($this->requisitar($c));

        $this->assertTrue($c->metodoExecutou);
        $this->assertNull($c->output->status);
        $this->assertSame('', $c->output->corpo);
    }

    public function testOPortaoFicaAntesDaPermissaoENaoDependeDoLayout(): void
    {
        $base = (string) file_get_contents(APPPATH . 'core/MY_Controller.php');
        $this->assertLessThan(strpos($base, '$this->verificarPermissaoDaRota();'), strpos($base, '$this->exigirBancoAtualizado();'));
        $this->assertLessThan(strpos($base, '$this->exigirDoisFatores();'), strpos($base, '$this->exigirBancoAtualizado();'));

        $view = (string) file_get_contents(APPPATH . 'views/mapos/banco-desatualizado.php');
        $this->assertStringNotContainsString('tema/', $view);
        $this->assertStringNotContainsString('->db', $view);
    }

    public function testLogoutNaoPassaPeloPortao(): void
    {
        // O logout é Login::sair e o Login não estende MY_Controller.
        $this->assertDoesNotMatchRegularExpression('/class\s+Login\s+extends\s+MY_Controller/', (string) file_get_contents(APPPATH . 'controllers/Login.php'));
        $this->assertStringContainsString('function sair', (string) file_get_contents(APPPATH . 'controllers/Login.php'));
    }
}

final class BancoDesatualizadoInterrompido extends RuntimeException
{
}

/**
 * MY_Controller sem o construtor, com o encerramento trocado por exceção.
 */
final class BancoDesatualizadoControllerTestavel extends MY_Controller
{
    public $db;

    public $router;

    public $permission;

    public $session;

    public $security;

    public $input;

    public $load;

    public $output;

    public bool $metodoExecutou = false;

    protected function encerrar()
    {
        throw new BancoDesatualizadoInterrompido('exit');
    }
}
