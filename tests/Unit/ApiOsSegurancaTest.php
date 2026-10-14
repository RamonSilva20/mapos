<?php

require_once APPPATH . 'models/Os_model.php';
require_once APPPATH . 'controllers/api/v1/OsController.php';

/**
 * Hardening da API de OS (#2879): item e anotação precisam pertencer à OS da
 * URL, OS faturada ou cancelada não muda, o desconto é calculado no servidor e
 * os valores são validados. Executa os métodos reais do controller sobre um
 * SQLite; response() vira exceção (na API ele encerra a requisição).
 */
final class ApiOsSegurancaTest extends MaposTestCase
{
    private object $controller;

    private mixed $chaveAnterior;

    protected function setUp(): void
    {
        parent::setUp();

        // config.php lê a chave ao ser carregado (get_config() de um log).
        $this->chaveAnterior = $_ENV['APP_ENCRYPTION_KEY'] ?? null;
        $_ENV['APP_ENCRYPTION_KEY'] ??= 'chave-de-teste';

        foreach ([
            'CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, senha TEXT, celular TEXT, telefone TEXT, contato TEXT)',
            'CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, telefone TEXT, email TEXT)',
            'CREATE TABLE garantias (idGarantias INTEGER PRIMARY KEY AUTOINCREMENT, refGarantia TEXT, textoGarantia TEXT)',
            'CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, clientes_id INTEGER, usuarios_id INTEGER, garantias_id INTEGER, status TEXT, faturado INTEGER DEFAULT 0, desconto REAL DEFAULT 0, valor_desconto REAL DEFAULT 0, tipo_desconto TEXT)',
            'CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, precoVenda REAL, estoque REAL)',
            'CREATE TABLE servicos (idServicos INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, preco REAL)',
            'CREATE TABLE produtos_os (idProdutos_os INTEGER PRIMARY KEY AUTOINCREMENT, os_id INTEGER, produtos_id INTEGER, quantidade REAL, preco REAL, subTotal REAL, desconto REAL DEFAULT 0, tipo_desconto TEXT)',
            'CREATE TABLE servicos_os (idServicos_os INTEGER PRIMARY KEY AUTOINCREMENT, os_id INTEGER, servicos_id INTEGER, quantidade REAL, preco REAL, subTotal REAL, desconto REAL DEFAULT 0, tipo_desconto TEXT)',
            'CREATE TABLE anotacoes_os (idAnotacoes INTEGER PRIMARY KEY AUTOINCREMENT, anotacao TEXT, data_hora TEXT, os_id INTEGER)',
            "INSERT INTO clientes (nomeCliente, senha) VALUES ('Ana', 'x')",
            "INSERT INTO usuarios (nome) VALUES ('Técnico')",
            "INSERT INTO produtos (descricao, precoVenda, estoque) VALUES ('Tela', 100, 10)",
            "INSERT INTO servicos (nome, preco) VALUES ('Troca', 50)",
            // OS 1 e 2 abertas; OS 3 faturada.
            "INSERT INTO os (clientes_id, usuarios_id, status, faturado) VALUES (1, 1, 'Aberto', 0), (1, 1, 'Aberto', 0), (1, 1, 'Faturado', 1)",
            'INSERT INTO produtos_os (os_id, produtos_id, quantidade, preco, subTotal) VALUES (1, 1, 1, 100, 100), (2, 1, 1, 100, 100), (3, 1, 1, 100, 100)',
            'INSERT INTO servicos_os (os_id, servicos_id, quantidade, preco, subTotal) VALUES (1, 1, 1, 50, 50), (2, 1, 1, 50, 50), (3, 1, 1, 50, 50)',
            "INSERT INTO anotacoes_os (anotacao, data_hora, os_id) VALUES ('da OS 1', '2026-10-14', 1), ('da OS 2', '2026-10-14', 2), ('da OS 3', '2026-10-14', 3)",
        ] as $sql) {
            $this->db->query($sql);
        }

        $model = $this->makeInstance(Os_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);
        $permission = new class() {
            public bool $pode = true;

            public function checkPermission($permissao, $acao): bool
            {
                return $this->pode;
            }
        };

        $this->controller = new class($model, $permission, $this->db) extends OsController {
            public $os_model;

            public $permission;

            public $db;

            public $load;

            public $form_validation;

            public array $entrada = [];

            public array $configs = ['control_editos' => '0', 'control_estoque' => '0'];

            public function __construct($model, $permission, $db)
            {
                $this->os_model = $model;
                $this->permission = $permission;
                $this->db = $db;
                // Carregamento de bibliotecas e validação de formulário: não fazem parte do que se testa.
                $this->load = new class() {
                    public function __call($nome, $args)
                    {
                        return null;
                    }
                };
                $this->form_validation = new class() {
                    public function run($grupo)
                    {
                        return true;
                    }
                };
            }

            public function __destruct()
            {
                // O destruidor real grava o log da requisição no banco.
            }

            public function response($data = null, $http_code = null, $continue = false)
            {
                throw new RuntimeException(json_encode(['code' => $http_code, 'data' => $data]));
            }

            public function logged_user($reGenToken = false)
            {
                return (object) ['level' => 1, 'usuario' => (object) ['nome' => 'Teste']];
            }

            public function getConfig($config)
            {
                return $this->configs[$config] ?? null;
            }

            public function log_app($task, $nome_usuario = '')
            {
            }

            public function put($key = null, $xss_clean = null)
            {
                return $this->entrada[$key] ?? null;
            }

            public function post($key = null, $xss_clean = null)
            {
                return $this->entrada[$key] ?? null;
            }
        };
        $this->controller->permission = $permission;
    }

