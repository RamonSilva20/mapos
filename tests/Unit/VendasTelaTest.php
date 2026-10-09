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

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Tela da venda (#2843): regras de desconto e fatura, o model (totais, itens
 * da venda, PIX), as views e os endpoints JSON que a tela chama sem recarregar.
 */
final class VendasTelaTest extends MaposTestCase
{
    // --- Regras (vendas_helper) ---------------------------------------

    public function testDescontoDaVendaUsaARegraDaOsComAsMensagensDaVenda(): void
    {
        $this->assertSame([['tipo_desconto' => 'porcento', 'desconto' => 10.0, 'valor_desconto' => 135.0], []], vendaCalcularDesconto(150.0, 'porcento', '10'));
        $this->assertSame([['tipo_desconto' => null, 'desconto' => 0.0, 'valor_desconto' => 0.0], []], vendaCalcularDesconto(150.0, 'real', '0'));

        $this->assertSame('O desconto em porcentagem vai até 100%.', vendaCalcularDesconto(150.0, 'porcento', '101')[1]['desconto']);
        $this->assertSame('O desconto tem de ser menor que o total da venda (R$ 150,00).', vendaCalcularDesconto(150.0, 'real', '150')[1]['desconto']);
        $this->assertSame('Adicione produtos antes de dar desconto.', vendaCalcularDesconto(0.0, 'real', '5')[1]['desconto']);
        $this->assertArrayHasKey('tipoDesconto', vendaCalcularDesconto(150.0, 'outro', '5')[1]);

        // A OS continua com as mensagens dela.
        $this->assertSame('O desconto tem de ser menor que o total da OS (R$ 150,00).', osCalcularDesconto(150.0, 'real', '150')[1]['desconto']);
        $this->assertSame('Adicione produtos ou serviços antes de dar desconto.', osCalcularDesconto(0.0, 'real', '5')[1]['desconto']);
    }

    public function testFaturaDaVendaTrazOVinculoEUsaOsValoresDoServidor(): void
    {
        $venda = (object) ['idVendas' => 9, 'clientes_id' => 3, 'nomeCliente' => 'Ana'];
        $totais = ['bruto' => 150.0, 'desconto' => 15.0, 'total' => 135.0];
        $post = ['descricao' => 'Fatura de Venda Nº: 9', 'vencimento' => '2026-10-10', 'formaPgto' => 'Pix', 'valor' => '1', 'clientes_id' => '99', 'vendas_id' => '77'];

        [$dados, $erros] = vendaFaturaDoFormulario($post, $venda, $totais, 7);

        $this->assertSame([], $erros);
        $this->assertSame(9, $dados['vendas_id'], 'A venda vem da venda conferida, não do POST.');
        $this->assertSame(150.0, $dados['valor']);
        $this->assertSame(135.0, $dados['valor_desconto']);
        $this->assertSame(3, $dados['clientes_id']);
        $this->assertSame('receita', $dados['tipo']);

        [, $erros] = vendaFaturaDoFormulario($post, $venda, ['bruto' => 0.0, 'desconto' => 0.0, 'total' => 0.0], 7);
        $this->assertSame('Adicione produtos antes de faturar.', $erros['_geral']);

        [, $erros] = vendaFaturaDoFormulario(['formaPgto' => 'Escambo'] + $post, $venda, $totais, 7);
        $this->assertArrayHasKey('formaPgto', $erros);
    }

    // --- Model ---------------------------------------------------------

