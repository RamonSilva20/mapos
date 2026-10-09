<?php

require_once APPPATH . 'models/Vendas_model.php';
require_once APPPATH . 'controllers/Vendas.php';

// O audit_helper (log_info) e o url_helper não são carregados nos testes.
if (! function_exists('log_info')) {
    function log_info($task)
    {
    }
}

if (! function_exists('base_url')) {
    function base_url($uri = '')
    {
        return 'http://mapos.test/' . ltrim((string) $uri, '/');
    }
}

if (! function_exists('current_url')) {
    function current_url()
    {
        return 'http://mapos.test/index.php/vendas/adicionar';
    }
}

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Formulário de venda migrado (#2843): conversão e conferência do POST, valores
 * da tela, estoque ao cancelar e a view.
 */
final class VendasFormularioTest extends MaposTestCase
{
    private function postValido(array $mais = []): array
    {
        return $mais + [
            'cliente' => 'Ana', 'clientes_id' => '3', 'vendedor' => 'Admin', 'usuarios_id' => '1',
            'status' => 'Aberto', 'dataVenda' => '2026-10-09', 'garantia' => '30',
            'observacoes' => "Linha 1\nLinha 2", 'observacoes_cliente' => 'Obrigado',
        ];
    }

    public function testDadosDeUmPostValido(): void
    {
        [$dados, $erros] = vendaDadosDoFormulario($this->postValido());

        $this->assertSame([], $erros);
        $this->assertSame(3, $dados['clientes_id']);
        $this->assertSame(1, $dados['usuarios_id']);
        $this->assertSame('Aberto', $dados['status']);
        $this->assertSame('2026-10-09', $dados['dataVenda']);
        $this->assertSame('30', $dados['garantia']);
        $this->assertSame("Linha 1<br>\nLinha 2", $dados['observacoes']);
        $this->assertSame('Obrigado', $dados['observacoes_cliente']);
        $this->assertSame(['clientes_id', 'usuarios_id', 'status', 'dataVenda', 'garantia', 'observacoes', 'observacoes_cliente'], array_keys($dados), 'Só os campos do formulário são gravados.');
    }

    public function testDataStatusIdsEGarantia(): void
    {
        [, $erros] = vendaDadosDoFormulario($this->postValido(['dataVenda' => '09/10/2026', 'status' => 'Inventado', 'clientes_id' => '', 'usuarios_id' => '0', 'garantia' => '10000']));
        $this->assertSame(['dataVenda', 'status', 'cliente', 'vendedor', 'garantia'], array_keys($erros));

        [, $erros] = vendaDadosDoFormulario($this->postValido(['dataVenda' => '2026-02-30', 'garantia' => '-1', 'clientes_id' => '3abc']));
        $this->assertSame(['dataVenda', 'cliente', 'garantia'], array_keys($erros), 'Data impossível, garantia negativa e id com lixo.');

        [$dados, $erros] = vendaDadosDoFormulario($this->postValido(['garantia' => '']));
        $this->assertSame([], $erros);
        $this->assertSame('', $dados['garantia'], 'Garantia em branco continua em branco.');

        [$dados] = vendaDadosDoFormulario($this->postValido(['garantia' => '007']));
        $this->assertSame('7', $dados['garantia']);
    }

    public function testTodosOsStatusDaV4SaoAceitos(): void
    {
        foreach (array_keys(OS_STATUS_VARIANTES) as $status) {
            [, $erros] = vendaDadosDoFormulario($this->postValido(['status' => $status]));
            $this->assertSame([], $erros, $status);
        }
    }

    public function testArrayNoPostNaoQuebra(): void
    {
        [$dados, $erros] = vendaDadosDoFormulario($this->postValido(['observacoes' => ['x'], 'status' => ['Aberto'], 'dataVenda' => ['2026-10-09']]));

        $this->assertSame('', $dados['observacoes']);
        $this->assertSame(['dataVenda', 'status'], array_keys($erros));
    }

    public function testTextoGravadoEscapaTags(): void
    {
        [$dados] = vendaDadosDoFormulario($this->postValido(['observacoes' => '<script>alert(1)</script> & "ok"']));

        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt; &amp; "ok"', $dados['observacoes']);
    }

    public function testValoresDaTela(): void
    {
        $venda = (object) [
            'nomeCliente' => 'Ana', 'clientes_id' => 3, 'nome' => 'Admin', 'usuarios_id' => 1, 'status' => 'Aberto',
            'dataVenda' => '2026-10-09', 'garantia' => '30', 'observacoes' => "Linha 1<br>\nLinha 2", 'observacoes_cliente' => '<p>Oi <b>você</b></p>',
        ];

        $valores = vendaValoresDoFormulario(null, $venda);
        $this->assertSame('Ana', $valores['cliente']);
        $this->assertSame('3', $valores['clientes_id']);
        $this->assertSame('Admin', $valores['vendedor']);
        $this->assertSame('2026-10-09', $valores['dataVenda']);
        $this->assertSame("Linha 1\nLinha 2", $valores['observacoes']);
        $this->assertSame('Oi você', $valores['observacoes_cliente'], 'O HTML do editor antigo vira texto.');

        // Com erro, a tela volta com o que foi enviado, não com o banco.
        $reenvio = vendaValoresDoFormulario(['cliente' => 'Bia', 'clientes_id' => '', 'observacoes' => ['x']], $venda);
        $this->assertSame('Bia', $reenvio['cliente']);
        $this->assertSame('', $reenvio['clientes_id']);
        $this->assertSame('', $reenvio['observacoes']);
        $this->assertSame('', $reenvio['status']);

        $nova = vendaValoresDoFormulario(null, null, ['status' => 'Orçamento', 'vendedor' => 'Eu', 'usuarios_id' => '7']);
        $this->assertSame('Orçamento', $nova['status']);
        $this->assertSame('Eu', $nova['vendedor']);
        $this->assertSame('', $nova['cliente']);
    }

    private function controllerComFakes(string $statusAtual, array $produtos, bool $controleEstoque = true): array
    {
        $registro = (object) ['estoque' => [], 'edit' => null];

        $vendasModel = new class($produtos, $registro) {
            public function __construct(private array $produtos, private object $registro)
            {
            }

            public function getProdutos($id)
            {
                return $this->produtos;
            }

            public function edit($tabela, $dados, $chave, $id)
            {
                $this->registro->edit = [$tabela, $dados, $chave, $id];

                return true;
            }
        };
        $produtosModel = new class($registro) {
            public function __construct(private object $registro)
            {
            }

            public function updateEstoque($produto, $quantidade, $operacao)
            {
                $this->registro->estoque[] = [$produto, $quantidade, $operacao];
            }
        };

        // Subclasse com as propriedades declaradas: o CI as cria dinamicamente,
        // o que é deprecado no PHP 8.2+ fora do framework.
        $controller = new class() extends Vendas {
            public $vendas_model;

            public $produtos_model;

            public $load;

            public function __construct()
            {
            }
        };
        $controller->vendas_model = $vendasModel;
        $controller->produtos_model = $produtosModel;
        $controller->load = new class() {
            public function model($nome)
            {
            }
        };
        $controller->data = ['configuration' => ['control_estoque' => $controleEstoque ? '1' : '0']];

        return [$controller, (object) ['idVendas' => 12, 'status' => $statusAtual], $registro];
    }

    public function testCancelarDevolveEstoqueEReabrirDebita(): void
    {
        $produtos = [(object) ['produtos_id' => 5, 'quantidade' => 2]];

        [$controller, $venda, $registro] = $this->controllerComFakes('Aberto', $produtos);
        $this->assertSame(12, $this->invokeMethod($controller, 'salvarVenda', [$venda, ['status' => 'Cancelado']]));
        $this->assertSame([[5, 2, '+']], $registro->estoque);
        $this->assertSame(['vendas', ['status' => 'Cancelado'], 'idVendas', 12], $registro->edit);

        [$controller, $venda, $registro] = $this->controllerComFakes('Cancelado', $produtos);
        $this->invokeMethod($controller, 'salvarVenda', [$venda, ['status' => 'Aberto']]);
        $this->assertSame([[5, 2, '-']], $registro->estoque);

        [$controller, $venda, $registro] = $this->controllerComFakes('Aberto', $produtos);
        $this->invokeMethod($controller, 'salvarVenda', [$venda, ['status' => 'Finalizado']]);
        $this->assertSame([], $registro->estoque, 'Sem cancelamento, o estoque não muda.');

        [$controller, $venda, $registro] = $this->controllerComFakes('Cancelado', $produtos);
        $this->invokeMethod($controller, 'salvarVenda', [$venda, ['status' => 'Cancelado']]);
        $this->assertSame([], $registro->estoque, 'Salvar de novo uma venda cancelada não devolve duas vezes.');
    }

    public function testSemControleDeEstoqueCancelarNaoMexeNoEstoque(): void
    {
        [$controller, $venda, $registro] = $this->controllerComFakes('Aberto', [(object) ['produtos_id' => 5, 'quantidade' => 2]], false);

        $this->invokeMethod($controller, 'salvarVenda', [$venda, ['status' => 'Cancelado']]);

        $this->assertSame([], $registro->estoque);
        $this->assertNotNull($registro->edit, 'A venda é gravada do mesmo jeito.');
    }

    private function modelComBanco(): Vendas_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, situacao INTEGER)');
        $this->db->query("INSERT INTO clientes (nomeCliente) VALUES ('Ana Souza')");
        $this->db->query("INSERT INTO usuarios (nome, situacao) VALUES ('Vendedor Ativo', 1), ('Vendedor Inativo', 0)");

        $model = $this->makeInstance(Vendas_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    public function testExisteConfereIdsEUsuarioAtivo(): void
    {
        $model = $this->modelComBanco();

        $this->assertTrue($model->existe('clientes', 'idClientes', 1));
        $this->assertFalse($model->existe('clientes', 'idClientes', 99));
        $this->assertTrue($model->existe('usuarios', 'idUsuarios', 1, true));
        $this->assertFalse($model->existe('usuarios', 'idUsuarios', 2, true), 'Vendedor inativo não pode ser escolhido.');
    }

    /**
     * O controller usa o id da URL (nunca o idVendas do POST), confere a edição
     * pelo id da URL também no GET e segue o padrão dos formulários.
     */
    public function testControllerUsaOIdDaUrlEOPadraoDeFormulario(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Vendas.php');
        preg_match('/public function editar\(\).*?\n    }\n/s', $codigo, $editar);
        preg_match('/private function formulario\(.*?\n    }\n/s', $codigo, $formulario);

        $this->assertNotEmpty($editar);
        $this->assertNotEmpty($formulario);
        $this->assertStringContainsString('isEditable((int) $venda->idVendas)', $editar[0]);
        $this->assertStringContainsString("\$this->validarFormulario('vendas')", $formulario[0]);
        $this->assertStringContainsString('vendaDadosDoFormulario($this->input->post())', $formulario[0]);
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $formulario[0]);
        $this->assertStringNotContainsString("post('idVendas')", $editar[0] . $formulario[0]);
    }

    public function testRegrasDoGrupoVendasEmPortugues(): void
    {
        // O arquivo usa get_instance() em outros grupos; lê o grupo "vendas" do código.
        $codigo = (string) file_get_contents(APPPATH . 'config/form_validation.php');
        preg_match("/    'vendas' => \\[.*?\\n    \\],\\n/s", $codigo, $grupo);

        $this->assertNotEmpty($grupo);
        $this->assertMatchesRegularExpression("/'field' => 'clientes_id',\\s*'label' => 'Cliente',/", $grupo[0]);
        $this->assertMatchesRegularExpression("/'field' => 'usuarios_id',\\s*'label' => 'Vendedor',/", $grupo[0]);
        $this->assertMatchesRegularExpression("/'field' => 'dataVenda',\\s*'label' => 'Data da venda',/", $grupo[0]);
        $this->assertStringNotContainsString('situacao', $grupo[0], 'Nenhuma tela envia "situacao": o grupo exigia um campo que não existe.');
    }

    // --- View ----------------------------------------------------------

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

            public function view(string $nome, array $mais = [], bool $retornar = false)
            {
                $this->variaveis = array_merge($this->variaveis, $mais);
                $arquivo = APPPATH . 'views' . DIRECTORY_SEPARATOR . $nome . '.php';
                $html = (function () use ($arquivo) {
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

                if ($retornar) {
                    return $html;
                }

                echo $html;
            }
        };

        return $carregador->view($view, [], true);
    }

    public function testFormularioRenderizaEscapandoEComOsCamposDoServidor(): void
    {
        $xss = '<script>alert(1)</script>';
        $venda = (object) ['idVendas' => 7, 'status' => 'Aberto', 'nomeCliente' => 'Ana ' . $xss];
        $valores = vendaValoresDoFormulario(['cliente' => 'Ana ' . $xss, 'clientes_id' => '3', 'vendedor' => 'Admin', 'usuarios_id' => '1', 'status' => 'Aberto', 'dataVenda' => '2026-10-09', 'garantia' => '30', 'observacoes' => $xss, 'observacoes_cliente' => ''], null);

        $html = $this->renderizar('vendas/formulario', [
            'venda' => $venda,
            'valores' => $valores,
            'erros' => ['dataVenda' => 'Informe uma data válida.'],
            'formatados' => ['observacoes'],
            'pode' => ['cadastrar_cliente' => true, 'ver_venda' => true],
        ]);

        $this->assertStringContainsString('Editar venda #7', $html);
        $this->assertStringNotContainsString($xss, $html);
        $this->assertStringContainsString('Ana &lt;script&gt;', $html);
        $this->assertStringContainsString('Informe uma data válida.', $html);
        $this->assertStringContainsString('data-module="vendas/formulario"', $html);
        $this->assertStringContainsString('data-module="formulario/padrao"', $html);
        $this->assertStringContainsString('os/autoCompleteCliente', $html);
        $this->assertStringContainsString('os/autoCompleteUsuario', $html);
        $this->assertStringContainsString('name="clientes_id"', $html);
        $this->assertStringContainsString('name="usuarios_id"', $html);
        $this->assertStringContainsString('Cadastrar novo cliente', $html);
        $this->assertStringContainsString('A formatação do editor antigo', $html);
        $this->assertStringContainsString('hash-csrf', $html);
        $this->assertStringContainsString('vendas/visualizar/7', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('bx-', $html);

        $nova = $this->renderizar('vendas/formulario', [
            'venda' => null,
            'valores' => vendaValoresDoFormulario(null, null, ['status' => 'Orçamento', 'dataVenda' => '2026-10-09']),
            'erros' => ['_geral' => 'Não foi possível salvar. Tente de novo.'],
            'formatados' => [],
            'pode' => ['cadastrar_cliente' => false, 'ver_venda' => false],
        ]);
        $this->assertStringContainsString('Nova venda', $nova);
        $this->assertStringContainsString('Criar venda', $nova);
        $this->assertStringContainsString('Não foi possível salvar', $nova);
        $this->assertStringNotContainsString('Cadastrar novo cliente', $nova);
    }
}