    protected function tearDown(): void
    {
        if ($this->chaveAnterior === null) {
            unset($_ENV['APP_ENCRYPTION_KEY']);
        }
        parent::tearDown();
    }

    /** @return array{code: int, data: array} */
    private function chamar(string $metodo, array $argumentos, array $entrada = []): array
    {
        $this->controller->entrada = $entrada;

        try {
            $this->controller->$metodo(...$argumentos);
        } catch (RuntimeException $e) {
            return json_decode($e->getMessage(), true);
        }

        $this->fail("{$metodo} deveria ter respondido.");
    }

    private function contar(string $tabela, array $where = []): int
    {
        return (int) $this->db->where($where)->count_all_results($tabela);
    }

    public function testItemDeOutraOsNaoEAlteradoNemExcluido(): void
    {
        // O item 2 de produtos_os pertence à OS 2, e a URL é da OS 1.
        $resposta = $this->chamar('produtos_put', [1, 2], ['preco' => '1', 'quantidade' => '1']);
        $this->assertSame(404, $resposta['code']);
        $this->assertEquals(100, $this->db->get_where('produtos_os', ['idProdutos_os' => 2])->row()->preco);

        $this->assertSame(404, $this->chamar('produtos_delete', [1, 2])['code']);
        $this->assertSame(1, $this->contar('produtos_os', ['idProdutos_os' => 2]));

        $this->assertSame(404, $this->chamar('servicos_put', [1, 2], ['preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertEquals(50, $this->db->get_where('servicos_os', ['idServicos_os' => 2])->row()->preco);

        $this->assertSame(404, $this->chamar('servicos_delete', [1, 2])['code']);
        $this->assertSame(1, $this->contar('servicos_os', ['idServicos_os' => 2]));

        $this->assertSame(404, $this->chamar('anotacoes_delete', [1, 2])['code']);
        $this->assertSame(1, $this->contar('anotacoes_os', ['idAnotacoes' => 2]));
    }

    public function testItemDaPropriaOsEAlteradoEExcluido(): void
    {
        $this->assertSame(200, $this->chamar('produtos_put', [1, 1], ['preco' => '120', 'quantidade' => '2'])['code']);
        $this->assertEquals(240, $this->db->get_where('produtos_os', ['idProdutos_os' => 1])->row()->subTotal);

        $this->assertSame(200, $this->chamar('servicos_put', [1, 1], ['preco' => '60', 'quantidade' => '1'])['code']);
        $this->assertSame(200, $this->chamar('anotacoes_delete', [1, 1])['code']);
        $this->assertSame(200, $this->chamar('servicos_delete', [1, 1])['code']);
        $this->assertSame(200, $this->chamar('produtos_delete', [1, 1])['code']);
        $this->assertSame(0, $this->contar('produtos_os', ['os_id' => 1]));
    }

    public function testOsFaturadaOuCanceladaNaoMudaComControleEditosDesligado(): void
    {
        $this->assertSame(403, $this->chamar('produtos_put', [3, 3], ['preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(403, $this->chamar('produtos_delete', [3, 3])['code']);
        $this->assertSame(403, $this->chamar('servicos_put', [3, 3], ['preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(403, $this->chamar('servicos_delete', [3, 3])['code']);
        $this->assertSame(403, $this->chamar('anotacoes_delete', [3, 3])['code']);
        $this->assertSame(403, $this->chamar('produtos_post', [3], ['idProduto' => '1', 'preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(403, $this->chamar('servicos_post', [3], ['idServico' => '1', 'preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(403, $this->chamar('anotacoes_post', [3], ['anotacao' => 'x'])['code']);
        $this->assertSame(403, $this->chamar('desconto_post', [3], ['tipoDesconto' => 'real', 'desconto' => '10'])['code']);

        $this->assertEquals(100, $this->db->get_where('produtos_os', ['idProdutos_os' => 3])->row()->preco);
        $this->assertSame(1, $this->contar('anotacoes_os', ['idAnotacoes' => 3]));

        // Com a configuração que libera a edição de OS fechada, o fluxo segue.
        $this->controller->configs['control_editos'] = '1';
        $this->assertSame(200, $this->chamar('produtos_put', [3, 3], ['preco' => '90', 'quantidade' => '1'])['code']);
    }

    public function testOsInexistenteDa404(): void
    {
        $this->assertSame(404, $this->chamar('index_get', [99])['code']);
        $this->assertSame(404, $this->chamar('desconto_post', [99], ['tipoDesconto' => 'real', 'desconto' => '10'])['code']);
        $this->assertSame(404, $this->chamar('produtos_post', [99], ['idProduto' => '1', 'preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(404, $this->chamar('servicos_post', [99], ['idServico' => '1', 'preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(404, $this->chamar('servicos_delete', [99, 1])['code']);
        $this->assertSame(404, $this->chamar('anotacoes_delete', [99, 1])['code']);
    }

    public function testDescontoEhCalculadoNoServidorEValidado(): void
    {
        // OS 1: produto 100 + serviço 50 = 150. O valor_desconto do app é ignorado.
        $resposta = $this->chamar('desconto_post', [1], ['tipoDesconto' => 'real', 'desconto' => '30', 'valor_desconto' => '1']);
        $this->assertSame(201, $resposta['code']);
        $os = $this->db->get_where('os', ['idOs' => 1])->row();
        $this->assertEquals(120, $os->valor_desconto);
        $this->assertEquals(30, $os->desconto);
        $this->assertSame('real', $os->tipo_desconto);

        $this->assertSame(201, $this->chamar('desconto_post', [1], ['tipoDesconto' => 'porcento', 'desconto' => '10'])['code']);
        $this->assertEquals(135, $this->db->get_where('os', ['idOs' => 1])->row()->valor_desconto);

        // Maior que o total, negativo, porcentagem acima de 100, texto e tipo inválido.
        foreach ([['real', '150'], ['real', '200'], ['real', '-10'], ['porcento', '101'], ['real', 'abc'], ['outro', '10']] as [$tipo, $valor]) {
            $this->assertSame(400, $this->chamar('desconto_post', [1], ['tipoDesconto' => $tipo, 'desconto' => $valor, 'valor_desconto' => '1'])['code'], "{$tipo} {$valor}");
        }
        $this->assertEquals(135, $this->db->get_where('os', ['idOs' => 1])->row()->valor_desconto, 'Desconto inválido não grava nada.');

        // Zero remove o desconto.
        $this->assertSame(201, $this->chamar('desconto_post', [1], ['tipoDesconto' => 'real', 'desconto' => '0'])['code']);
        $this->assertEquals(0, $this->db->get_where('os', ['idOs' => 1])->row()->valor_desconto);

        $this->assertSame(400, $this->chamar('desconto_post', [1], ['tipoDesconto' => 'real'])['code'], 'Sem o campo desconto.');
    }

    public function testValoresDoItemSaoValidados(): void
    {
        foreach ([['-1', '1'], ['10', '0'], ['10', '-2'], ['abc', '1'], ['10', 'x'], ['1e20', '1'], ['10', '9999999'], [null, '1'], ['10', null]] as [$preco, $quantidade]) {
            $this->assertSame(400, $this->chamar('produtos_post', [1], ['idProduto' => '1', 'preco' => $preco, 'quantidade' => $quantidade])['code'], "{$preco} x {$quantidade}");
            $this->assertSame(400, $this->chamar('produtos_put', [1, 1], ['preco' => $preco, 'quantidade' => $quantidade])['code'], "put {$preco} x {$quantidade}");
            $this->assertSame(400, $this->chamar('servicos_post', [1], ['idServico' => '1', 'preco' => $preco, 'quantidade' => $quantidade])['code'], "serv {$preco} x {$quantidade}");
            $this->assertSame(400, $this->chamar('servicos_put', [1, 1], ['preco' => $preco, 'quantidade' => $quantidade])['code'], "serv put {$preco} x {$quantidade}");
        }

        $this->assertSame(1, $this->contar('produtos_os', ['os_id' => 1]));
        $this->assertEquals(100, $this->db->get_where('produtos_os', ['idProdutos_os' => 1])->row()->preco);
    }

    public function testProdutoEServicoPrecisamExistirNoCadastro(): void
    {
        $this->assertSame(400, $this->chamar('produtos_post', [1], ['idProduto' => '99', 'preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(400, $this->chamar('servicos_post', [1], ['idServico' => '99', 'preco' => '1', 'quantidade' => '1'])['code']);
        $this->assertSame(1, $this->contar('produtos_os', ['os_id' => 1]));
    }

    public function testSemPermissaoEOsAbertaNaoMudaNada(): void
    {
        $this->controller->permission->pode = false;

        $this->assertSame(401, $this->chamar('desconto_post', [1], ['tipoDesconto' => 'real', 'desconto' => '10'])['code']);
        $this->assertSame(401, $this->chamar('anotacoes_delete', [1, 1])['code']);
        $this->assertSame(1, $this->contar('anotacoes_os', ['idAnotacoes' => 1]));
    }

    public function testCorsDaApiDoClienteVemDaConfiguracaoDaApi(): void
    {
        foreach (['ClientLoginController', 'ClientOsController'] as $classe) {
            $codigo = (string) file_get_contents(APPPATH . "controllers/api/v1/client/{$classe}.php");
            $this->assertStringNotContainsString('Access-Control-Allow-Origin', $codigo, "{$classe} fixava CORS *; vale API_CORS_ORIGINS (REST_Controller).");
        }
    }
}