    private function criarBanco(): void
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, celular TEXT, telefone TEXT, contato TEXT, email TEXT, documento TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, telefone TEXT, email TEXT, situacao INTEGER)');
        $this->db->query('CREATE TABLE vendas (idVendas INTEGER PRIMARY KEY AUTOINCREMENT, dataVenda TEXT, valorTotal REAL, desconto REAL DEFAULT 0, faturado INTEGER DEFAULT 0, clientes_id INTEGER, usuarios_id INTEGER,
            lancamentos_id INTEGER, observacoes TEXT, observacoes_cliente TEXT, valor_desconto REAL DEFAULT 0, tipo_desconto TEXT, garantia TEXT, status TEXT)');
        $this->db->query('CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, codDeBarra TEXT, precoVenda REAL, estoque INTEGER)');
        $this->db->query('CREATE TABLE itens_de_vendas (idItens INTEGER PRIMARY KEY AUTOINCREMENT, subTotal REAL, quantidade INTEGER, preco REAL, vendas_id INTEGER, produtos_id INTEGER)');
        $this->db->query('CREATE TABLE lancamentos (idLancamentos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, valor REAL, tipo_desconto TEXT, desconto REAL, valor_desconto REAL,
            clientes_id INTEGER, cliente_fornecedor TEXT, data_vencimento TEXT, data_pagamento TEXT, baixado INTEGER, forma_pgto TEXT, tipo TEXT, observacoes TEXT, usuarios_id INTEGER, vendas_id INTEGER)');

        $this->db->query("INSERT INTO clientes (nomeCliente, celular) VALUES ('Ana', '11999998888')");
        $this->db->query("INSERT INTO usuarios (nome, situacao) VALUES ('Vendedor', 1)");
        $this->db->query("INSERT INTO vendas (clientes_id, usuarios_id, status, dataVenda) VALUES (1, 1, 'Aberto', '2026-10-01'), (1, 1, 'Aberto', '2026-10-02')");
        $this->db->query("INSERT INTO produtos (descricao, precoVenda, estoque) VALUES ('Tela', 100, 10)");
        // Venda 1: 3 × 100; venda 2: 1 × 100.
        $this->db->query('INSERT INTO itens_de_vendas (quantidade, preco, vendas_id, produtos_id, subTotal) VALUES (3, 100, 1, 1, 300), (1, 100, 2, 1, 100)');
    }

    private function model(bool $editavel = true): Vendas_model
    {
        $model = new class() extends Vendas_model {
            public bool $editavel = true;

            public function __construct()
            {
            }

            public function isEditable($id = null)
            {
                return $this->editavel;
            }
        };
        $model->db = $this->db;
        $model->editavel = $editavel;

        return $model;
    }

    public function testItensSoDaPropriaVenda(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $this->assertNotNull($model->getProdutoDaVenda(1, 1));
        $this->assertNull($model->getProdutoDaVenda(1, 2), 'O item 2 é da venda 2.');
        $this->assertNull($model->getProdutoDaVenda(3, 1));
    }

    public function testTotaisDoModel(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $totais = $model->totais($model->getById(1));
        $this->assertSame(300.0, $totais['produtos']);
        $this->assertSame(300.0, $totais['bruto']);
        $this->assertSame(300.0, $totais['total']);
        $this->assertSame(0.0, $totais['desconto']);

        $this->db->query("UPDATE vendas SET desconto = 10, valor_desconto = 270, tipo_desconto = 'porcento' WHERE idVendas = 1");
        $totais = $model->totais($model->getById(1));
        $this->assertSame(270.0, $totais['total']);
        $this->assertSame(30.0, $totais['desconto']);
        $this->assertSame('porcento', $totais['tipo']);
        $this->assertSame(10.0, $totais['informado']);

        $this->db->query('DELETE FROM itens_de_vendas WHERE vendas_id = 2');
        $this->assertSame(0.0, $model->totais($model->getById(2))['total'], 'Venda sem itens.');
    }

    public function testCadastroDoProdutoZerarDescontoEExiste(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $this->assertSame('Tela', $model->getProdutoCadastro(1)->descricao);
        $this->assertNull($model->getProdutoCadastro(99));

        $this->db->query("UPDATE vendas SET desconto = 10, valor_desconto = 270, tipo_desconto = 'porcento' WHERE idVendas = 1");
        $model->zerarDesconto(1);
        $venda = $this->db->query('SELECT * FROM vendas WHERE idVendas = 1')->row();
        $this->assertSame(0.0, (float) $venda->valor_desconto);
        $this->assertSame(0.0, (float) $venda->desconto);
        $this->assertNull($venda->tipo_desconto);

        $this->assertTrue($model->existe('clientes', 'idClientes', 1));
        $this->assertFalse($model->existe('clientes', 'idClientes', 99));
    }

    /**
     * O copia e cola sai do servidor (na v4 o navegador decodificava a imagem
     * do QR com o jsQR): BR Code com o valor da venda e CRC16 válido.
     */
    public function testPixPayloadValido(): void
    {
        $this->criarBanco();
        $model = $this->model();
        $emitente = (object) ['nome' => 'Loja Teste', 'cidade' => 'São Paulo'];

        $payload = $model->getPixPayload(1, '11999998888', $emitente);

        $this->assertNotNull($payload);
        $this->assertStringStartsWith('000201', $payload);
        $this->assertStringContainsString('5406300.00', $payload, 'Valor da venda (300,00) no campo 54.');
        $this->assertSame(substr($payload, -4), $this->crc16(substr($payload, 0, -4)));

        $this->db->query("UPDATE vendas SET desconto = 10, valor_desconto = 270, tipo_desconto = 'porcento' WHERE idVendas = 1");
        $this->assertStringContainsString('5406270.00', (string) $model->getPixPayload(1, '11999998888', $emitente), 'Com desconto, o PIX cobra o total com desconto.');

        $this->assertNull($model->getPixPayload(1, '', $emitente), 'Sem chave PIX não há código.');
        $this->db->query('DELETE FROM itens_de_vendas');
        $this->db->query('UPDATE vendas SET valor_desconto = 0');
        $this->assertNull($model->getPixPayload(1, '11999998888', $emitente), 'Venda sem valor não tem PIX.');
    }

    private function crc16(string $dados): string
    {
        $crc = 0xFFFF;
        for ($i = 0, $n = strlen($dados); $i < $n; $i++) {
            $crc ^= ord($dados[$i]) << 8;
            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    // --- Views --------------------------------------------------------

    /**
     * Renderiza uma view como o CI_Loader faz: variáveis compartilhadas entre
     * a view e os $this->load->view() que ela chama, e $this->security para o
     * token CSRF.
     */
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

    private function dadosDaTela(string $situacao): array
    {
        $xss = '<script>alert(1)</script>';
        $editavel = $situacao === 'aberta';
        $status = ['aberta' => 'Aberto', 'faturada' => 'Faturado', 'cancelada' => 'Cancelado'][$situacao];

        return [
            'venda' => (object) [
                'idVendas' => 9, 'faturado' => $situacao === 'faturada' ? 1 : 0, 'status' => $status, 'nomeCliente' => 'Ana ' . $xss, 'nome' => 'Vendedor',
                'clientes_id' => 3, 'celular' => '11999998888', 'telefone' => '', 'email' => 'ana@example.com', 'dataVenda' => '2026-10-01', 'garantia' => '90',
                'observacoes' => 'Interna<br>' . "\n" . '&lt;b&gt;', 'observacoes_cliente' => '<p>Obrigado <b>Ana</b></p>' . $xss,
            ],
            'totais' => osTotais(300.0, 0.0, 270, 'porcento', 10),
            'produtos' => [(object) ['idItens' => 1, 'descricao' => 'Tela ' . $xss, 'quantidade' => '3', 'preco' => '100', 'precoVenda' => '100', 'subTotal' => '300']],
            'cobranca' => null,
            'emitente' => (object) ['nome' => 'Loja'],
            'whatsapp' => $editavel ? ['telefone' => '5511999998888'] : null,
            'pix' => $editavel ? ['payload' => '000201PIX6304ABCD', 'qr' => 'data:image/png;base64,AAAA', 'chave' => '(11) 99999-8888'] : null,
            'garantia_ate' => '2026-12-30',
            'controle_estoque' => true,
            'gateways' => $editavel ? ['Asaas|boleto' => 'Asaas — Boleto'] : [],
            'pode' => [
                'editar' => $editavel, 'itens' => $editavel, 'faturar' => $editavel, 'excluir' => $editavel, 'cobrar' => $editavel, 'ver_cobranca' => true, 'ver_cliente' => true,
            ],
        ];
    }

    public function testTelaRenderizaEscapandoEOcultandoOQueNaoPodeMudar(): void
    {
        $html = $this->renderizar('vendas/visualizar', $this->dadosDaTela('aberta'));

        $this->assertStringContainsString('Venda #9', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<script', $html, 'Nenhum script inline.');
        $this->assertStringNotContainsString(' onclick', $html);
        $this->assertStringContainsString('Ana &lt;script&gt;', $html);
        $this->assertStringContainsString('data-module="vendas/tela"', $html);
        $this->assertStringContainsString('000201PIX6304ABCD', $html);
        $this->assertStringContainsString('Calculado pelos produtos', $html);
        $this->assertStringContainsString('https://wa.me/5511999998888?text=000201PIX6304ABCD', $html);
        $this->assertStringContainsString('até 30/12/2026', $html);
        $this->assertStringContainsString('data-modal-abrir="faturar-venda"', $html);
        $this->assertStringContainsString('id="form-faturar"', $html);
        $this->assertStringContainsString('name="tipo" value="venda"', $html, 'A cobrança é da venda, não da OS.');
        $this->assertStringContainsString('os/autoCompleteProdutoSaida', $html);
        $this->assertStringContainsString('data-valor-id="1"', $html);
        $this->assertStringContainsString('name="idVendas"', $html);
        $this->assertStringNotContainsString('name="idOs"', $html);
        foreach (['data-os-parte="totais"', 'data-os-parte="produtos"', 'data-os-parte="desconto"', 'data-os-parte="pix"'] as $parte) {
            $this->assertStringContainsString($parte, $html);
        }

        $faturada = $this->renderizar('vendas/visualizar', $this->dadosDaTela('faturada'));
        $this->assertStringContainsString('faturada: produtos e desconto não podem mais ser alterados', $faturada);
        $this->assertStringNotContainsString('data-os-acao="', $faturada, 'Formulários só com a venda editável.');
        $this->assertStringNotContainsString('data-modal-abrir="excluir-', $faturada);
        $this->assertStringNotContainsString('data-modal-abrir="faturar-venda"', $faturada);

        $canceladaComEdicao = $this->dadosDaTela('cancelada');
        $canceladaComEdicao['pode'] = ['editar' => true, 'itens' => false, 'faturar' => false, 'excluir' => true, 'cobrar' => false, 'ver_cobranca' => true, 'ver_cliente' => true];
        $cancelada = $this->renderizar('vendas/visualizar', $canceladaComEdicao);
        $this->assertStringContainsString('já voltaram ao estoque', $cancelada);
        $this->assertStringNotContainsString('id="secao-adicionar-produto"', $cancelada, 'Venda cancelada não recebe produtos.');
        $this->assertStringNotContainsString('data-modal-abrir="excluir-produto"', $cancelada);
        $this->assertStringContainsString('data-os-acao="desconto"', $cancelada);
    }

    public function testTrechosDosEndpointsRenderizamSozinhos(): void
    {
        $dados = $this->dadosDaTela('aberta');
        foreach (['totais', 'desconto', 'pix', 'produtos'] as $parte) {
            $this->assertNotSame('', trim($this->renderizar('vendas/partes/' . $parte, $dados)), $parte);
        }

        $vazio = ['produtos' => [], 'totais' => osTotais(0.0, 0.0)] + $dados;
        $this->assertStringContainsString('Nenhum produto na venda', $this->renderizar('vendas/partes/produtos', $vazio));
        $this->assertStringContainsString('Nenhum item', $this->renderizar('vendas/partes/totais', $vazio));

        $semPix = ['pix' => null] + $dados;
        $this->assertSame('', trim($this->renderizar('vendas/partes/pix', $semPix)));
        $semEdicao = ['pode' => ['editar' => false, 'itens' => false] + $dados['pode']] + $dados;
        $this->assertSame('', trim($this->renderizar('vendas/partes/desconto', $semEdicao)));
    }

    // --- Endpoints JSON ------------------------------------------------

    /**
     * Controller com as dependências do CodeIgniter trocadas por fakes. As
     * partes da tela não são renderizadas: o teste confere quais foram pedidas.
     */
    private function controller(Vendas_model $model, array $post, bool $permitido = true, array $configuracao = ['control_estoque' => '1']): object
    {
        $controller = new class() extends Vendas {
            public $vendas_model;

            public $produtos_model;

            public $load;

            public $input;

            public $output;

            public $permission;

            public $session;

            public $db;

            public array $partes = [];

            public function __construct()
            {
            }

            protected function partesDaTela(int $idVenda, array $partes): array
            {
                $this->partes = $partes;

                return array_fill_keys($partes, '<p>trecho</p>');
            }
        };

        $controller->vendas_model = $model;
        $controller->db = $this->db;
        $controller->data = ['configuration' => $configuracao];
        $controller->load = new class() {
            public function model($nome)
            {
            }
        };
        $controller->produtos_model = new class() {
            public array $chamadas = [];

            public function updateEstoque($produto, $quantidade, $operacao)
            {
                $this->chamadas[] = [(int) $produto, (int) $quantidade, $operacao];
            }
        };
        $controller->input = new class($post) {
            public function __construct(private array $post)
            {
            }

            public function post($chave = null)
            {
                return $chave === null ? $this->post : ($this->post[$chave] ?? null);
            }

            public function get($chave = null)
            {
                return $chave === null ? [] : null;
            }
        };
        $controller->output = new class() {
            public int $status = 200;

            public string $corpo = '';

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
        };
        $controller->permission = new class($permitido) {
            public function __construct(private bool $permitido)
            {
            }

            public function checkPermission($grupo, $permissao)
            {
                return $this->permitido;
            }
        };
        $controller->session = new class() {
            public array $flash = [];

            public function userdata($chave)
            {
                return ['id_admin' => '7', 'nome_admin' => 'Ana', 'permissao' => '1'][$chave] ?? null;
            }

            public function set_flashdata($chave, $valor)
            {
                $this->flash[$chave] = $valor;
            }
        };

        return $controller;
    }

    private function resposta(object $controller): array
    {
        return [$controller->output->status, json_decode($controller->output->corpo, true)];
    }

    public function testExcluirProdutoDevolveAoEstoqueAQuantidadeDoBanco(): void
    {
        $this->criarBanco();
        $this->db->query("UPDATE vendas SET desconto = 10, valor_desconto = 270, tipo_desconto = 'porcento' WHERE idVendas = 1");
        // A quantidade do POST é ignorada: na v4 ela vinha do navegador.
        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idItem' => '1', 'quantidade' => '99', 'produto' => '42']);

        $controller->excluirProduto();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status);
        $this->assertTrue($corpo['result']);
        $this->assertStringContainsString('desconto foi removido', $corpo['message']);
        $this->assertSame(['produtos', 'totais', 'desconto', 'pix'], array_keys($corpo['html']));
        $this->assertSame(0.0, (float) $corpo['totais']['total']);
        $this->assertSame([[1, 3, '+']], $controller->produtos_model->chamadas);
        $this->assertSame(0, (int) $this->db->query('SELECT COUNT(*) AS n FROM itens_de_vendas WHERE vendas_id = 1')->row()->n);
        $this->assertSame(0.0, (float) $this->db->query('SELECT valor_desconto FROM vendas WHERE idVendas = 1')->row()->valor_desconto);
    }

    public function testExcluirProdutoSemControleDeEstoqueNaoMexeNoEstoque(): void
    {
        $this->criarBanco();
        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idItem' => '1'], true, ['control_estoque' => '0']);

        $controller->excluirProduto();

        $this->assertSame(200, $this->resposta($controller)[0]);
        $this->assertSame([], $controller->produtos_model->chamadas);
    }

    public function testItemDeOutraVendaOuVendaInexistenteDa404(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idItem' => '2']);
        $controller->excluirProduto();
        $this->assertSame(404, $this->resposta($controller)[0]);
        $this->assertSame(2, (int) $this->db->query('SELECT COUNT(*) AS n FROM itens_de_vendas')->row()->n, 'Nada foi apagado.');
        $this->assertSame([], $controller->produtos_model->chamadas, 'O estoque não volta por um item que não é da venda.');

        $controller = $this->controller($this->model(), ['idVendas' => '99', 'idItem' => '1']);
        $controller->excluirProduto();
        $this->assertSame(404, $this->resposta($controller)[0]);

        $controller = $this->controller($this->model(), ['idItem' => '1']);
        $controller->adicionarProduto();
        $this->assertSame(404, $this->resposta($controller)[0], 'Sem idVendas.');
    }

    public function testVendaNaoEditavelESemPermissaoDao403(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(false), ['idVendas' => '1', 'idItem' => '1']);
        $controller->excluirProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(403, $status);
        $this->assertFalse($corpo['result']);
        $this->assertSame(2, (int) $this->db->query('SELECT COUNT(*) AS n FROM itens_de_vendas')->row()->n);

        foreach (['adicionarProduto', 'excluirProduto', 'adicionarDesconto', 'faturar'] as $metodo) {
            $controller = $this->controller($this->model(), ['idVendas' => '1', 'idItem' => '1'], false);
            $controller->{$metodo}();
            $this->assertSame(403, $this->resposta($controller)[0], $metodo);
        }
    }

    public function testVendaCanceladaNaoAceitaProdutosMasAceitaDesconto(): void
    {
        $this->criarBanco();
        $this->db->query("UPDATE vendas SET status = 'Cancelado' WHERE idVendas = 1");

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idProduto' => '1', 'quantidade' => '1', 'preco' => '10']);
        $controller->adicionarProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(403, $status);
        $this->assertStringContainsString('reabra a venda', $corpo['message']);
        $this->assertSame([], $controller->produtos_model->chamadas);

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idItem' => '1']);
        $controller->excluirProduto();
        $this->assertSame(403, $this->resposta($controller)[0]);
        $this->assertSame(2, (int) $this->db->query('SELECT COUNT(*) AS n FROM itens_de_vendas')->row()->n);

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'tipoDesconto' => 'real', 'desconto' => '10']);
        $controller->adicionarDesconto();
        $this->assertSame(200, $this->resposta($controller)[0]);
    }

    public function testAdicionarProduto(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idProduto' => '1', 'quantidade' => '20', 'preco' => '100']);
        $controller->adicionarProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertSame('Estoque insuficiente: há 10 em estoque.', $corpo['erros']['quantidade']);
        $this->assertSame([], $controller->produtos_model->chamadas);

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idProduto' => '1', 'quantidade' => '2', 'preco' => '89,90', 'vendas_id' => '2']);
        $controller->adicionarProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status, (string) json_encode($corpo));
        $this->assertSame('Tela adicionado à venda.', $corpo['message']);
        $this->assertSame(['produtos', 'totais', 'desconto', 'pix'], array_keys($corpo['html']));
        $this->assertSame([[1, 2, '-']], $controller->produtos_model->chamadas);
        $linha = $this->db->query('SELECT * FROM itens_de_vendas ORDER BY idItens DESC LIMIT 1')->row();
        $this->assertSame(1, (int) $linha->vendas_id, 'A venda vem do idVendas conferido, não de outro campo do POST.');
        $this->assertSame(179.8, (float) $linha->subTotal);
        $this->assertSame(479.8, (float) $corpo['totais']['total']);

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idProduto' => '77', 'quantidade' => '1', 'preco' => '10']);
        $controller->adicionarProduto();
        $this->assertSame('Escolha um produto da lista.', $this->resposta($controller)[1]['erros']['produto']);

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idProduto' => '1', 'quantidade' => '5', 'preco' => '10'], true, ['control_estoque' => '0']);
        $controller->adicionarProduto();
        $this->assertSame(200, $this->resposta($controller)[0]);
        $this->assertSame([], $controller->produtos_model->chamadas, 'Sem controle de estoque, o estoque não baixa.');
    }

    public function testAdicionarProdutoTiraODescontoEAvisa(): void
    {
        $this->criarBanco();
        $this->db->query("UPDATE vendas SET desconto = 10, valor_desconto = 270, tipo_desconto = 'porcento' WHERE idVendas = 1");
        $controller = $this->controller($this->model(), ['idVendas' => '1', 'idProduto' => '1', 'quantidade' => '1', 'preco' => '10']);

        $controller->adicionarProduto();

        $corpo = $this->resposta($controller)[1];
        $this->assertStringContainsString('O desconto foi removido', $corpo['message']);
        $this->assertSame(0.0, (float) $this->db->query('SELECT valor_desconto FROM vendas WHERE idVendas = 1')->row()->valor_desconto);
    }

    public function testDescontoIgnoraOResultadoDoNavegador(): void
    {
        $this->criarBanco();
        $controller = $this->controller($this->model(), ['idVendas' => '1', 'tipoDesconto' => 'porcento', 'desconto' => '10', 'resultado' => '1']);

        $controller->adicionarDesconto();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status);
        $this->assertSame(['totais', 'desconto', 'pix'], array_keys($corpo['html']));
        $venda = $this->db->query('SELECT * FROM vendas WHERE idVendas = 1')->row();
        $this->assertSame(270.0, (float) $venda->valor_desconto);
        $this->assertSame(10.0, (float) $venda->desconto);
        $this->assertSame('porcento', $venda->tipo_desconto);

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'tipoDesconto' => 'real', 'desconto' => '300']);
        $controller->adicionarDesconto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertStringContainsString('menor que o total da venda', $corpo['erros']['desconto']);
        $this->assertSame(270.0, (float) $this->db->query('SELECT valor_desconto FROM vendas WHERE idVendas = 1')->row()->valor_desconto, 'Desconto inválido não grava.');

        $controller = $this->controller($this->model(), ['idVendas' => '1', 'tipoDesconto' => 'real', 'desconto' => '0']);
        $controller->adicionarDesconto();
        $this->assertSame('Desconto removido.', $this->resposta($controller)[1]['message']);
        $this->assertSame(0.0, (float) $this->db->query('SELECT valor_desconto FROM vendas WHERE idVendas = 1')->row()->valor_desconto);
    }

    public function testFaturarGravaLancamentoEOsDoisVinculos(): void
    {
        $this->criarBanco();
        $this->db->query("UPDATE vendas SET desconto = 10, valor_desconto = 270, tipo_desconto = 'porcento' WHERE idVendas = 1");
        $controller = $this->controller($this->model(), [
            'idVendas' => '1',
            'descricao' => 'Fatura de Venda Nº: 1',
            'vencimento' => '2026-10-15',
            'formaPgto' => 'Pix',
            'recebido' => '1',
            'recebimento' => '2026-10-10',
            'valor' => '1',
        ]);

        $controller->faturar();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status, (string) json_encode($corpo));
        $this->assertStringEndsWith('vendas/visualizar/1', $corpo['redirecionar']);

        $lancamento = $this->db->query('SELECT * FROM lancamentos')->row();
        $this->assertSame(300.0, (float) $lancamento->valor, 'O valor vem dos itens, não do POST.');
        $this->assertSame(30.0, (float) $lancamento->desconto);
        $this->assertSame(270.0, (float) $lancamento->valor_desconto);
        $this->assertSame(1, (int) $lancamento->baixado);
        $this->assertSame('2026-10-10', $lancamento->data_pagamento);
        $this->assertSame(1, (int) $lancamento->vendas_id, 'lancamentos.vendas_id aponta para a venda.');
        $this->assertSame(1, (int) $lancamento->clientes_id);
        $this->assertSame('Ana', $lancamento->cliente_fornecedor);

        $venda = $this->db->query('SELECT * FROM vendas WHERE idVendas = 1')->row();
        $this->assertSame(1, (int) $venda->faturado);
        $this->assertSame('Faturado', $venda->status);
        $this->assertSame((int) $lancamento->idLancamentos, (int) $venda->lancamentos_id, 'vendas.lancamentos_id aponta para o lançamento.');
        $this->assertSame(300.0, (float) $venda->valorTotal);
        $this->assertSame(270.0, (float) $venda->valor_desconto);
        // O desconto digitado e o tipo continuam como estão: os gateways e a área do cliente os leem.
        $this->assertSame(10.0, (float) $venda->desconto);
        $this->assertSame('porcento', $venda->tipo_desconto);
        $this->assertSame('Fatura de Venda Nº: 1', $this->db->query('SELECT descricao FROM lancamentos')->row()->descricao);
    }

    public function testFaturarComErroDeCampoNaoGrava(): void
    {
        $this->criarBanco();
        $controller = $this->controller($this->model(), ['idVendas' => '1', 'descricao' => '', 'vencimento' => '', 'formaPgto' => 'Pix']);

        $controller->faturar();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertSame(['descricao', 'vencimento'], array_keys($corpo['erros']));
        $this->assertSame(0, (int) $this->db->query('SELECT COUNT(*) AS n FROM lancamentos')->row()->n);
        $this->assertSame(0, (int) $this->db->query('SELECT faturado FROM vendas WHERE idVendas = 1')->row()->faturado);
    }

    public function testNaoFaturaDuasVezesNemVendaCanceladaNemSemProdutos(): void
    {
        $this->criarBanco();
        $post = ['descricao' => 'Fatura', 'vencimento' => '2026-10-15', 'formaPgto' => 'Pix'];

        $this->db->query('UPDATE vendas SET faturado = 1 WHERE idVendas = 1');
        $controller = $this->controller($this->model(), ['idVendas' => '1'] + $post);
        $controller->faturar();
        $this->assertSame(422, $this->resposta($controller)[0], 'Já faturada.');

        $this->db->query("UPDATE vendas SET status = 'Cancelado' WHERE idVendas = 2");
        $controller = $this->controller($this->model(), ['idVendas' => '2'] + $post);
        $controller->faturar();
        $this->assertSame(422, $this->resposta($controller)[0], 'Cancelada.');

        $this->db->query("INSERT INTO vendas (clientes_id, usuarios_id, status, dataVenda) VALUES (1, 1, 'Aberto', '2026-10-03')");
        $controller = $this->controller($this->model(), ['idVendas' => '3'] + $post);
        $controller->faturar();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertSame('Adicione produtos antes de faturar.', $corpo['message']);

        $this->assertSame(0, (int) $this->db->query('SELECT COUNT(*) AS n FROM lancamentos')->row()->n);
    }

    // --- Controller e view ----------------------------------------------

    public function testExcluirDevolveEstoqueSalvoCanceladaEApagaAFatura(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Vendas.php');
        preg_match('/public function excluir\(\).*?\n    }\n/s', $controller, $excluir);

        $this->assertNotEmpty($excluir);
        $this->assertStringContainsString("strtolower((string) \$vendaAtual->status) !== 'cancelado'", $excluir[0]);
        $this->assertStringContainsString('$this->devolucaoEstoque($id)', $excluir[0]);
        $this->assertLessThan(strpos($excluir[0], "delete('itens_de_vendas'"), strpos($excluir[0], '$this->devolucaoEstoque($id)'), 'Os produtos voltam antes de os itens serem apagados.');
        $this->assertStringContainsString('excluirFatura(', $excluir[0]);
        $this->assertStringContainsString('listagemQuery(', $excluir[0]);
        $this->assertStringNotContainsString('${', $excluir[0]);
    }

    public function testControllerNaoTemMaisOQueFoiSubstituido(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Vendas.php');

        // Os autocompletes são os de os/autoComplete*; a visualizarVenda($id) não tinha rota que funcionasse.
        $this->assertStringNotContainsString('function autoComplete', $controller);
        $this->assertStringNotContainsString('function visualizarVenda', $controller);
        $this->assertStringNotContainsString('percentual', $controller, 'O tipo de desconto gravado é porcento; "percentual" nunca casava.');

        $mapa = (string) file_get_contents(APPPATH . 'config/permissions_map.php');
        preg_match("/'Vendas' => \\[.*?\\n    \\],/s", $mapa, $vendas);
        $this->assertNotEmpty($vendas);
        $this->assertStringNotContainsString("'autoComplete", $vendas[0]);
        $this->assertStringNotContainsString('visualizarVenda', $vendas[0]);

        foreach (['adicionarVenda', 'editarVenda', 'visualizarVenda'] as $view) {
            $this->assertFileDoesNotExist(APPPATH . "views/vendas/{$view}.php");
        }
    }
}
