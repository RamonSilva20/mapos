<?php

require_once APPPATH . 'models/Os_model.php';
require_once APPPATH . 'controllers/Os.php';

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
 * Tela da OS (#2842): regras puras (itens, totais, desconto, faturamento,
 * WhatsApp), o model (histórico, itens da OS, PIX) e os endpoints JSON que a
 * tela chama sem recarregar.
 */
final class OsTelaTest extends MaposTestCase
{
    // --- Funções puras (os_helper) -------------------------------------

    public function testNumeroAceitaPontoVirgulaEMilhar(): void
    {
        $this->assertSame(49.9, osNumero('49.9'));
        $this->assertSame(49.9, osNumero('49,90'));
        $this->assertSame(1234.5, osNumero('1.234,50'));
        $this->assertSame(10.0, osNumero(10));
        $this->assertNull(osNumero(''));
        $this->assertNull(osNumero('abc'));
        $this->assertNull(osNumero(['1']));
    }

    public function testItemDoFormulario(): void
    {
        $produto = (object) ['idProdutos' => 5, 'estoque' => 4];

        [$dados, $erros] = osItemDoFormulario(['quantidade' => '2', 'preco' => '49,90'], 'produto', $produto, true);
        $this->assertSame([], $erros);
        $this->assertSame(['produtos_id' => 5, 'quantidade' => 2, 'preco' => 49.9, 'subTotal' => 99.8], $dados);

        [, $erros] = osItemDoFormulario(['quantidade' => '5', 'preco' => '1'], 'produto', $produto, true);
        $this->assertSame('Estoque insuficiente: há 4 em estoque.', $erros['quantidade']);

        [, $erros] = osItemDoFormulario(['quantidade' => '5', 'preco' => '1'], 'produto', $produto, false);
        $this->assertSame([], $erros, 'Sem controle de estoque, a quantidade não é limitada.');

        [, $erros] = osItemDoFormulario(['quantidade' => '1.5', 'preco' => '-1'], 'produto', null);
        $this->assertSame('Escolha um produto da lista.', $erros['produto']);
        $this->assertArrayHasKey('quantidade', $erros);
        $this->assertArrayHasKey('preco', $erros);

        [$dados, $erros] = osItemDoFormulario(['quantidade' => '1,5', 'preco' => '80'], 'servico', (object) ['idServicos' => 3]);
        $this->assertSame([], $erros, 'Serviço aceita quantidade fracionada (horas).');
        $this->assertSame(['servicos_id' => 3, 'quantidade' => 1.5, 'preco' => 80.0, 'subTotal' => 120.0], $dados);
    }

    // --- Desconto individual por item (#2880) --------------------------

    public function testDescontoDoItemEmReaisEPorcentagem(): void
    {
        $this->assertSame([['tipo_desconto' => null, 'desconto' => 0.0, 'subTotal' => 160.0], []], osDescontoDoItem(160.0, null, ''), 'Vazio é sem desconto.');
        $this->assertSame([['tipo_desconto' => null, 'desconto' => 0.0, 'subTotal' => 160.0], []], osDescontoDoItem(160.0, 'real', '0'));
        $this->assertSame([['tipo_desconto' => 'real', 'desconto' => 20.0, 'subTotal' => 140.0], []], osDescontoDoItem(160.0, 'real', '20'));
        $this->assertSame([['tipo_desconto' => 'real', 'desconto' => 20.5, 'subTotal' => 139.5], []], osDescontoDoItem(160.0, 'real', '20,50'));
        $this->assertSame([['tipo_desconto' => 'porcento', 'desconto' => 10.0, 'subTotal' => 144.0], []], osDescontoDoItem(160.0, 'porcento', '10'));
        // O arredondamento é em centavos, sobre o desconto em reais.
        $this->assertSame(66.66, osDescontoDoItem(99.99, 'porcento', '33,33')[0]['subTotal']);
        // Brinde: 100% e o valor inteiro são aceitos, o item fica com subTotal 0.
        $this->assertSame(0.0, osDescontoDoItem(160.0, 'porcento', '100')[0]['subTotal']);
        $this->assertSame(0.0, osDescontoDoItem(160.0, 'real', '160')[0]['subTotal']);
    }

    public function testDescontoDoItemRecusaValoresInvalidos(): void
    {
        $this->assertArrayHasKey('descontoItem', osDescontoDoItem(160.0, 'real', '160,01')[1], 'Passa do valor do item.');
        $this->assertArrayHasKey('descontoItem', osDescontoDoItem(160.0, 'porcento', '100,01')[1]);
        $this->assertArrayHasKey('descontoItem', osDescontoDoItem(160.0, 'real', '-5')[1]);
        $this->assertArrayHasKey('descontoItem', osDescontoDoItem(160.0, 'real', 'abc')[1]);
        $this->assertSame([], osDescontoDoItem(160.0, 'real', ['5'])[1], 'Valor que não é texto nem número vale como vazio.');
    }

    public function testDescontoDoItemExigeTipoQuandoHaValor(): void
    {
        $this->assertArrayHasKey('tipoDescontoItem', osDescontoDoItem(160.0, 'outro', '10')[1]);
        $this->assertArrayHasKey('tipoDescontoItem', osDescontoDoItem(160.0, null, '10')[1]);
        $this->assertSame([], osDescontoDoItem(160.0, 'outro', '0')[1], 'Sem desconto o tipo não importa.');
    }

    public function testItemDoFormularioComDesconto(): void
    {
        $produto = (object) ['idProdutos' => 5, 'estoque' => 10];

        [$dados, $erros] = osItemDoFormulario(['quantidade' => '2', 'preco' => '80', 'tipoDescontoItem' => 'porcento', 'descontoItem' => '10'], 'produto', $produto, true);
        $this->assertSame([], $erros);
        $this->assertSame(['produtos_id' => 5, 'quantidade' => 2, 'preco' => 80.0, 'subTotal' => 144.0, 'desconto' => 10.0, 'tipo_desconto' => 'porcento'], $dados);

        [$dados] = osItemDoFormulario(['quantidade' => '1,5', 'preco' => '100', 'tipoDescontoItem' => 'real', 'descontoItem' => '20'], 'servico', (object) ['idServicos' => 3]);
        $this->assertSame(130.0, $dados['subTotal']);

        // O desconto é sobre o item: mais que preço x quantidade é recusado.
        [$dados, $erros] = osItemDoFormulario(['quantidade' => '1', 'preco' => '50', 'tipoDescontoItem' => 'real', 'descontoItem' => '60'], 'produto', $produto, true);
        $this->assertSame([], $dados);
        $this->assertArrayHasKey('descontoItem', $erros);

        // Sem desconto a linha não ganha as chaves (as colunas ficam no padrão).
        [$dados] = osItemDoFormulario(['quantidade' => '1', 'preco' => '50', 'tipoDescontoItem' => 'real', 'descontoItem' => '0'], 'produto', $produto, true);
        $this->assertArrayNotHasKey('desconto', $dados);
    }

