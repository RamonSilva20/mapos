<?php

require_once APPPATH . 'models/Os_model.php';
require_once APPPATH . 'controllers/Os.php';

// O audit_helper (log_info) não é carregado nos testes; o controller o chama
// ao mexer no estoque.
if (! function_exists('log_info')) {
    function log_info($task)
    {
    }
}

/**
 * Formulário de OS (#2842): conversão e conferência do POST, valores da tela,
 * textos do editor antigo, estoque ao cancelar e o fluxo do controller.
 */
final class OsFormularioTest extends MaposTestCase
{
    private const POST = [
        'cliente' => 'Ana Souza',
        'clientes_id' => '3',
        'tecnico' => 'Técnico',
        'usuarios_id' => '1',
        'status' => 'Aberto',
        'dataInicial' => '2026-10-08',
        'dataFinal' => '2026-10-15',
        'garantia' => '90',
        'termoGarantia' => '',
        'garantias_id' => '',
        'descricaoProduto' => "Notebook Dell\nCarregador",
        'defeito' => 'Não liga',
        'observacoes' => '',
        'laudoTecnico' => '',
        'idOs' => '999',
        'faturado' => '1',
        'valorTotal' => '1000',
    ];

    public function testDadosDeUmPostValido(): void
    {
        [$dados, $erros] = osDadosDoFormulario(self::POST);

        $this->assertSame([], $erros);
        $this->assertSame(3, $dados['clientes_id']);
        $this->assertSame(1, $dados['usuarios_id']);
        $this->assertSame('2026-10-08', $dados['dataInicial']);
        $this->assertSame('2026-10-15', $dados['dataFinal']);
        $this->assertSame('90', $dados['garantia']);
        $this->assertNull($dados['garantias_id']);
        $this->assertSame("Notebook Dell<br>\nCarregador", $dados['descricaoProduto']);
        // Só os campos do formulário: nada de id, faturado ou valor vindo do POST.
        $this->assertArrayNotHasKey('idOs', $dados);
        $this->assertArrayNotHasKey('faturado', $dados);
        $this->assertArrayNotHasKey('valorTotal', $dados);
    }

    public function testDatasInvalidasEFinalAntesDaInicial(): void
    {
        [, $erros] = osDadosDoFormulario(['dataInicial' => '08/10/2026', 'dataFinal' => '2026-02-31'] + self::POST);
        $this->assertArrayHasKey('dataInicial', $erros);
        $this->assertArrayHasKey('dataFinal', $erros);

        [, $erros] = osDadosDoFormulario(['dataInicial' => '2026-10-08', 'dataFinal' => '2026-10-01'] + self::POST);
        $this->assertSame('A data final não pode ser anterior à data inicial.', $erros['dataFinal']);

        [$dados, $erros] = osDadosDoFormulario(['dataFinal' => '2026-10-08'] + self::POST);
        $this->assertSame([], $erros, 'Mesmo dia é permitido.');
        $this->assertSame('2026-10-08', $dados['dataFinal']);
    }

    public function testIdsDosAutocompletesStatusEGarantia(): void
    {
        [, $erros] = osDadosDoFormulario(['clientes_id' => '', 'usuarios_id' => 'abc', 'status' => 'Inventado', 'garantia' => '-1'] + self::POST);

        $this->assertSame('Escolha um cliente da lista.', $erros['cliente']);
        $this->assertSame('Escolha um técnico da lista.', $erros['tecnico']);
        $this->assertSame('Escolha um status da lista.', $erros['status']);
        $this->assertArrayHasKey('garantia', $erros);

        // Termo digitado sem escolher da lista.
        [, $erros] = osDadosDoFormulario(['termoGarantia' => 'Garantia 90', 'garantias_id' => ''] + self::POST);
        $this->assertArrayHasKey('termoGarantia', $erros);

        [$dados, $erros] = osDadosDoFormulario(['termoGarantia' => 'Garantia 90', 'garantias_id' => '4', 'garantia' => ''] + self::POST);
        $this->assertSame([], $erros);
        $this->assertSame(4, $dados['garantias_id']);
        $this->assertSame('', $dados['garantia']);
    }

    public function testArrayNoPostNaoQuebra(): void
    {
        [$dados, $erros] = osDadosDoFormulario(['clientes_id' => ['3'], 'defeito' => ['x']] + self::POST);

        $this->assertArrayHasKey('cliente', $erros);
        $this->assertSame('', $dados['defeito']);
    }

    /**
     * O banco continua guardando HTML (texto escapado com <br>): as telas que
     * exibem com printSafeHtml() mantêm as linhas, e uma tag digitada não vira
     * HTML.
     */
    public function testTextoGravadoEscapaEPreservaLinhas(): void
    {
        $this->assertSame('', osTextoParaGravar("  \n "));
        $this->assertSame("Tela &lt;b&gt;quebrada&lt;/b&gt; &amp; \"riscada\"<br>\n<br>\nok", osTextoParaGravar("Tela <b>quebrada</b> & \"riscada\"\r\n\r\nok"));
    }

