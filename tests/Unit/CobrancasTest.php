<?php

require_once APPPATH . 'models/Cobrancas_model.php';
require_once APPPATH . 'controllers/Cobrancas.php';

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
 * Cobranças (#2844): status dos gateways como pill, links só http(s), filtros
 * da listagem, ações por POST que voltam para uma tela do Map-OS e as views.
 */
final class CobrancasTest extends MaposTestCase
{
    // --- Helpers -------------------------------------------------------------

    public function testStatusDosTresGatewaysViramPalavraEVariante(): void
    {
        $this->assertSame(['label' => 'Paga', 'variant' => 'success'], cobrancaStatusPill('paid'));
        $this->assertSame(['label' => 'Paga', 'variant' => 'success'], cobrancaStatusPill('RECEIVED'));
        $this->assertSame(['label' => 'Paga', 'variant' => 'success'], cobrancaStatusPill('approved'));
        $this->assertSame(['label' => 'Aguardando', 'variant' => 'warning'], cobrancaStatusPill('waiting'));
        $this->assertSame(['label' => 'Aguardando', 'variant' => 'warning'], cobrancaStatusPill('PENDING'));
        $this->assertSame(['label' => 'Gerada', 'variant' => 'info'], cobrancaStatusPill('new'));
        $this->assertSame(['label' => 'Vencida', 'variant' => 'danger'], cobrancaStatusPill('OVERDUE'));
        $this->assertSame(['label' => 'Cancelada', 'variant' => 'neutral'], cobrancaStatusPill('cancelled'));
        $this->assertSame(['label' => 'Cancelada', 'variant' => 'neutral'], cobrancaStatusPill('DELETED'));
        $this->assertSame(['label' => 'Estornada', 'variant' => 'neutral'], cobrancaStatusPill('refunded'));
        $this->assertSame(['label' => 'Em disputa', 'variant' => 'danger'], cobrancaStatusPill('charged_back'));
        $this->assertSame(['label' => 'novo_status', 'variant' => 'neutral'], cobrancaStatusPill('novo_status'));
        $this->assertSame(['label' => 'Sem status', 'variant' => 'neutral'], cobrancaStatusPill(null));
    }

    public function testTodoStatusDoConfigDosGatewaysTemPalavra(): void
    {
        $config = [];
        include APPPATH . 'config/payment_gateways.php';

        foreach ($config['payment_gateways'] as $gateway) {
            foreach (array_keys($gateway['transaction_status']) as $status) {
                $this->assertNotSame((string) $status, cobrancaStatusPill((string) $status)['label'], "Sem palavra para {$status}");
            }
        }
    }

    public function testDescricaoDoStatusNaoQuebraComStatusDesconhecido(): void
    {
        $gateways = ['Asaas' => ['transaction_status' => ['PENDING' => 'Aguardando pagamento']]];

        $this->assertSame('Aguardando pagamento', cobrancaDescricaoDoStatus($gateways, 'Asaas', 'PENDING'));
        $this->assertSame('X', cobrancaDescricaoDoStatus($gateways, 'Asaas', 'X'));
        $this->assertSame('Y', cobrancaDescricaoDoStatus($gateways, 'Outro', 'Y'));
        $this->assertSame('', cobrancaDescricaoDoStatus(null, null, null));
    }

    public function testSoUrlHttpViraLink(): void
    {
        $this->assertSame('https://pay.example.com/b/1?x=2', cobrancaUrlSegura(' https://pay.example.com/b/1?x=2 '));
        $this->assertSame('http://pay.example.com', cobrancaUrlSegura('http://pay.example.com'));
        $this->assertNull(cobrancaUrlSegura('javascript:alert(1)'));
        $this->assertNull(cobrancaUrlSegura('data:text/html,<script>1</script>'));
        $this->assertNull(cobrancaUrlSegura('//evil.example.com'));
        $this->assertNull(cobrancaUrlSegura('https://a.com/x y'));
        $this->assertNull(cobrancaUrlSegura(''));
        $this->assertNull(cobrancaUrlSegura(null));
    }

    public function testGatewaySoValeSeConfigurado(): void
    {
        $config = ['Asaas' => ['library_name' => 'Asaas'], 'MercadoPago' => ['library_name' => 'MercadoPago']];

        $this->assertTrue(cobrancaGatewayValido('Asaas', $config));
        $this->assertFalse(cobrancaGatewayValido('Inexistente', $config));
        $this->assertFalse(cobrancaGatewayValido('../Asaas', $config));
        $this->assertFalse(cobrancaGatewayValido('Asaas ', $config));
        $this->assertFalse(cobrancaGatewayValido('', $config));
        $this->assertFalse(cobrancaGatewayValido(null, $config));
        $this->assertFalse(cobrancaGatewayValido('Asaas', null));
    }

    public function testDetalheSemClienteAvisa(): void
    {
        $html = $this->renderizar('cobrancas/visualizarCobranca', [
            'result' => $this->cobranca(['clientes_id' => null, 'nomeCliente' => null, 'documento' => null, 'telefone' => null, 'celular' => null, 'email' => null]),
            'gateways' => $this->gateways(),
            'pode' => ['editar' => true, 'excluir' => true, 'ver_os' => true, 'ver_venda' => true, 'ver_cliente' => true],
        ]);

        $this->assertStringContainsString('Sem cliente vinculado a esta cobrança.', $html);
    }

    public function testEmailSoComPermissaoDeEditar(): void
    {
        $dados = [
            'results' => [$this->cobranca()], 'filtros' => [], 'total' => 1, 'paginacao' => ['total_pages' => 1, 'current' => 1, 'url' => 'x/{offset}', 'per_page' => 10],
            'status_opcoes' => [], 'gateways' => $this->gateways(),
        ];

        $this->assertStringContainsString('cobrancas/enviarEmail/3', $this->renderizar('cobrancas/cobrancas', $dados + ['pode' => ['editar' => true, 'excluir' => false]]));
        $this->assertStringNotContainsString('cobrancas/enviarEmail/3', $this->renderizar('cobrancas/cobrancas', $dados + ['pode' => ['editar' => false, 'excluir' => false]]));
    }

    // --- Model -----------------------------------------------------------------

    private function model(): Cobrancas_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT)');
        $this->db->query('CREATE TABLE cobrancas (idCobranca INTEGER PRIMARY KEY AUTOINCREMENT, charge_id INTEGER, status TEXT, os_id INTEGER, vendas_id INTEGER, clientes_id INTEGER)');
        $this->db->query("INSERT INTO clientes (nomeCliente) VALUES ('Ana Souza'), ('Bruno 100% Lima')");
        $this->db->query("INSERT INTO cobrancas (charge_id, status, os_id, vendas_id, clientes_id) VALUES (555, 'waiting', 9, NULL, 1), (777, 'paid', NULL, 4, 2), (888, 'waiting', NULL, 5, NULL)");

        $model = $this->makeInstance(Cobrancas_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    private function ids(array $linhas): array
    {
        return array_map('intval', array_column($linhas, 'idCobranca'));
    }

    public function testListagemFiltraPorStatusTipoEBusca(): void
    {
        $model = $this->model();

        $this->assertSame([3, 2, 1], $this->ids($model->listar([], 10, 0)), 'Mais recentes primeiro, inclusive sem cliente.');
        $this->assertSame([3, 1], $this->ids($model->listar(['status' => 'waiting'], 10, 0)));
        $this->assertSame([1], $this->ids($model->listar(['tipo' => 'os'], 10, 0)));
        $this->assertSame([3, 2], $this->ids($model->listar(['tipo' => 'venda'], 10, 0)));
        $this->assertSame([1], $this->ids($model->listar(['pesquisa' => 'ana'], 10, 0)));
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '100%'], 10, 0)));
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '777'], 10, 0)), 'Número procura o id no gateway.');
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '2'], 10, 0)), 'E o Nº da cobrança.');
        $this->assertSame([], $this->ids($model->listar(['pesquisa' => "' OR '1'='1"], 10, 0)));
        $this->assertSame([], $this->ids($model->listar(['pesquisa' => '2', 'tipo' => 'os'], 10, 0)), 'O OR da busca não anula o tipo.');
    }

    public function testContagemEPaginacaoUsamOsMesmosFiltros(): void
    {
        $model = $this->model();

        $this->assertSame(3, $model->contar([]));
        $this->assertSame(2, $model->contar(['status' => 'waiting']));
        $this->assertSame(1, $model->contar(['pesquisa' => 'ana', 'status' => 'waiting']));
        $this->assertSame([2], $this->ids($model->listar([], 1, 1)));
    }

    // --- Controller --------------------------------------------------------------

    private function controller(array $post = [], string $metodo = 'post', array $get = []): Cobrancas
    {
        $controller = new class() extends Cobrancas {
            public $input;

            public $session;

            public $cobrancas_model;

            public $config;

            public $load;

            public function __construct()
            {
            }

            protected function statusDosGateways(): array
            {
                return ['waiting' => 'Aguardando (waiting)', 'paid' => 'Paga (paid)'];
            }
        };
        $controller->input = new class($post, $metodo, $get) {
            public function __construct(private array $post, private string $metodo, private array $get)
            {
            }

            public function post($chave = null)
            {
                return $this->post[$chave] ?? null;
            }

            public function get()
            {
                return $this->get;
            }

            public function method()
            {
                return $this->metodo;
            }
        };

        return $controller;
    }

    public function testFiltrosDaListagem(): void
    {
        $controller = $this->controller([], 'get', ['pesquisa' => ' ana ', 'status' => 'paid', 'tipo' => 'venda', 'extra' => 'x']);
        $this->assertSame(['pesquisa' => 'ana', 'status' => 'paid', 'tipo' => 'venda'], $this->invokeMethod($controller, 'filtrosDaListagem'));

        $controller = $this->controller([], 'get', ['status' => 'inventado', 'tipo' => 'outro']);
        $this->assertSame([], $this->invokeMethod($controller, 'filtrosDaListagem'));
    }

    public function testAcaoVoltaParaUmaTelaDoMapOsNuncaParaOReferer(): void
    {
        $_SERVER['HTTP_REFERER'] = 'https://evil.example.com/';

        $lista = $this->controller([], 'post', ['status' => 'paid']);
        $this->assertSame('http://mapos.test/index.php/cobrancas/cobrancas?status=paid', $this->invokeMethod($lista, 'destinoDaAcao', [3]));

        $detalhe = $this->controller(['voltar' => 'detalhe']);
        $this->assertSame('http://mapos.test/index.php/cobrancas/visualizar/3', $this->invokeMethod($detalhe, 'destinoDaAcao', [3]));

        unset($_SERVER['HTTP_REFERER']);
    }

    public function testIdDoPostSoAceitaNumero(): void
    {
        $this->assertSame(12, $this->invokeMethod($this->controller(['id' => '12']), 'idDoPost'));
        $this->assertSame(0, $this->invokeMethod($this->controller(['id' => '12; DROP']), 'idDoPost'));
        $this->assertSame(0, $this->invokeMethod($this->controller(['id' => ['1']]), 'idDoPost'));
        $this->assertSame(0, $this->invokeMethod($this->controller([]), 'idDoPost'));
    }

    public function testControllerSegueOPadraoDaV5(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Cobrancas.php');

        $this->assertStringNotContainsString('HTTP_REFERER', $codigo);
        $this->assertStringContainsString("\$this->input->method() !== 'post'", $codigo, 'Ações que mudam dados só por POST.');
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $codigo);
        // A resposta JSON de adicionar() é usada pelas telas de OS e de venda.
        $this->assertStringContainsString("json_encode(\$cobranca)", $codigo);
    }

    // --- Views --------------------------------------------------------------------

    private function renderizar(string $view, array $variaveis): string
    {
        $carregador = new class($variaveis) {
            public object $load;

            public object $security;

            public function __construct(private array $variaveis)
            {
                $this->load = $this;
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

            public function view(string $nome)
            {
                $arquivo = APPPATH . 'views' . DIRECTORY_SEPARATOR . $nome . '.php';

                return (function () use ($arquivo) {
                    extract($this->variaveis);
                    ob_start();

                    try {
                        include $arquivo;
                    } catch (Throwable $erro) {
                        ob_end_clean();

                        throw $erro;
                    }

                    return (string) ob_get_clean();
                })();
            }
        };

        return $carregador->view($view);
    }

    private function cobranca(array $mudancas = []): object
    {
        $xss = '<script>alert(1)</script>';

        return (object) ($mudancas + [
            'idCobranca' => 3, 'charge_id' => 910001, 'status' => 'waiting', 'payment_gateway' => 'Asaas', 'payment_method' => 'boleto',
            'total' => '15090.00', 'expire_at' => '2026-10-20', 'barcode' => '3419 1', 'link' => 'https://pay.example.com/b/1', 'pdf' => 'javascript:alert(1)',
            'payment_url' => '', 'message' => 'Msg ' . $xss, 'os_id' => 9, 'vendas_id' => null, 'clientes_id' => 5, 'nomeCliente' => 'Ana ' . $xss,
            'documento' => '111', 'telefone' => '', 'celular' => '1199', 'email' => 'a@example.com',
        ]);
    }

    private function gateways(): array
    {
        return ['Asaas' => ['transaction_status' => ['waiting' => 'Aguardando a confirmação']]];
    }

    public function testListagemRenderizaEscapandoEComAcoesPorPost(): void
    {
        $html = $this->renderizar('cobrancas/cobrancas', [
            'results' => [$this->cobranca()], 'filtros' => ['status' => 'waiting'], 'total' => 1,
            'paginacao' => ['total_pages' => 1, 'current' => 1, 'url' => 'x/{offset}', 'per_page' => 10],
            'status_opcoes' => ['waiting' => 'Aguardando (waiting)'], 'gateways' => $this->gateways(),
            'pode' => ['editar' => true, 'excluir' => true],
        ]);

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringContainsString('Aguardando', $html);
        $this->assertStringContainsString('R$ 150,90', $html, 'O total do gateway vem em centavos.');
        $this->assertStringContainsString('OS #9', $html);
        $this->assertStringContainsString('formaction="http://mapos.test/index.php/cobrancas/atualizar/3?status=waiting"', $html);
        $this->assertStringContainsString('formaction="http://mapos.test/index.php/cobrancas/enviarEmail/3?status=waiting"', $html);
        $this->assertStringContainsString('data-modal-abrir="cancelar-cobranca"', $html);
        $this->assertStringContainsString('data-modal-abrir="excluir-cobranca"', $html);
        $this->assertStringContainsString('name="MAPOS_TOKEN" value="hash-csrf"', $html);
        $this->assertStringNotContainsString('href="http://mapos.test/index.php/cobrancas/atualizar', $html, 'Atualizar não é mais um link GET.');
        $this->assertStringNotContainsString('bx-', $html);

        $sem = $this->renderizar('cobrancas/cobrancas', [
            'results' => [$this->cobranca()], 'filtros' => [], 'total' => 1,
            'paginacao' => ['total_pages' => 1, 'current' => 1, 'url' => 'x/{offset}', 'per_page' => 10],
            'status_opcoes' => [], 'gateways' => $this->gateways(), 'pode' => ['editar' => false, 'excluir' => false],
        ]);
        $this->assertStringNotContainsString('cancelar-cobranca', $sem);
        $this->assertStringNotContainsString('excluir-cobranca', $sem);
        $this->assertStringNotContainsString('confirmar-pagamento', $sem);
    }

    public function testListagemVazia(): void
    {
        $dados = [
            'results' => [], 'total' => 0, 'paginacao' => ['total_pages' => 1, 'current' => 1, 'url' => 'x/{offset}', 'per_page' => 10],
            'status_opcoes' => [], 'gateways' => [], 'pode' => ['editar' => true, 'excluir' => true],
        ];

        $this->assertStringContainsString('Nenhuma cobrança gerada', $this->renderizar('cobrancas/cobrancas', ['filtros' => []] + $dados));
        $filtrada = $this->renderizar('cobrancas/cobrancas', ['filtros' => ['pesquisa' => 'x']] + $dados);
        $this->assertStringContainsString('Nenhuma cobrança encontrada', $filtrada);
        $this->assertStringContainsString('Limpar filtros', $filtrada);
    }

    public function testDetalheEscapaEOmiteLinksQueNaoSaoHttp(): void
    {
        $html = $this->renderizar('cobrancas/visualizarCobranca', [
            'result' => $this->cobranca(), 'gateways' => $this->gateways(),
            'pode' => ['editar' => true, 'excluir' => true, 'ver_os' => true, 'ver_venda' => true, 'ver_cliente' => true],
        ]);

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringContainsString('Ana &lt;script&gt;', $html);
        $this->assertStringContainsString('href="https://pay.example.com/b/1"', $html);
        $this->assertStringNotContainsString('javascript:', $html, 'PDF que não é http(s) não vira link.');
        $this->assertStringContainsString('os/visualizar/9', $html);
        $this->assertStringContainsString('name="voltar" value="detalhe"', $html);
        $this->assertStringContainsString('Aguardando a confirmação', $html);

        $sem = $this->renderizar('cobrancas/visualizarCobranca', [
            'result' => $this->cobranca(), 'gateways' => $this->gateways(),
            'pode' => ['editar' => false, 'excluir' => false, 'ver_os' => false, 'ver_venda' => false, 'ver_cliente' => false],
        ]);
        $this->assertStringNotContainsString('os/visualizar/9', $sem);
        $this->assertStringNotContainsString('cancelar-cobranca', $sem);
        $this->assertStringNotContainsString('excluir-cobranca', $sem);
        $this->assertStringNotContainsString('clientes/visualizar', $sem);
    }
}