    public function testValorDoItemEDescontosDosItens(): void
    {
        $semDesconto = (object) ['preco' => 50.0, 'precoVenda' => 80.0, 'quantidade' => 2.0, 'subTotal' => 0, 'desconto' => 0];
        $this->assertSame(100.0, osValorDoItem($semDesconto), 'Sem desconto vale preço x quantidade, mesmo com subTotal vazio.');

        $semPreco = (object) ['preco' => 0, 'precoVenda' => 80.0, 'quantidade' => 0, 'subTotal' => 0, 'desconto' => 0];
        $this->assertSame(80.0, osValorDoItem($semPreco), 'Serviço sem preço usa o do cadastro e sem quantidade conta 1.');

        $comDesconto = (object) ['preco' => 80.0, 'quantidade' => 2.0, 'subTotal' => 140.0, 'desconto' => 20.0, 'tipo_desconto' => 'real'];
        $this->assertSame(140.0, osValorDoItem($comDesconto));
        $this->assertSame(20.0, osDescontoDosItens([$semDesconto, $comDesconto]));
        $this->assertSame(0.0, osDescontoDosItens([]));

        $this->assertSame('R$ 20,00', osDescontoItemTexto($comDesconto));
        $porcento = (object) ['preco' => 80.0, 'quantidade' => 2.0, 'subTotal' => 144.0, 'desconto' => 10.0, 'tipo_desconto' => 'porcento'];
        $this->assertSame('10% (R$ 16,00)', osDescontoItemTexto($porcento));
        $this->assertSame('', osDescontoItemTexto($semDesconto));
        $this->assertSame('<br><small>Desconto: 10% (R$ 16,00)</small>', osDescontoItemNota($porcento));
        $this->assertSame('', osDescontoItemNota($semDesconto));
    }

    public function testTotaisComDescontoDosItensEDescontoGeral(): void
    {
        // Os totais de produtos e serviços já vêm com o desconto dos itens;
        // o desconto geral incide sobre esse bruto.
        $totais = osTotais(300.0, 130.0, 387, 'porcento', 10, 70.0);
        $this->assertSame(430.0, $totais['bruto']);
        $this->assertSame(43.0, $totais['desconto']);
        $this->assertSame(387.0, $totais['total']);
        $this->assertSame(70.0, $totais['descontoItens']);

        $this->assertSame(0.0, osTotais(100.0, 0.0)['descontoItens']);
    }

    public function testTotaisDoModelComDescontoIndividual(): void
    {
        $this->criarBanco();
        $model = $this->model();
        // OS 1: produto 3 × 100 com 10% (270) e serviço 1 × 50 com R$ 20 (30).
        $this->db->query("UPDATE produtos_os SET desconto = 10, tipo_desconto = 'porcento', subTotal = 270 WHERE idProdutos_os = 1");
        $this->db->query("UPDATE servicos_os SET desconto = 20, tipo_desconto = 'real', subTotal = 30 WHERE idServicos_os = 1");

        $totais = $model->totais($model->getById(1));
        $this->assertSame(270.0, $totais['produtos']);
        $this->assertSame(30.0, $totais['servicos']);
        $this->assertSame(300.0, $totais['total']);
        $this->assertSame(50.0, $totais['descontoItens']);
        $this->assertSame(0.0, $totais['desconto'], 'O desconto geral é outro.');

        $this->assertSame(100.0, $model->totais($model->getById(2))['total'], 'A OS 2 não muda.');
        $this->assertSame(0.0, $model->totais($model->getById(2))['descontoItens']);
    }

    public function testDescontoPorItemDisponivelSoComAsColunas(): void
    {
        $this->criarBanco();
        $this->assertTrue($this->model()->descontoPorItemDisponivel());

        $this->db->query('ALTER TABLE servicos_os DROP COLUMN desconto');
        $this->assertFalse($this->model()->descontoPorItemDisponivel(), 'Banco sem a migration continua funcionando, sem esse desconto.');
        $this->assertSame(350.0, $this->model()->totais($this->model()->getById(1))['total']);
    }

    public function testTotais(): void
    {
        $semDesconto = osTotais(100.0, 50.0);
        $this->assertSame(150.0, $semDesconto['total']);
        $this->assertSame(0.0, $semDesconto['desconto']);
        $this->assertNull($semDesconto['tipo']);

        $comDesconto = osTotais(100.0, 50.0, 135, 'porcento', 10);
        $this->assertSame(135.0, $comDesconto['total']);
        $this->assertSame(15.0, $comDesconto['desconto']);
        $this->assertSame('porcento', $comDesconto['tipo']);
        $this->assertSame(10.0, $comDesconto['informado']);

        // Depois de faturar sem desconto, valor_desconto guarda o próprio total.
        $this->assertSame(0.0, osTotais(100.0, 50.0, 150)['desconto']);
    }

    public function testDescontoCalculadoNoServidor(): void
    {
        $this->assertSame([['tipo_desconto' => 'porcento', 'desconto' => 10.0, 'valor_desconto' => 135.0], []], osCalcularDesconto(150.0, 'porcento', '10'));
        $this->assertSame([['tipo_desconto' => 'real', 'desconto' => 15.5, 'valor_desconto' => 134.5], []], osCalcularDesconto(150.0, 'real', '15,50'));
        $this->assertSame([['tipo_desconto' => null, 'desconto' => 0.0, 'valor_desconto' => 0.0], []], osCalcularDesconto(150.0, 'real', '0'));

        $this->assertArrayHasKey('desconto', osCalcularDesconto(150.0, 'porcento', '101')[1]);
        $this->assertArrayHasKey('desconto', osCalcularDesconto(150.0, 'real', '150')[1], 'Desconto que zera o total não pode ser gravado.');
        $this->assertArrayHasKey('desconto', osCalcularDesconto(0.0, 'real', '5')[1]);
        $this->assertArrayHasKey('tipoDesconto', osCalcularDesconto(150.0, 'outro', '5')[1]);
    }