    public function testTextoParaEdicaoDoEditorAntigoEIdaEVolta(): void
    {
        $antigo = '<p>Notebook <b>Dell</b> &amp; fonte</p><ul><li>Sem bateria</li><li>Tela riscada</li></ul>';
        $this->assertSame("Notebook Dell & fonte\nSem bateria\nTela riscada", osTextoParaEdicao($antigo));
        $this->assertTrue(osTemFormatacao($antigo));

        $digitado = "Linha 1\n\nLinha <3 & \"aspas\"";
        $gravado = osTextoParaGravar($digitado);
        $this->assertFalse(osTemFormatacao($gravado), 'Só <br> não é formatação.');
        $this->assertSame($digitado, osTextoParaEdicao($gravado));

        $this->assertSame('', osTextoParaEdicao(null));
        $this->assertFalse(osTemFormatacao(null));
    }

    public function testDataDoBancoParaOCampo(): void
    {
        $this->assertSame('2026-10-08', osDataParaCampo('2026-10-08'));
        $this->assertSame('2026-10-08', osDataParaCampo('2026-10-08 10:00:00'));
        $this->assertSame('', osDataParaCampo('0000-00-00'));
        $this->assertSame('', osDataParaCampo(null));
    }

    public function testValoresDaTela(): void
    {
        $os = (object) [
            'nomeCliente' => 'Ana', 'clientes_id' => 3, 'nome' => 'Técnico', 'usuarios_id' => 1, 'status' => 'Aprovado',
            'dataInicial' => '2026-10-01', 'dataFinal' => '0000-00-00', 'garantia' => '30', 'refGarantia' => 'Termo A', 'garantias_id' => 2,
            'descricaoProduto' => '<p>Celular</p>', 'defeito' => null, 'observacoes' => '', 'laudoTecnico' => 'Ok<br>\nTrocado',
        ];

        $edicao = osValoresDoFormulario(null, $os);
        $this->assertSame('Ana', $edicao['cliente']);
        $this->assertSame('3', $edicao['clientes_id']);
        $this->assertSame('Técnico', $edicao['tecnico']);
        $this->assertSame('Termo A', $edicao['termoGarantia']);
        $this->assertSame('2026-10-01', $edicao['dataInicial']);
        $this->assertSame('', $edicao['dataFinal']);
        $this->assertSame('Celular', $edicao['descricaoProduto']);
        $this->assertSame('', $edicao['defeito']);

        // Com erro, a tela volta com o que foi enviado, não com o banco.
        $reenvio = osValoresDoFormulario(['cliente' => 'Bia', 'clientes_id' => '', 'descricaoProduto' => ['x']], $os);
        $this->assertSame('Bia', $reenvio['cliente']);
        $this->assertSame('', $reenvio['clientes_id']);
        $this->assertSame('', $reenvio['descricaoProduto']);
        $this->assertSame('', $reenvio['status']);

        $nova = osValoresDoFormulario(null, null, ['status' => 'Aberto', 'tecnico' => 'Eu', 'usuarios_id' => '7']);
        $this->assertSame('Aberto', $nova['status']);
        $this->assertSame('Eu', $nova['tecnico']);
        $this->assertSame('', $nova['cliente']);
    }

