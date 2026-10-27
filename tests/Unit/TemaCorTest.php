<?php

use PHPUnit\Framework\Attributes\DataProvider;

require_once APPPATH . 'models/Mapos_model.php';
require_once APPPATH . 'controllers/Mapos.php';
require_once APPPATH . 'database/migrations/20261027000000_add_tema_cor.php';

/**
 * Cor do tema do painel (#3032): resolução, atributos do <html>, preferência
 * por usuário, endpoint, migration e a aba Geral das Configurações.
 */
final class TemaCorTest extends MaposTestCase
{
    // --- Resolução ----------------------------------------------------------

    public function testAAllowListSaoAsCincoCores(): void
    {
        $this->assertSame(['padrao', 'azul', 'verde', 'violeta', 'grafite'], TEMA_CORES);
        $this->assertSame(TEMA_CORES, array_keys(temaCoresRotulos()));
    }

    public function testPreferenciaPessoalVenceOPadraoDoSistemaQueVenceOPadrao(): void
    {
        $this->assertSame('verde', temaCorConfiguracao(['tema_usuario_cor' => 'verde', 'app_tema_cor' => 'azul']));
        $this->assertSame('azul', temaCorConfiguracao(['tema_usuario_cor' => '', 'app_tema_cor' => 'azul']));
        $this->assertSame('azul', temaCorConfiguracao(['app_tema_cor' => ' AZUL ']));
        $this->assertSame('padrao', temaCorConfiguracao([]));
    }

    #[DataProvider('valoresForaDaAllowList')]
    public function testValorForaDaAllowListViraPadrao($valor): void
    {
        $this->assertSame('padrao', temaCorConfiguracao(['tema_usuario_cor' => $valor, 'app_tema_cor' => $valor]));
        $this->assertSame('azul', temaCorConfiguracao(['tema_usuario_cor' => $valor, 'app_tema_cor' => 'azul']), 'O inválido pessoal cai no padrão do sistema.');
    }

    public static function valoresForaDaAllowList(): array
    {
        return ['rosa' => ['rosa'], 'html' => ['"><script>x</script>'], 'null' => [null], 'array' => [['azul']], 'laranja' => ['laranja']];
    }

    // --- Atributos do <html> ------------------------------------------------

    public function testHtmlImprimeODataTemaSoParaCoresDaAllowList(): void
    {
        $this->assertSame(' data-tema-modo="claro" data-tema="azul"', temaAtributosHtml(['app_tema_modo' => 'claro', 'app_tema_cor' => 'azul']));
        $this->assertSame(' data-tema-modo="claro"', temaAtributosHtml(['app_tema_modo' => 'claro', 'app_tema_cor' => 'padrao']), 'O padrão (laranja) não leva atributo.');
        $this->assertSame(' data-tema-modo="claro"', temaAtributosHtml(['app_tema_modo' => 'claro']));
    }

    public function testHtmlMarcaQuandoACorVemDaPreferenciaDaPessoa(): void
    {
        $this->assertSame(
            ' data-tema-modo="claro" data-tema="grafite" data-tema-cor-usuario="1"',
            temaAtributosHtml(['app_tema_modo' => 'claro', 'app_tema_cor' => 'azul', 'tema_usuario_cor' => 'grafite'])
        );
        $this->assertSame(
            ' data-tema-modo="claro" data-tema-cor-usuario="1"',
            temaAtributosHtml(['app_tema_modo' => 'claro', 'app_tema_cor' => 'azul', 'tema_usuario_cor' => 'padrao']),
            'Escolher o padrão é uma preferência: vence o padrão azul do sistema.'
        );
        $this->assertStringNotContainsString('data-tema-cor-usuario', temaAtributosHtml(['app_tema_cor' => 'azul', 'tema_usuario_cor' => 'lixo']));
    }