    public function testFaturaUsaOsValoresDoServidor(): void
    {
        $os = (object) ['clientes_id' => 3, 'nomeCliente' => 'Ana'];
        $totais = ['bruto' => 150.0, 'desconto' => 15.0, 'total' => 135.0];
        $post = ['descricao' => 'Fatura de OS Nº: 9', 'vencimento' => '2026-10-10', 'formaPgto' => 'Pix', 'valor' => '1', 'clientes_id' => '99'];

        [$dados, $erros] = osFaturaDoFormulario($post, $os, $totais, 7);
        $this->assertSame([], $erros);
        $this->assertSame(150.0, $dados['valor']);
        $this->assertSame(135.0, $dados['valor_desconto']);
        $this->assertSame(3, $dados['clientes_id'], 'O cliente vem da OS, não do POST.');
        $this->assertSame('Ana', $dados['cliente_fornecedor']);
        $this->assertSame(0, $dados['baixado']);
        $this->assertNull($dados['data_pagamento']);

        [, $erros] = osFaturaDoFormulario(['recebido' => '1', 'recebimento' => ''] + $post, $os, $totais, 7);
        $this->assertArrayHasKey('recebimento', $erros);

        [$dados] = osFaturaDoFormulario(['recebido' => '1', 'recebimento' => '2026-10-09'] + $post, $os, $totais, 7);
        $this->assertSame(1, $dados['baixado']);
        $this->assertSame('2026-10-09', $dados['data_pagamento']);

        [, $erros] = osFaturaDoFormulario(['formaPgto' => 'Escambo', 'vencimento' => '10/10/2026', 'descricao' => ''] + $post, $os, $totais, 7);
        $this->assertSame(['descricao', 'vencimento', 'formaPgto'], array_keys($erros));

        [, $erros] = osFaturaDoFormulario($post, $os, ['bruto' => 0.0, 'desconto' => 0.0, 'total' => 0.0], 7);
        $this->assertArrayHasKey('_geral', $erros);
    }

    public function testWhatsApp(): void
    {
        $this->assertSame('5511999998888', osTelefoneWhatsApp('(11) 99999-8888'));
        $this->assertSame('551133334444', osTelefoneWhatsApp('11 3333-4444'));
        $this->assertSame('5511999998888', osTelefoneWhatsApp('+55 11 99999-8888'));
        $this->assertNull(osTelefoneWhatsApp('123'));
        $this->assertNull(osTelefoneWhatsApp(null));

        // Texto da OS gravado pelo formulário (escapado, com <br>): sai sem
        // entidades e com as quebras de linha (na v4 vinha &amp;lt;).
        $texto = osTextoWhatsApp('Olá {CLIENTE_NOME}, OS {NUMERO_OS}: {DEFEITO_OS}', [
            '{CLIENTE_NOME}' => 'Ana & Cia',
            '{NUMERO_OS}' => '9',
            '{DEFEITO_OS}' => osTextoParaGravar("Tela <quebrada>\nNão liga"),
        ]);
        $this->assertSame("Olá Ana & Cia, OS 9: Tela <quebrada>\nNão liga", $texto);
    }

    public function testVencimentoDaGarantiaEOpcoesDeCobranca(): void
    {
        $this->assertSame('2026-11-09', osVencimentoGarantia('2026-10-10', '30', 'Finalizado'));
        $this->assertNull(osVencimentoGarantia('2026-10-10', '30', 'Aberto'), 'A garantia só corre depois de finalizada.');
        $this->assertNull(osVencimentoGarantia('2026-10-10', '', 'Faturado'));

        $opcoes = osOpcoesDeCobranca([
            'asaas' => ['name' => 'Asaas', 'library_name' => 'Asaas', 'payment_methods' => [['name' => 'Boleto', 'value' => 'boleto'], ['name' => 'Link', 'value' => 'link']]],
            'ruim' => ['name' => 'X', 'library_name' => '../Hack', 'payment_methods' => [['name' => 'B', 'value' => 'b']]],
        ]);
        $this->assertSame(['Asaas|boleto' => 'Asaas — Boleto', 'Asaas|link' => 'Asaas — Link'], $opcoes);
    }

    // --- Model ---------------------------------------------------------