    private function controllerComFakes(string $statusAtual, array $produtos): array
    {
        $registro = (object) ['estoque' => [], 'edit' => null, 'historico' => []];

        $osModel = new class($produtos, $registro) {
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

            public function registrarStatus($idOs, $anterior, $novo, $usuario)
            {
                $this->registro->historico[] = [$idOs, $anterior, $novo, $usuario];
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
        $controller = new class() extends Os {
            public $os_model;

            public $produtos_model;

            public $load;

            public $session;

            public function __construct()
            {
            }
        };
        $controller->os_model = $osModel;
        $controller->produtos_model = $produtosModel;
        $controller->session = new class() {
            public function userdata($chave)
            {
                return $chave === 'id_admin' ? '7' : null;
            }
        };
        $controller->load = new class() {
            public function model($nome)
            {
            }
        };
        $controller->data = ['configuration' => ['control_estoque' => '1']];

        return [$controller, (object) ['idOs' => 12, 'status' => $statusAtual], $registro];
    }

    public function testCancelarDevolveEstoqueEReabrirDebita(): void
    {
        $produtos = [(object) ['produtos_id' => 5, 'quantidade' => 2]];

        [$controller, $os, $registro] = $this->controllerComFakes('Aberto', $produtos);
        $this->assertSame(12, $this->invokeMethod($controller, 'salvarOs', [$os, ['status' => 'Cancelado']]));
        $this->assertSame([[5, 2, '+']], $registro->estoque);
        $this->assertSame(['os', ['status' => 'Cancelado'], 'idOs', 12], $registro->edit);
        // A mudança de status entra no histórico, com quem mudou.
        $this->assertSame([[12, 'Aberto', 'Cancelado', 7]], $registro->historico);

        [$controller, $os, $registro] = $this->controllerComFakes('Cancelado', $produtos);
        $this->invokeMethod($controller, 'salvarOs', [$os, ['status' => 'Aberto']]);
        $this->assertSame([[5, 2, '-']], $registro->estoque);

        [$controller, $os, $registro] = $this->controllerComFakes('Aberto', $produtos);
        $this->invokeMethod($controller, 'salvarOs', [$os, ['status' => 'Finalizado']]);
        $this->assertSame([], $registro->estoque, 'Sem cancelamento, o estoque não muda.');
    }

    private function modelComBanco(): Os_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, telefone TEXT, celular TEXT, documento TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, telefone TEXT, situacao INTEGER)');
        $this->db->query('CREATE TABLE garantias (idGarantias INTEGER PRIMARY KEY AUTOINCREMENT, refGarantia TEXT)');
        $this->db->query("INSERT INTO clientes (nomeCliente, telefone, celular, documento) VALUES ('Ana Souza', '1133', '1199', '123.456.789-09')");
        $this->db->query("INSERT INTO usuarios (nome, telefone, situacao) VALUES ('Técnico Ativo', '11', 1), ('Técnico Inativo', '12', 0)");
        $this->db->query("INSERT INTO garantias (refGarantia) VALUES ('Garantia 90 dias')");

        $model = $this->makeInstance(Os_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    public function testAutocompletesDevolvemListaComValorEDetalhe(): void
    {
        $model = $this->modelComBanco();

        $clientes = $model->autoCompleteCliente('ana');
        $this->assertCount(1, $clientes);
        $this->assertSame('Ana Souza', $clientes[0]['valor']);
        $this->assertSame('123.456.789-09 · 1199', $clientes[0]['detalhe']);
        // label e id continuam para as telas legadas (jQuery UI).
        $this->assertStringStartsWith('Ana Souza | Telefone: 1133', $clientes[0]['label']);
        $this->assertEquals(1, $clientes[0]['id']);

        // Sem resultado é lista vazia (na v4 a resposta vinha sem corpo).
        $this->assertSame([], $model->autoCompleteCliente('ninguém'));

        $tecnicos = $model->autoCompleteUsuario('técnico');
        $this->assertSame(['Técnico Ativo'], array_column($tecnicos, 'valor'), 'Só usuários ativos.');

        $this->assertSame('Garantia 90 dias', $model->autoCompleteTermoGarantia('garantia')[0]['valor']);
    }

    public function testExisteConfereIdsEUsuarioAtivo(): void
    {
        $model = $this->modelComBanco();

        $this->assertTrue($model->existe('clientes', 'idClientes', 1));
        $this->assertFalse($model->existe('clientes', 'idClientes', 99));
        $this->assertTrue($model->existe('usuarios', 'idUsuarios', 1, true));
        $this->assertFalse($model->existe('usuarios', 'idUsuarios', 2, true), 'Técnico inativo não pode ser escolhido.');
    }

    /**
     * O controller usa o id da URL (nunca o idOs do POST), confere a edição
     * pelo id da URL também no GET e segue o padrão dos formulários.
     */
    public function testControllerUsaOIdDaUrlEOPadraoDeFormulario(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Os.php');
        preg_match('/public function editar\(\).*?\n    }\n/s', $codigo, $editar);
        preg_match('/private function formulario\(.*?\n    }\n/s', $codigo, $formulario);

        $this->assertNotEmpty($editar);
        $this->assertNotEmpty($formulario);
        $this->assertStringContainsString('isEditable((int) $os->idOs)', $editar[0]);
        $this->assertStringContainsString("\$this->validarFormulario('os')", $formulario[0]);
        $this->assertStringContainsString('osDadosDoFormulario($this->input->post())', $formulario[0]);
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $formulario[0]);
        $this->assertStringNotContainsString("post('idOs')", $editar[0] . $formulario[0]);
    }

    public function testRegrasDoGrupoOsEmPortugues(): void
    {
        // O arquivo usa get_instance() em outros grupos; lê o grupo "os" do código.
        $codigo = (string) file_get_contents(APPPATH . 'config/form_validation.php');
        preg_match("/    'os' => \\[.*?\\n    \\],\\n/s", $codigo, $grupo);

        $this->assertNotEmpty($grupo);
        $this->assertMatchesRegularExpression("/'field' => 'clientes_id',\\s*'label' => 'Cliente',/", $grupo[0]);
        $this->assertMatchesRegularExpression("/'field' => 'usuarios_id',\\s*'label' => 'Técnico responsável',/", $grupo[0]);
        $this->assertMatchesRegularExpression("/'field' => 'dataInicial',\\s*'label' => 'Data inicial',/", $grupo[0]);
    }
}