    public function testHtmlNaoDeixaPassarMarcacaoNaCor(): void
    {
        $html = temaAtributosHtml(['tema_usuario_cor' => '" onload="x', 'app_tema_cor' => '"><script>']);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('onload', $html);
        $this->assertStringNotContainsString('data-tema="', $html);
    }

    public function testAreaDoClienteFicaNaMarca(): void
    {
        $html = temaAtributosHtml(['app_tema_modo' => 'escuro', 'app_tema_cor' => 'azul', 'tema_usuario_cor' => 'verde'], false);

        $this->assertSame(' class="dark" data-tema-modo="escuro" data-tema-fixo="1"', $html);
    }

    // --- Model --------------------------------------------------------------

    private function modelo(): Mapos_model
    {
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, tema_cor TEXT NULL)');
        $this->db->query("INSERT INTO usuarios (nome) VALUES ('Ana'), ('Bia')");
        $modelo = $this->makeInstance(Mapos_model::class);
        $this->setPrivateProperty($modelo, 'db', $this->db);

        return $modelo;
    }

    public function testSalvarACorDoUsuarioNoBanco(): void
    {
        $modelo = $this->modelo();

        $this->assertTrue($modelo->salvarTemaCor(1, 'violeta'));
        $this->assertSame('violeta', $this->db->where('idUsuarios', 1)->get('usuarios')->row()->tema_cor);
        $this->assertNull($this->db->where('idUsuarios', 2)->get('usuarios')->row()->tema_cor, 'Só o usuário pedido muda.');
    }

    public function testSalvarACorSemAColunaNaoGravaENaoQuebra(): void
    {
        $modelo = $this->modelo();
        $this->db->query('DROP TABLE usuarios');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY, nome TEXT)');

        $this->assertFalse($modelo->salvarTemaCor(1, 'azul'));
    }

    // --- Endpoint -----------------------------------------------------------

    /** Mapos sem o construtor, com entrada, sessão e saída falsas e o model real no SQLite. */
    private function controller(array $post, string $metodo = 'post', int $idAdmin = 1): Mapos
    {
        $controller = new class() extends Mapos {
            public $input;

            public $session;

            public $output;

            public $load;

            public $mapos_model;

            public function __construct()
            {
            }
        };
        $controller->input = new class($post, $metodo) {
            public function __construct(private array $post, private string $metodo)
            {
            }

            public function post($chave = null)
            {
                return $this->post[$chave] ?? null;
            }

            public function method()
            {
                return $this->metodo;
            }
        };
        $controller->session = new class($idAdmin) {
            public array $gravado = [];

            public function __construct(private int $id)
            {
            }

            public function userdata($chave)
            {
                return $chave === 'id_admin' ? $this->id : null;
            }

            public function set_userdata($chave, $valor = null)
            {
                $this->gravado[$chave] = $valor;
            }
        };
        $controller->output = new class() {
            public int $status = 0;

            public string $corpo = '';

            public function set_status_header($codigo)
            {
                $this->status = $codigo;

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
        };
        $controller->load = new class() {
            public function helper($nome)
            {
            }
        };
        $controller->mapos_model = $this->modelo();

        return $controller;
    }

    public function testEndpointGravaAPropriaCorDaSessaoEResponde200(): void
    {
        $controller = $this->controller(['cor' => ' Azul ', 'id' => '2', 'idUsuarios' => '2'], 'post', 1);
        $controller->salvarTemaCor();

        $this->assertSame(200, $controller->output->status);
        $this->assertSame(['result' => true, 'cor' => 'azul'], json_decode($controller->output->corpo, true));
        $this->assertSame('azul', $this->db->where('idUsuarios', 1)->get('usuarios')->row()->tema_cor);
        $this->assertNull($this->db->where('idUsuarios', 2)->get('usuarios')->row()->tema_cor, 'O id do POST é ignorado: grava só quem está logado.');
        $this->assertSame(['tema_cor_admin' => 'azul'], $controller->session->gravado);
    }

    #[DataProvider('pedidosNegados')]
    public function testEndpointNegaValorInvalidoEMetodoErradoSemGravar($cor, string $metodo): void
    {
        $controller = $this->controller(['cor' => $cor], $metodo);
        $controller->salvarTemaCor();

        $this->assertSame(422, $controller->output->status);
        $this->assertSame(['result' => false, 'cor' => null], json_decode($controller->output->corpo, true));
        $this->assertNull($this->db->where('idUsuarios', 1)->get('usuarios')->row()->tema_cor);
        $this->assertSame([], $controller->session->gravado);
    }

    public static function pedidosNegados(): array
    {
        return [
            'cor fora da lista' => ['rosa', 'post'],
            'cor vazia' => ['', 'post'],
            'sem cor' => [null, 'post'],
            'html' => ['<b>azul</b>', 'post'],
            'método GET' => ['azul', 'get'],
        ];
    }

    public function testEndpointSemAColunaRespondeResultFalse(): void
    {
        $controller = $this->controller(['cor' => 'azul']);
        $this->db->query('DROP TABLE usuarios');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY, nome TEXT)');
        $controller->salvarTemaCor();

        $this->assertSame(200, $controller->output->status);
        $this->assertFalse(json_decode($controller->output->corpo, true)['result']);
        $this->assertSame([], $controller->session->gravado, 'Sem gravar no banco a sessão não muda.');
    }

    public function testRotaLoginELogoutAcompanhamOModo(): void
    {
        $this->assertStringContainsString("'salvarTemaCor' => '*'", (string) file_get_contents(APPPATH . 'config/permissions_map.php'));
        $this->assertStringContainsString("'tema_cor_admin' => \$user->tema_cor", (string) file_get_contents(APPPATH . 'controllers/Login.php'));
        $this->assertStringContainsString("'tema_modo_admin', 'tema_cor_admin'", (string) file_get_contents(APPPATH . 'core/MY_Controller.php'));
        $this->assertStringContainsString("['configuration']['tema_usuario_cor']", (string) file_get_contents(APPPATH . 'core/MY_Controller.php'));
        $this->assertStringContainsString('data-tema-cor-url=', (string) file_get_contents(APPPATH . 'views/tema/topo.php'));
    }

    // --- Migration ----------------------------------------------------------

    private function migration()
    {
        $migration = new Migration_add_tema_cor();
        $migration->db = $this->db;
        $migration->dbforge = new class($this->db) {
            public function __construct(private $db)
            {
            }

            public function add_column($tabela, array $campos)
            {
                foreach ($campos as $nome => $def) {
                    $this->db->query("ALTER TABLE {$tabela} ADD COLUMN {$nome} VARCHAR({$def['constraint']}) NULL");
                }
            }

            public function drop_column($tabela, $nome)
            {
                $this->db->query("ALTER TABLE {$tabela} DROP COLUMN {$nome}");
            }
        };

        return $migration;
    }

    private function bancoAntesDaMigration(): void
    {
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT)');
        $this->db->query('CREATE TABLE configuracoes (idConfig INTEGER PRIMARY KEY AUTOINCREMENT, config VARCHAR(20) NOT NULL UNIQUE, valor TEXT NULL)');
    }

    public function testMigrationCriaAColunaEAConfiguracaoPadrao(): void
    {
        $this->bancoAntesDaMigration();

        $this->migration()->up();

        $this->assertTrue($this->db->field_exists('tema_cor', 'usuarios'));
        $this->assertSame('padrao', $this->db->where('config', 'app_tema_cor')->get('configuracoes')->row()->valor);
    }

    public function testMigrationEIdempotenteENaoSobrescreveEscolha(): void
    {
        $this->bancoAntesDaMigration();
        $migration = $this->migration();

        $migration->up();
        $this->db->where('config', 'app_tema_cor')->update('configuracoes', ['valor' => 'verde']);
        $migration->up();

        $this->assertSame(1, $this->db->where('config', 'app_tema_cor')->count_all_results('configuracoes'));
        $this->assertSame('verde', $this->db->where('config', 'app_tema_cor')->get('configuracoes')->row()->valor);
    }

    public function testMigrationDownDesfaz(): void
    {
        $this->bancoAntesDaMigration();
        $migration = $this->migration();

        $migration->up();
        $migration->down();

        $this->assertFalse($this->db->field_exists('tema_cor', 'usuarios'));
        $this->assertSame(0, $this->db->where('config', 'app_tema_cor')->count_all_results('configuracoes'));
    }

    // --- Configurações (aba Geral) -------------------------------------------

    public function testAbaGeralTemOCampoDaCorPadraoComAllowList(): void
    {
        $campos = configuracaoCampos('geral');

        $this->assertSame('opcao', $campos['app_tema_cor']['tipo']);
        $this->assertSame(TEMA_CORES, array_keys($campos['app_tema_cor']['opcoes']));
    }

    public function testCorForaDaListaEhRejeitadaENaoGravada(): void
    {
        [$config, , $erros] = configuracaoDadosDoFormulario('geral', ['app_name' => 'X', 'per_page' => '10', 'app_tema_modo' => 'claro', 'app_tema_cor' => 'rosa']);

        $this->assertSame(['app_tema_cor'], array_keys($erros));
        $this->assertArrayNotHasKey('app_tema_cor', $config);
    }

    /** O bug da revisão: a aba mostrava (e salvava) o tema de quem está logado como padrão do sistema. */
    public function testAbaGeralMostraOPadraoDoSistemaENaoAPreferenciaPessoal(): void
    {
        $configuracao = [
            'app_tema_modo' => 'claro', 'app_tema_cor' => 'azul',
            'tema_usuario_modo' => 'escuro', 'tema_usuario_cor' => 'grafite',
        ];

        $valores = configuracaoValores('geral', null, $configuracao, []);

        $this->assertSame('claro', $valores['app_tema_modo']);
        $this->assertSame('azul', $valores['app_tema_cor']);
    }

    public function testAbaGeralSemACorGravadaMostraOPadrao(): void
    {
        $valores = configuracaoValores('geral', null, ['tema_usuario_cor' => 'verde'], []);

        $this->assertSame('padrao', $valores['app_tema_cor'], 'Sem app_tema_cor (migration não rodou) vale o padrão, não a cor pessoal.');
    }

    public function testSalvarAAbaGeralNaoGravaAPreferenciaPessoal(): void
    {
        // O que o formulário envia é o que a tela mostrou: o padrão do sistema.
        $configuracao = ['app_name' => 'Oficina', 'per_page' => '10', 'app_tema_modo' => 'claro', 'app_tema_cor' => 'azul', 'tema_usuario_modo' => 'escuro', 'tema_usuario_cor' => 'grafite'];
        $post = configuracaoValores('geral', null, $configuracao, []);

        [$config, , $erros] = configuracaoDadosDoFormulario('geral', $post);

        $this->assertSame([], $erros);
        $this->assertSame('claro', $config['app_tema_modo']);
        $this->assertSame('azul', $config['app_tema_cor']);
        $this->assertNotContains('grafite', $config);
        $this->assertNotContains('escuro', $config);
    }

    public function testTextoAuxiliarDizOQueAPessoaEstaUsando(): void
    {
        $this->assertSame('Escuro · Grafite', configuracaoTemaPessoal(['app_tema_modo' => 'claro', 'app_tema_cor' => 'azul', 'tema_usuario_modo' => 'escuro', 'tema_usuario_cor' => 'grafite']));
        $this->assertSame('Claro · Azul', configuracaoTemaPessoal(['app_tema_modo' => 'claro', 'app_tema_cor' => 'azul']));
        $this->assertSame('Claro · Padrão (laranja)', configuracaoTemaPessoal([]));
    }
}