    private function criarBanco(bool $historico = true): void
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, celular TEXT, telefone TEXT, contato TEXT, email TEXT, documento TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, telefone TEXT, email TEXT, situacao INTEGER)');
        $this->db->query('CREATE TABLE garantias (idGarantias INTEGER PRIMARY KEY AUTOINCREMENT, refGarantia TEXT, textoGarantia TEXT)');
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, clientes_id INTEGER, usuarios_id INTEGER, garantias_id INTEGER, status TEXT, faturado INTEGER DEFAULT 0,
            desconto REAL DEFAULT 0, valor_desconto REAL DEFAULT 0, tipo_desconto TEXT, valorTotal REAL, lancamento INTEGER, dataInicial TEXT, dataFinal TEXT, garantia TEXT,
            descricaoProduto TEXT, defeito TEXT, observacoes TEXT, laudoTecnico TEXT)');
        $this->db->query('CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, precoVenda REAL, estoque INTEGER)');
        $this->db->query('CREATE TABLE servicos (idServicos INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, preco REAL)');
        $this->db->query('CREATE TABLE produtos_os (idProdutos_os INTEGER PRIMARY KEY AUTOINCREMENT, quantidade INTEGER, preco REAL, os_id INTEGER, produtos_id INTEGER, subTotal REAL, desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE servicos_os (idServicos_os INTEGER PRIMARY KEY AUTOINCREMENT, quantidade REAL, preco REAL, os_id INTEGER, servicos_id INTEGER, subTotal REAL, desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE anotacoes_os (idAnotacoes INTEGER PRIMARY KEY AUTOINCREMENT, anotacao TEXT, data_hora TEXT, os_id INTEGER)');
        $this->db->query('CREATE TABLE lancamentos (idLancamentos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, valor REAL, tipo_desconto TEXT, desconto REAL, valor_desconto REAL,
            clientes_id INTEGER, cliente_fornecedor TEXT, data_vencimento TEXT, data_pagamento TEXT, baixado INTEGER, forma_pgto TEXT, tipo TEXT, observacoes TEXT, usuarios_id INTEGER)');
        if ($historico) {
            $this->db->query('CREATE TABLE os_historico (idHistorico INTEGER PRIMARY KEY AUTOINCREMENT, os_id INTEGER, status_anterior TEXT, status_novo TEXT, usuarios_id INTEGER, data_hora TEXT)');
        }

        $this->db->query("INSERT INTO clientes (nomeCliente, celular) VALUES ('Ana', '11999998888')");
        $this->db->query("INSERT INTO usuarios (nome, situacao) VALUES ('Técnico', 1)");
        $this->db->query("INSERT INTO os (clientes_id, usuarios_id, status) VALUES (1, 1, 'Aberto'), (1, 1, 'Aberto')");
        $this->db->query("INSERT INTO produtos (descricao, precoVenda, estoque) VALUES ('Tela', 100, 10)");
        $this->db->query("INSERT INTO servicos (nome, preco) VALUES ('Mão de obra', 50)");
        // OS 1: produto (3 × 100) e serviço; OS 2: um produto.
        $this->db->query('INSERT INTO produtos_os (quantidade, preco, os_id, produtos_id, subTotal) VALUES (3, 100, 1, 1, 300), (1, 100, 2, 1, 100)');
        $this->db->query('INSERT INTO servicos_os (quantidade, preco, os_id, servicos_id, subTotal) VALUES (1, 50, 1, 1, 50)');
    }

    private function model(bool $editavel = true): Os_model
    {
        $model = new class() extends Os_model {
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

    public function testItensSoDaPropriaOs(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $this->assertNotNull($model->getProdutoDaOs(1, 1));
        $this->assertNull($model->getProdutoDaOs(1, 2), 'O item 2 é da OS 2.');
        $this->assertNotNull($model->getServicoDaOs(1, 1));
        $this->assertNull($model->getServicoDaOs(2, 1));
    }

    public function testTotaisDoModel(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $totais = $model->totais($model->getById(1));
        $this->assertSame(300.0, $totais['produtos']);
        $this->assertSame(50.0, $totais['servicos']);
        $this->assertSame(350.0, $totais['total']);
    }

    public function testHistorico(): void
    {
        $this->criarBanco();
        $model = $this->model();

        $model->registrarStatus(1, null, 'Aberto', 1);
        $model->registrarStatus(1, 'Aberto', 'Aberto', 1);
        $model->registrarStatus(1, 'Aberto', 'Em Andamento', null);

        $historico = $model->getHistorico(1);
        $this->assertCount(2, $historico, 'Status igual ao anterior não registra.');
        $this->assertSame('Em Andamento', $historico[0]->status_novo);
        $this->assertNull($historico[0]->autor, 'Sem usuário: aberta pelo cliente.');
        $this->assertSame('Técnico', $historico[1]->autor);
        $this->assertSame([], $model->getHistorico(2));
    }

    public function testSemATabelaDoHistoricoNadaQuebra(): void
    {
        $this->criarBanco(false);
        $model = $this->model();

        $this->assertFalse($model->historicoDisponivel());
        $model->registrarStatus(1, null, 'Aberto', 1);
        $this->assertSame([], $model->getHistorico(1));
    }

    /**
     * O copia e cola sai do servidor (na v4 o navegador decodificava a imagem
     * do QR com o jsQR): BR Code com o valor da OS e CRC16 válido.
     */
    public function testPixPayloadValido(): void
    {
        $this->criarBanco();
        $model = $this->model();
        $emitente = (object) ['nome' => 'Assistência Teste', 'cidade' => 'São Paulo'];

        $payload = $model->getPixPayload(1, '11999998888', $emitente);

        $this->assertNotNull($payload);
        $this->assertStringStartsWith('000201', $payload);
        $this->assertStringContainsString('5406350.00', $payload, 'Valor da OS (350,00) no campo 54.');
        $this->assertMatchesRegularExpression('/6304[0-9A-F]{4}$/', $payload);
        $this->assertSame(substr($payload, -4), $this->crc16(substr($payload, 0, -4)));

        $this->assertNull($model->getPixPayload(1, '', $emitente), 'Sem chave PIX não há código.');
        $this->db->query('DELETE FROM produtos_os');
        $this->db->query('DELETE FROM servicos_os');
        $this->assertNull($model->getPixPayload(1, '11999998888', $emitente), 'OS sem valor não tem PIX.');
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

    private function dadosDaTela(bool $editavel): array
    {
        $xss = '<script>alert(1)</script>';

        return [
            'os' => (object) [
                'idOs' => 9, 'faturado' => $editavel ? 0 : 1, 'status' => $editavel ? 'Em Andamento' : 'Faturado', 'nomeCliente' => 'Ana ' . $xss, 'nome' => 'Técnico',
                'clientes_id' => 3, 'celular_cliente' => '11999998888', 'telefone_cliente' => '', 'dataInicial' => '2026-10-01', 'dataFinal' => '2026-10-10',
                'garantia' => '90', 'refGarantia' => 'Termo 90', 'garantias_id' => 2, 'descricaoProduto' => 'Notebook<br>' . "\n" . '&lt;b&gt;', 'defeito' => '<p>Não <b>liga</b></p>' . $xss,
                'observacoes' => '', 'laudoTecnico' => null,
            ],
            'totais' => osTotais(300.0, 50.0, 315, 'porcento', 10),
            'produtos' => [(object) ['idProdutos_os' => 1, 'descricao' => 'Tela ' . $xss, 'quantidade' => '3', 'preco' => '100', 'subTotal' => '300']],
            'servicos' => [(object) ['idServicos_os' => 2, 'nome' => 'Mão de obra', 'quantidade' => '', 'preco' => '0', 'precoVenda' => '50']],
            'anexos' => [
                (object) ['idAnexos' => 4, 'anexo' => 'abc.jpg', 'thumb' => 'thumb_abc.jpg', 'url' => 'http://mapos.test/assets/anexos/10-2026/OS-9'],
                (object) ['idAnexos' => 5, 'anexo' => 'def.pdf', 'thumb' => '', 'url' => 'http://mapos.test/assets/anexos/10-2026/OS-9'],
            ],
            'anotacoes' => [(object) ['idAnotacoes' => 6, 'anotacao' => '[Ana] Ligar ' . $xss, 'data_hora' => '2026-10-02 10:30:00']],
            'historico' => [
                (object) ['status_novo' => 'Em Andamento', 'status_anterior' => 'Aberto', 'autor' => 'Ana', 'usuarios_id' => 1, 'data_hora' => '2026-10-02 11:00:00'],
                (object) ['status_novo' => 'Aberto', 'status_anterior' => null, 'autor' => null, 'usuarios_id' => null, 'data_hora' => '2026-10-01 09:00:00'],
            ],
            'historico_disponivel' => true,
            'cobranca' => null,
            'whatsapp' => $editavel ? ['telefone' => '5511999998888', 'texto' => "Olá Ana\nOS 9"] : null,
            'pix' => $editavel ? ['payload' => '000201PIX6304ABCD', 'qr' => 'data:image/png;base64,AAAA', 'chave' => '(11) 99999-8888'] : null,
            'garantia_ate' => null,
            'controle_estoque' => true,
            'gateways' => $editavel ? ['Asaas|boleto' => 'Asaas — Boleto'] : [],
            'pode' => [
                'editar' => $editavel, 'faturar' => $editavel, 'excluir' => $editavel, 'cobrar' => $editavel, 'ver_cobranca' => true, 'ver_cliente' => true,
            ],
        ];
    }

    public function testTelaRenderizaTodasAsAbasEscapando(): void
    {
        foreach ([true, false] as $editavel) {
            foreach (OS_ABAS as $aba) {
                $html = $this->renderizar('os/visualizar', ['aba' => $aba] + $this->dadosDaTela($editavel));

                $contexto = $aba . ($editavel ? ' (editável)' : ' (faturada)');
                $this->assertStringContainsString('data-os-parte="abas"', $html, $contexto);
                $this->assertStringNotContainsString('<script>alert(1)</script>', $html, $contexto);
                $this->assertStringNotContainsString('<script', $html, 'Nenhum script inline: ' . $contexto);
                $this->assertStringNotContainsString(' onclick', $html, $contexto);
                $this->assertSame($editavel, str_contains($html, 'data-os-acao="'), 'Formulários só com a OS editável: ' . $contexto);
            }
        }

        $resumo = $this->renderizar('os/visualizar', ['aba' => 'resumo'] + $this->dadosDaTela(true));
        $this->assertStringContainsString('Ana &lt;script&gt;', $resumo);
        $this->assertStringContainsString('000201PIX6304ABCD', $resumo);
        $this->assertStringContainsString('Calculado pelos produtos e serviços', $resumo);
        $this->assertStringContainsString('https://wa.me/5511999998888?text=Ol%C3%A1%20Ana%0AOS%209', $resumo);
        $this->assertStringContainsString('data-modal-abrir="faturar-os"', $resumo);
        $this->assertStringContainsString('id="form-faturar"', $resumo);
        $this->assertStringContainsString('Laudo técnico', $resumo);
        $this->assertStringContainsString('Não informado', $resumo);
        $this->assertStringContainsString('os/editar/', $resumo);

        $produtos = $this->renderizar('os/visualizar', ['aba' => 'produtos'] + $this->dadosDaTela(true));
        $this->assertStringContainsString('Tela &lt;script&gt;', $produtos);
        $this->assertStringContainsString('data-valor-id="1"', $produtos);
        $this->assertStringContainsString('os/autoCompleteProduto', $produtos);

        $faturada = $this->renderizar('os/visualizar', ['aba' => 'resumo'] + $this->dadosDaTela(false));
        $this->assertStringContainsString('não podem mais ser alterados', $faturada);
        $this->assertStringContainsString('Equipamento e serviço', $faturada);
        $this->assertStringNotContainsString('os/editar/', $faturada);
        $this->assertStringNotContainsString('data-modal-abrir="excluir-', $faturada);
    }

    public function testTelaMostraODescontoIndividualDosItens(): void
    {
        $xss = '<script>alert(1)</script>';
        $dados = ['aba' => 'produtos'] + $this->dadosDaTela(true);
        $dados['produtos'] = [
            (object) ['idProdutos_os' => 1, 'descricao' => 'Tela ' . $xss, 'quantidade' => '2', 'preco' => '80', 'subTotal' => '144', 'desconto' => '10', 'tipo_desconto' => 'porcento'],
            (object) ['idProdutos_os' => 2, 'descricao' => 'Cabo', 'quantidade' => '1', 'preco' => '20', 'subTotal' => '20', 'desconto' => '0', 'tipo_desconto' => null],
        ];
        $dados['totais'] = osTotais(164.0, 50.0, 0, null, 0, 16.0);

        $html = $this->renderizar('os/visualizar', $dados);

        // Formulário de adicionar com o desconto do item (enviado e calculado no servidor).
        $this->assertStringContainsString('name="descontoItem"', $html);
        $this->assertStringContainsString('name="tipoDescontoItem"', $html);
        // Coluna, valor e botão do modal que muda o desconto de um item já na OS.
        $this->assertStringContainsString('10% (R$ 16,00)', $html);
        $this->assertStringContainsString('data-modal-abrir="desconto-item-produto"', $html);
        $this->assertStringContainsString('data-valor-tipo="porcento"', $html);
        $this->assertStringContainsString('data-valor-desconto="10"', $html);
        $this->assertStringContainsString('action="http://mapos.test/index.php/os/descontoProduto"', $html);
        $this->assertStringContainsString('Nos itens: R$ 16,00', $this->renderizar('os/partes/totais', $dados));
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<script', $html);

        // Sem nenhum item com desconto, a coluna não aparece.
        $dados['produtos'] = [$dados['produtos'][1]];
        $this->assertStringNotContainsString('>Desconto</th>', $this->renderizar('os/partes/produtos', $dados));

        // Faturada: nem o formulário nem o modal de desconto.
        $faturada = $this->renderizar('os/visualizar', ['aba' => 'produtos'] + $this->dadosDaTela(false));
        $this->assertStringNotContainsString('descontoItem', $faturada);
        $this->assertStringNotContainsString('desconto-item-produto', $faturada);

        $servicos = $this->renderizar('os/visualizar', ['aba' => 'servicos'] + $this->dadosDaTela(true));
        $this->assertStringContainsString('action="http://mapos.test/index.php/os/descontoServico"', $servicos);
        $this->assertStringContainsString('data-modal-abrir="desconto-item-servico"', $servicos);
    }

    public function testImpressoesEEmailUsamOValorComDescontoDoItem(): void
    {
        $views = [
            'os/imprimirOs', 'os/imprimirOsTermica', 'os/emails/os', 'conecte/imprimirOs', 'conecte/imprimirVenda',
            'vendas/imprimirVenda', 'vendas/imprimirVendaTermica', 'vendas/imprimirVendaOrcamento',
        ];
        foreach ($views as $view) {
            $fonte = (string) file_get_contents(APPPATH . 'views/' . $view . '.php');
            $this->assertStringContainsString('osDescontoItemNota($p)', $fonte, $view . ' mostra o desconto do produto.');
            $this->assertStringNotContainsString('$preco * ($s->quantidade ?: 1)', $fonte, $view . ' soma o serviço pelo valor com desconto.');
        }

        // A cobrança nos gateways usa o total da OS: CobrancaValorTest (#3014).
    }

    public function testTrechosDosEndpointsRenderizamSozinhos(): void
    {
        $dados = ['aba' => 'produtos'] + $this->dadosDaTela(true);
        foreach (['abas', 'totais', 'desconto', 'pix', 'produtos', 'servicos', 'anexos', 'anotacoes', 'historico'] as $parte) {
            $this->assertNotSame('', trim($this->renderizar('os/partes/' . $parte, $dados)), $parte);
        }

        $vazio = ['produtos' => [], 'servicos' => [], 'anexos' => [], 'anotacoes' => [], 'historico' => []] + $dados;
        $this->assertStringContainsString('Nenhum produto na OS', $this->renderizar('os/partes/produtos', $vazio));
        $this->assertStringContainsString('Histórico disponível a partir desta versão', $this->renderizar('os/partes/historico', $vazio));
        $this->assertStringContainsString('Atualizar banco', $this->renderizar('os/partes/historico', ['historico_disponivel' => false] + $vazio));
    }

    public function testAnexoSemArquivoNoDiscoMostraEstadoAusenteEmVezDeImagemQuebrada(): void
    {
        $raiz = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mapos-anexos-view-' . bin2hex(random_bytes(4));
        $anterior = $_ENV['STORAGE_PATH'] ?? null;
        $_ENV['STORAGE_PATH'] = $raiz;
        mkdir($raiz . '/anexos/10-2026/OS-9/thumbs', 0750, true);
        file_put_contents($raiz . '/anexos/10-2026/OS-9/presente.jpg', 'x');
        file_put_contents($raiz . '/anexos/10-2026/OS-9/thumbs/thumb_presente.jpg', 'x');

        try {
            $dados = $this->dadosDaTela(true);
            $dados['anexos'] = [
                (object) ['idAnexos' => 4, 'anexo' => 'presente.jpg', 'thumb' => 'thumb_presente.jpg', 'path' => 'anexos/10-2026/OS-9'],
                (object) ['idAnexos' => 5, 'anexo' => 'sumiu.jpg', 'thumb' => 'thumb_sumiu.jpg', 'path' => 'anexos/10-2026/OS-9'],
                (object) ['idAnexos' => 6, 'anexo' => 'sem-miniatura.pdf', 'thumb' => 'thumb_nao_existe.png', 'path' => 'anexos/10-2026/OS-9'],
            ];
            file_put_contents($raiz . '/anexos/10-2026/OS-9/sem-miniatura.pdf', 'x');

            $html = $this->renderizar('os/partes/anexos', $dados);

            $this->assertStringContainsString('os/anexo/4/thumb', $html, 'O anexo presente mostra a miniatura.');
            $this->assertStringContainsString('os/downloadanexo/4', $html);

            $this->assertSame(1, substr_count($html, 'Arquivo não encontrado'), 'Só o anexo sem arquivo vira estado ausente.');
            $this->assertStringNotContainsString('os/anexo/5', $html, 'Sem link nem miniatura para o arquivo que não existe.');
            $this->assertStringNotContainsString('os/downloadanexo/5', $html);
            $this->assertStringContainsString('excluir-anexo', $html, 'O registro órfão ainda pode ser excluído.');

            $this->assertStringNotContainsString('os/anexo/6/thumb', $html, 'Miniatura que não existe cai no ícone de arquivo.');
            $this->assertStringContainsString('os/downloadanexo/6', $html);
        } finally {
            if ($anterior === null) {
                unset($_ENV['STORAGE_PATH']);
            } else {
                $_ENV['STORAGE_PATH'] = $anterior;
            }
            foreach (['/anexos/10-2026/OS-9/thumbs/thumb_presente.jpg', '/anexos/10-2026/OS-9/presente.jpg', '/anexos/10-2026/OS-9/sem-miniatura.pdf', '/anexos/10-2026/OS-9/thumbs', '/anexos/10-2026/OS-9', '/anexos/10-2026', '/anexos', '/.htaccess', ''] as $item) {
                $caminho = $raiz . $item;
                is_dir($caminho) ? @rmdir($caminho) : @unlink($caminho);
            }
        }
    }

    // --- Endpoints JSON ------------------------------------------------

    /**
     * Controller com as dependências do CodeIgniter trocadas por fakes. As
     * partes da tela não são renderizadas: o teste confere quais foram pedidas.
     */
    private function controller(Os_model $model, array $post, bool $permitido = true, array $configuracao = ['control_estoque' => '1']): object
    {
        $controller = new class() extends Os {
            public $os_model;

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

            protected function partesDaTela(int $idOs, array $partes): array
            {
                $this->partes = $partes;

                return array_fill_keys($partes, '<p>trecho</p>');
            }
        };

        $controller->os_model = $model;
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
        $this->db->query("UPDATE os SET desconto = 10, valor_desconto = 315, tipo_desconto = 'porcento' WHERE idOs = 1");
        // A quantidade do POST é ignorada: na v4 ela vinha do navegador.
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'quantidade' => '99', 'produto' => '42']);

        $controller->excluirProduto();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status);
        $this->assertTrue($corpo['result']);
        $this->assertStringContainsString('desconto foi removido', $corpo['message']);
        $this->assertSame(['abas', 'produtos', 'totais', 'desconto', 'pix'], array_keys($corpo['html']));
        $this->assertSame(50.0, (float) $corpo['totais']['total']);
        $this->assertSame([[1, 3, '+']], $controller->produtos_model->chamadas);
        $this->assertSame(0, (int) $this->db->query('SELECT COUNT(*) AS n FROM produtos_os WHERE os_id = 1')->row()->n);
        $this->assertSame(0.0, (float) $this->db->query('SELECT valor_desconto FROM os WHERE idOs = 1')->row()->valor_desconto);
    }

    public function testItemDeOutraOsOuOsInexistenteDa404(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '2']);
        $controller->excluirProduto();
        $this->assertSame(404, $this->resposta($controller)[0]);
        $this->assertSame(2, (int) $this->db->query('SELECT COUNT(*) AS n FROM produtos_os')->row()->n, 'Nada foi apagado.');

        $controller = $this->controller($this->model(), ['idOs' => '99', 'idItem' => '1']);
        $controller->excluirServico();
        $this->assertSame(404, $this->resposta($controller)[0]);
    }

    public function testOsNaoEditavelESemPermissaoDao403(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(false), ['idOs' => '1', 'idItem' => '1']);
        $controller->excluirServico();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(403, $status);
        $this->assertFalse($corpo['result']);
        $this->assertSame(1, (int) $this->db->query('SELECT COUNT(*) AS n FROM servicos_os')->row()->n);

        $controller = $this->controller($this->model(), ['idOs' => '1', 'anotacao' => 'Oi'], false);
        $controller->adicionarAnotacao();
        $this->assertSame(403, $this->resposta($controller)[0]);
    }

    public function testAdicionarProduto(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idProduto' => '1', 'quantidade' => '20', 'preco' => '100']);
        $controller->adicionarProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertSame('Estoque insuficiente: há 10 em estoque.', $corpo['erros']['quantidade']);

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idProduto' => '1', 'quantidade' => '2', 'preco' => '89,90', 'os_id' => '2']);
        $controller->adicionarProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status, (string) json_encode($corpo));
        $this->assertSame('Tela adicionado à OS.', $corpo['message']);
        $this->assertSame([[1, 2, '-']], $controller->produtos_model->chamadas);
        $linha = $this->db->query('SELECT * FROM produtos_os ORDER BY idProdutos_os DESC LIMIT 1')->row();
        $this->assertSame(1, (int) $linha->os_id, 'A OS vem do idOs conferido, não de outro campo do POST.');
        $this->assertSame(179.8, (float) $linha->subTotal);

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idServico' => '77', 'quantidade' => '1', 'preco' => '10']);
        $controller->adicionarServico();
        $this->assertSame('Escolha um serviço da lista.', $this->resposta($controller)[1]['erros']['servico']);
    }

    public function testAdicionarProdutoComDescontoDoItem(): void
    {
        $this->criarBanco();
        $this->db->query("UPDATE os SET desconto = 10, valor_desconto = 315, tipo_desconto = 'porcento' WHERE idOs = 1");

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idProduto' => '1', 'quantidade' => '2', 'preco' => '80', 'tipoDescontoItem' => 'porcento', 'descontoItem' => '10']);
        $controller->adicionarProduto();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status, (string) json_encode($corpo));
        $linha = $this->db->query('SELECT * FROM produtos_os ORDER BY idProdutos_os DESC LIMIT 1')->row();
        $this->assertSame(144.0, (float) $linha->subTotal, 'O subTotal é calculado no servidor: 2 × 80 menos 10%.');
        $this->assertSame(10.0, (float) $linha->desconto);
        $this->assertSame('porcento', $linha->tipo_desconto);
        $this->assertSame(0.0, (float) $this->db->query('SELECT valor_desconto FROM os WHERE idOs = 1')->row()->valor_desconto, 'O desconto geral sai porque o total mudou.');
        // 300 + 144 (produtos) e 50 (serviço).
        $this->assertSame(494.0, (float) $corpo['totais']['total']);
        $this->assertSame(16.0, (float) $corpo['totais']['descontoItens']);
    }

    public function testAdicionarServicoComDescontoInvalidoDa422ENaoGrava(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idServico' => '1', 'quantidade' => '1', 'preco' => '50', 'tipoDescontoItem' => 'real', 'descontoItem' => '51']);
        $controller->adicionarServico();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertArrayHasKey('descontoItem', $corpo['erros']);
        $this->assertSame(1, (int) $this->db->query('SELECT COUNT(*) AS n FROM servicos_os')->row()->n);
    }

    public function testAdicionarComDescontoSemAMigrationPedeAtualizarOBanco(): void
    {
        $this->criarBanco();
        $this->db->query('ALTER TABLE produtos_os DROP COLUMN desconto');

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idProduto' => '1', 'quantidade' => '1', 'preco' => '50', 'tipoDescontoItem' => 'real', 'descontoItem' => '5']);
        $controller->adicionarProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertStringContainsString('Atualizar banco', $corpo['erros']['_geral']);
        $this->assertSame(2, (int) $this->db->query('SELECT COUNT(*) AS n FROM produtos_os')->row()->n);

        // Sem desconto, adicionar continua funcionando no banco antigo.
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idProduto' => '1', 'quantidade' => '1', 'preco' => '50']);
        $controller->adicionarProduto();
        $this->assertSame(200, $this->resposta($controller)[0]);
    }

    public function testDescontoDoProdutoJaNaOs(): void
    {
        $this->criarBanco();
        $this->db->query("UPDATE os SET desconto = 10, valor_desconto = 315, tipo_desconto = 'porcento' WHERE idOs = 1");

        // O subTotal e o preço do POST são ignorados: valem os do banco.
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '30', 'subTotal' => '1', 'preco' => '1']);
        $controller->descontoProduto();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status, (string) json_encode($corpo));
        $this->assertSame('Desconto do item aplicado. O desconto geral da OS foi removido porque o total mudou.', $corpo['message']);
        $this->assertSame(['abas', 'produtos', 'totais', 'desconto', 'pix'], array_keys($corpo['html']));
        $linha = $this->db->query('SELECT * FROM produtos_os WHERE idProdutos_os = 1')->row();
        $this->assertSame(270.0, (float) $linha->subTotal);
        $this->assertSame(30.0, (float) $linha->desconto);
        $this->assertSame('real', $linha->tipo_desconto);
        $this->assertSame(0.0, (float) $this->db->query('SELECT valor_desconto FROM os WHERE idOs = 1')->row()->valor_desconto);
        $this->assertSame([], $controller->produtos_model->chamadas, 'O estoque não muda.');
        $this->assertSame(320.0, (float) $corpo['totais']['total']);

        // 0 tira o desconto: o subTotal volta a preço x quantidade.
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '0']);
        $controller->descontoProduto();
        $this->assertSame('Desconto do item removido.', $this->resposta($controller)[1]['message']);
        $linha = $this->db->query('SELECT * FROM produtos_os WHERE idProdutos_os = 1')->row();
        $this->assertSame(300.0, (float) $linha->subTotal);
        $this->assertSame(0.0, (float) $linha->desconto);
        $this->assertNull($linha->tipo_desconto);
    }

    public function testDescontoDoServicoUsaOPrecoDoCadastroQuandoAOsNaoTem(): void
    {
        $this->criarBanco();
        // Serviço antigo, sem preço nem quantidade na OS: vale 1 × 50 do cadastro.
        $this->db->query('UPDATE servicos_os SET preco = 0, quantidade = 0, subTotal = 0 WHERE idServicos_os = 1');

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'porcento', 'descontoItem' => '20']);
        $controller->descontoServico();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status, (string) json_encode($corpo));
        $linha = $this->db->query('SELECT * FROM servicos_os WHERE idServicos_os = 1')->row();
        $this->assertSame(40.0, (float) $linha->subTotal);
        $this->assertSame(['abas', 'servicos', 'totais', 'desconto', 'pix'], array_keys($corpo['html']));
        // 300 de produtos e 40 de serviço.
        $this->assertSame(340.0, (float) $corpo['totais']['total']);
        $this->assertSame(10.0, (float) $corpo['totais']['descontoItens']);
    }

    public function testDescontoDoItemValidaEProtege(): void
    {
        $this->criarBanco();

        // Valor em branco: no modal o campo é obrigatório (0 tira o desconto).
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '']);
        $controller->descontoProduto();
        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertSame('Informe o desconto, ou 0 para tirar.', $corpo['erros']['descontoItem']);

        // Mais que o valor do item.
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '300,01']);
        $controller->descontoProduto();
        $this->assertSame(422, $this->resposta($controller)[0]);

        // Item de outra OS e item que não existe.
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '2', 'tipoDescontoItem' => 'real', 'descontoItem' => '5']);
        $controller->descontoProduto();
        $this->assertSame(404, $this->resposta($controller)[0]);
        $controller = $this->controller($this->model(), ['idOs' => '2', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '5']);
        $controller->descontoServico();
        $this->assertSame(404, $this->resposta($controller)[0]);

        // Sem permissão e OS que não pode mais ser alterada.
        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '5'], false);
        $controller->descontoProduto();
        $this->assertSame(403, $this->resposta($controller)[0]);
        $controller = $this->controller($this->model(false), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '5']);
        $controller->descontoProduto();
        $this->assertSame(403, $this->resposta($controller)[0]);

        $this->assertSame(0.0, (float) $this->db->query('SELECT desconto FROM produtos_os WHERE idProdutos_os = 1')->row()->desconto, 'Nada foi gravado.');
    }

    public function testDescontoDoItemSemAMigrationPedeAtualizarOBanco(): void
    {
        $this->criarBanco();
        $this->db->query('ALTER TABLE servicos_os DROP COLUMN desconto');

        $controller = $this->controller($this->model(), ['idOs' => '1', 'idItem' => '1', 'tipoDescontoItem' => 'real', 'descontoItem' => '5']);
        $controller->descontoProduto();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertStringContainsString('Atualizar banco', $corpo['message']);
    }

    public function testDescontoIgnoraOResultadoDoNavegador(): void
    {
        $this->criarBanco();
        $controller = $this->controller($this->model(), ['idOs' => '1', 'tipoDesconto' => 'porcento', 'desconto' => '10', 'resultado' => '1']);

        $controller->adicionarDesconto();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status);
        $this->assertSame(['totais', 'desconto', 'pix'], array_keys($corpo['html']));
        $os = $this->db->query('SELECT * FROM os WHERE idOs = 1')->row();
        $this->assertSame(315.0, (float) $os->valor_desconto);
        $this->assertSame('porcento', $os->tipo_desconto);
    }

    public function testAnotacao(): void
    {
        $this->criarBanco();

        $controller = $this->controller($this->model(), ['idOs' => '1', 'anotacao' => '   ']);
        $controller->adicionarAnotacao();
        $this->assertSame(422, $this->resposta($controller)[0]);

        $controller = $this->controller($this->model(), ['idOs' => '1', 'anotacao' => 'Cliente avisado']);
        $controller->adicionarAnotacao();
        $this->assertSame(200, $this->resposta($controller)[0]);
        $anotacao = $this->db->query('SELECT * FROM anotacoes_os')->row();
        $this->assertSame('[Ana] Cliente avisado', $anotacao->anotacao);

        $controller = $this->controller($this->model(), ['idOs' => '2', 'idItem' => (string) $anotacao->idAnotacoes]);
        $controller->excluirAnotacao();
        $this->assertSame(404, $this->resposta($controller)[0], 'A anotação é da OS 1.');
    }

    public function testFaturarGravaLancamentoVinculoEHistorico(): void
    {
        $this->criarBanco();
        $this->db->query('UPDATE os SET valor_desconto = 315 WHERE idOs = 1');
        $controller = $this->controller($this->model(), [
            'idOs' => '1',
            'descricao' => 'Fatura de OS Nº: 1',
            'vencimento' => '2026-10-15',
            'formaPgto' => 'Pix',
            'recebido' => '1',
            'recebimento' => '2026-10-10',
            'valor' => '1',
        ]);

        $controller->faturar();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(200, $status, (string) json_encode($corpo));
        $this->assertStringEndsWith('os/visualizar/1', $corpo['redirecionar']);

        $lancamento = $this->db->query('SELECT * FROM lancamentos')->row();
        $this->assertSame(350.0, (float) $lancamento->valor);
        $this->assertSame(35.0, (float) $lancamento->desconto);
        $this->assertSame(315.0, (float) $lancamento->valor_desconto);
        $this->assertSame(1, (int) $lancamento->baixado);
        $this->assertSame('2026-10-10', $lancamento->data_pagamento);

        $os = $this->db->query('SELECT * FROM os WHERE idOs = 1')->row();
        $this->assertSame(1, (int) $os->faturado);
        $this->assertSame('Faturado', $os->status);
        $this->assertSame((int) $lancamento->idLancamentos, (int) $os->lancamento, 'os.lancamento passa a ser preenchido.');

        $historico = $this->db->query('SELECT * FROM os_historico')->row();
        $this->assertSame('Aberto', $historico->status_anterior);
        $this->assertSame('Faturado', $historico->status_novo);
        $this->assertSame(7, (int) $historico->usuarios_id);
    }

    public function testFaturarComErroDeCampoNaoGrava(): void
    {
        $this->criarBanco();
        $controller = $this->controller($this->model(), ['idOs' => '1', 'descricao' => '', 'vencimento' => '', 'formaPgto' => 'Pix']);

        $controller->faturar();

        [$status, $corpo] = $this->resposta($controller);
        $this->assertSame(422, $status);
        $this->assertSame(['descricao', 'vencimento'], array_keys($corpo['erros']));
        $this->assertSame(0, (int) $this->db->query('SELECT COUNT(*) AS n FROM lancamentos')->row()->n);
    }
}
