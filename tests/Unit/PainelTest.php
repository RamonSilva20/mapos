<?php

require_once APPPATH . 'models/Mapos_model.php';
require_once APPPATH . 'models/Financeiro_model.php';

if (! function_exists('site_url')) {
    function site_url($uri = '')
    {
        return 'http://mapos.test/index.php/' . ltrim((string) $uri, '/');
    }
}

/**
 * Painel inicial (#2847): regras puras, as consultas do painel (SQLite) e a
 * view com e sem permissões.
 */
final class PainelTest extends MaposTestCase
{
    // --- Regras (painel_helper) ---------------------------------------

    public function testAnoDoBalanco(): void
    {
        $this->assertSame(2025, painelAno('2025', 2026));
        $this->assertSame(2027, painelAno('2027', 2026), 'O ano que vem pode (lançamentos futuros).');
        $this->assertSame(2026, painelAno('2028', 2026));
        $this->assertSame(2026, painelAno('1999', 2026));
        $this->assertSame(2026, painelAno('20x6', 2026));
        $this->assertSame(2026, painelAno(['2025'], 2026));
        $this->assertSame(2026, painelAno(null, 2026));
    }

    public function testSomaDosStatus(): void
    {
        $porStatus = ['Aberto' => 2, 'Em Andamento' => 3, 'Aguardando Peças' => 1, 'Faturado' => 9];

        $this->assertSame(6, painelSomar($porStatus, PAINEL_OS_ANDAMENTO));
        $this->assertSame(0, painelSomar($porStatus, PAINEL_OS_ORCAMENTO));
    }

    public function testBalancoTemOsDozeMesesEIgnoraOutroAno(): void
    {
        $balanco = painelBalanco([
            (object) ['mes' => '2026-01', 'tipo' => 'receita', 'total' => '100.50'],
            ['mes' => '2026-01', 'tipo' => 'despesa', 'total' => '40'],
            ['mes' => '2026-12', 'tipo' => 'receita', 'total' => '10'],
            ['mes' => '2025-12', 'tipo' => 'receita', 'total' => '999'],
            ['mes' => null, 'tipo' => 'receita', 'total' => '999'],
            ['mes' => '2026-13', 'tipo' => 'receita', 'total' => '999'],
            ['mes' => '2026-03', 'tipo' => 'outro', 'total' => '999'],
        ], 2026);

        $this->assertCount(12, $balanco['meses']);
        $this->assertSame('Jan', $balanco['meses'][0]);
        $this->assertSame(100.5, $balanco['receitas'][0]);
        $this->assertSame(40.0, $balanco['despesas'][0]);
        $this->assertSame(60.5, $balanco['saldo'][0]);
        $this->assertSame(10.0, $balanco['receitas'][11]);
        $this->assertSame(0.0, $balanco['receitas'][2], 'Tipo desconhecido não entra.');
        $this->assertSame(110.5, array_sum($balanco['receitas']), 'Outro ano e mês inválido não entram.');
    }

    public function testFatiasDeOsPorStatusNaOrdemDoFluxo(): void
    {
        $fatias = painelOsPorStatus(['Faturado' => 2, 'Aberto' => 1, 'Orçamento' => 0, 'Inventado' => 3, '' => 1]);

        $this->assertSame(['Aberto', 'Faturado', 'Inventado', 'Sem status'], array_column($fatias, 'status'));
        $this->assertSame(['info', 'success', 'neutral', 'neutral'], array_column($fatias, 'variante'));
        $this->assertSame([], painelOsPorStatus([]));
    }

    public function testEventoDaAgendaSoComTextoETotalComDesconto(): void
    {
        $os = (object) [
            'idOs' => 7, 'nomeCliente' => 'Ana <b>Souza</b>', 'status' => 'Em Andamento', 'dataInicial' => '2026-10-01', 'dataFinal' => '2026-10-10',
            'descricaoProduto' => '<p>Notebook <b>Dell</b></p>', 'totalProdutos' => '100', 'totalServicos' => '50', 'valor_desconto' => '0', 'faturado' => '0',
        ];

        $evento = painelEventoDaOs($os, 'http://mapos.test/os/visualizar/7', null);
        $this->assertSame('OS 7 · Ana <b>Souza</b>', $evento['title'], 'O texto vai cru: o JS usa textContent e o FullCalendar escapa o título.');
        $this->assertSame('2026-10-10', $evento['start']);
        $this->assertTrue($evento['allDay']);
        $this->assertSame('R$ 150,00', $evento['extendedProps']['total']);
        $this->assertSame('progress', $evento['extendedProps']['variante']);
        $this->assertSame('Notebook Dell', $evento['extendedProps']['equipamento']);
        $this->assertSame('10/10/2026', $evento['extendedProps']['dataFinal']);
        $this->assertFalse($evento['extendedProps']['faturado']);
        $this->assertNull($evento['extendedProps']['urlEditar']);
        foreach ($evento['extendedProps'] as $valor) {
            $this->assertFalse(is_string($valor) && str_contains($valor, '<b>Cliente'), 'Sem trechos de HTML montados no servidor.');
        }

        $os->valor_desconto = '135';
        $os->faturado = '1';
        $evento = painelEventoDaOs($os, 'u', 'e');
        $this->assertSame('R$ 135,00', $evento['extendedProps']['total']);
        $this->assertTrue($evento['extendedProps']['faturado']);
        $this->assertSame('e', $evento['extendedProps']['urlEditar']);
    }

    public function testDataPorExtenso(): void
    {
        $this->assertSame('sexta-feira, 9 de outubro de 2026', painelDataPorExtenso('2026-10-09'));
        $this->assertSame('domingo, 1 de março de 2026', painelDataPorExtenso('2026-03-01'));
        $this->assertSame('', painelDataPorExtenso('lixo'));
    }

    // --- Consultas ------------------------------------------------------

    private function banco(): void
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT)');
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, status TEXT, dataFinal TEXT, clientes_id INTEGER)');
        $this->db->query('CREATE TABLE vendas (idVendas INTEGER PRIMARY KEY AUTOINCREMENT, status TEXT, dataVenda TEXT, clientes_id INTEGER)');
        $this->db->query('CREATE TABLE produtos (idProdutos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, estoque INTEGER, estoqueMinimo INTEGER)');
        $this->db->query('CREATE TABLE lancamentos (idLancamentos INTEGER PRIMARY KEY AUTOINCREMENT, tipo TEXT, descricao TEXT, cliente_fornecedor TEXT, data_vencimento TEXT,
            data_pagamento TEXT, baixado INTEGER, valor REAL, desconto REAL, valor_desconto REAL)');

        $this->db->query("INSERT INTO clientes (nomeCliente) VALUES ('Ana'), ('Bruno')");
        $this->db->query("INSERT INTO os (status, dataFinal, clientes_id) VALUES
            ('Aberto', '2026-10-20', 1), ('Em Andamento', '2026-10-05', 2), ('Aguardando Peças', NULL, 1),
            ('Faturado', '2026-09-01', 1), ('Orçamento', '2026-10-01', 2), ('Aberto', '2026-10-05', 9)");
        $this->db->query("INSERT INTO vendas (status, dataVenda, clientes_id) VALUES ('Aberto', '2026-10-01', 1), ('Faturado', '2026-10-02', 2), ('Orçamento', '2026-10-03', 2)");
        $this->db->query("INSERT INTO produtos (descricao, estoque, estoqueMinimo) VALUES ('Cabo', 1, 5), ('Mouse', 5, 5), ('Teclado', 9, 5), ('Sem mínimo', 0, 0), ('Tela', -2, 3)");
        $this->db->query("INSERT INTO lancamentos (tipo, descricao, data_vencimento, data_pagamento, baixado, valor, desconto, valor_desconto) VALUES
            ('receita', 'Jan', '2026-01-10', '2026-01-15', 1, 100, 0, 0),
            ('receita', 'Jan com desconto', '2026-01-10', '2026-01-20', 1, 100, 10, 90),
            ('despesa', 'Jan', '2026-01-05', '2026-01-05', 1, 30, 5, 0),
            ('receita', 'Pendente', '2026-11-01', NULL, 0, 50, 0, 0),
            ('despesa', 'Pendente antigo', '2026-02-01', NULL, NULL, 20, 0, 0),
            ('receita', 'Ano passado', '2025-12-31', '2025-12-31', 1, 999, 0, 0)");
    }

    private function mapos(): Mapos_model
    {
        $model = $this->makeInstance(Mapos_model::class);
        $model->db = $this->db;

        return $model;
    }

    private function financeiro(): Financeiro_model
    {
        $model = $this->makeInstance(Financeiro_model::class);
        $model->db = $this->db;

        return $model;
    }

    public function testContagemPorStatus(): void
    {
        $this->banco();
        $mapos = $this->mapos();

        $this->assertSame(['Aberto' => 2, 'Aguardando Peças' => 1, 'Em Andamento' => 1, 'Faturado' => 1, 'Orçamento' => 1], $this->ordenado($mapos->contarPorStatus('os')));
        $this->assertSame(2, painelSomar($mapos->contarPorStatus('vendas'), PAINEL_VENDAS_ABERTAS));

        $this->expectException(InvalidArgumentException::class);
        $mapos->contarPorStatus('usuarios');
    }

    private function ordenado(array $lista): array
    {
        ksort($lista);

        return $lista;
    }

    public function testOsDaEntregaMaisProximaSemDataNoFim(): void
    {
        $this->banco();

        $linhas = $this->mapos()->osParaPainel(PAINEL_OS_ANDAMENTO, 10);
        $this->assertSame([6, 2, 1, 3], array_map('intval', array_column($linhas, 'idOs')), 'Mesma data: a OS mais nova primeiro; sem data no fim.');
        $this->assertNull($linhas[0]->nomeCliente, 'Cliente removido não some da lista.');
        $this->assertCount(2, $this->mapos()->osParaPainel(PAINEL_OS_ANDAMENTO, 2));
    }

    public function testVendasEmAbertoDaMaisRecente(): void
    {
        $this->banco();

        $this->assertSame([3, 1], array_map('intval', array_column($this->mapos()->vendasParaPainel(PAINEL_VENDAS_ABERTAS, 10), 'idVendas')));
    }

    public function testEstoqueBaixoSoComMinimoEOMaisAbaixoPrimeiro(): void
    {
        $this->banco();

        $this->assertSame(['Tela', 'Cabo', 'Mouse'], array_column($this->mapos()->produtosEstoqueBaixo(10), 'descricao'));
    }

    public function testBalancoAnualPeloPagamentoComOLiquido(): void
    {
        $this->banco();

        $balanco = painelBalanco($this->financeiro()->balancoAnual(2026), 2026);
        $this->assertSame(190.0, $balanco['receitas'][0], '100 + 90 (o líquido do que teve desconto).');
        $this->assertSame(25.0, $balanco['despesas'][0], 'A despesa também desconta (na v4 só a receita).');
        $this->assertSame(165.0, $balanco['saldo'][0]);
        $this->assertSame(190.0, array_sum($balanco['receitas']), 'Pendente e ano passado não entram.');
    }

    public function testProximosPendentesDoVencimentoMaisAntigo(): void
    {
        $this->banco();

        $linhas = $this->financeiro()->proximosPendentes(10);
        $this->assertSame(['Pendente antigo', 'Pendente'], array_column($linhas, 'descricao'), 'baixado NULL conta como pendente.');
        $this->assertSame(20.0, (float) $linhas[0]->liquido);
    }

    // --- View -----------------------------------------------------------

    private function renderizar(array $variaveis): string
    {
        $carregador = new class() {
            public function render(array $variaveis): string
            {
                extract($variaveis);
                ob_start();
                include APPPATH . 'views/mapos/painel.php';

                return (string) ob_get_clean();
            }
        };

        return $carregador->render($variaveis);
    }

    private function dadosCompletos(): array
    {
        $xss = '<script>alert(1)</script>';

        return [
            'hoje' => '2026-10-09',
            'pode' => ['os' => true, 'vendas' => true, 'lancamentos' => true, 'balanco' => true, 'produtos' => true],
            'os_por_status' => ['Aberto' => 2, 'Aguardando Peças' => 1, 'Orçamento' => 3],
            'os_grafico' => painelOsPorStatus(['Aberto' => 2, 'Aguardando Peças' => 1, 'Orçamento' => 3]),
            'os_lista' => [(object) ['idOs' => 5, 'status' => 'Aberto', 'dataFinal' => '2026-10-01', 'clientes_id' => 1, 'nomeCliente' => 'Ana ' . $xss]],
            'vendas_por_status' => ['Aberto' => 4],
            'vendas_lista' => [(object) ['idVendas' => 9, 'status' => 'Aberto', 'dataVenda' => '2026-10-02', 'clientes_id' => 1, 'nomeCliente' => 'Bia ' . $xss]],
            'mes' => ['receitas' => 100.0, 'despesas' => 40.0, 'saldo' => 60.0],
            'visao_geral' => ['saldo_realizado' => 0.0, 'a_receber' => 12.5, 'a_pagar' => 0.0, 'descontos' => 0.0],
            'lancamentos_lista' => [(object) ['idLancamentos' => 3, 'tipo' => 'despesa', 'descricao' => 'Luz ' . $xss, 'cliente_fornecedor' => 'CPFL', 'data_vencimento' => '2026-10-01', 'liquido' => '80']],
            'ano' => 2026,
            'anos' => [2027, 2026, 2025],
            'balanco' => painelBalanco([['mes' => '2026-10', 'tipo' => 'receita', 'total' => 100]], 2026),
            'estoque_lista' => [(object) ['idProdutos' => 4, 'descricao' => 'Cabo ' . $xss, 'estoque' => 0, 'estoqueMinimo' => 5]],
        ];
    }

    public function testPainelCompletoEscapaEPassaOsDadosAoModulo(): void
    {
        $html = $this->renderizar($this->dadosCompletos());

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertSame(1, substr_count($html, '<script'), 'Só o bloco de dados (page_data), nenhum script inline.');
        $this->assertStringContainsString('<script type="application/json" id="painel-dados">', $html);
        $this->assertStringContainsString('data-module="painel/painel"', $html);
        $this->assertStringContainsString('Sexta-feira, 9 de outubro de 2026', $html);
        $this->assertStringContainsString('R$ 60,00', $html, 'Saldo do mês.');
        $this->assertStringContainsString('1 aguardando peças', $html);
        $this->assertStringContainsString('data-agenda="http://mapos.test/index.php/mapos/calendario"', $html);
        $this->assertStringContainsString('data-grafico="balanco"', $html);
        $this->assertStringContainsString('data-grafico="os"', $html);
        $this->assertStringContainsString('Atrasada · 01/10/2026', $html);
        $this->assertStringContainsString('Vencido · 01/10/2026', $html);
        $this->assertStringContainsString('Sem estoque', $html);
        $this->assertStringContainsString('id="agenda-os"', $html);
        $this->assertSame(count(OS_STATUS_VARIANTES), substr_count($html, 'data-agenda-pill='));
        $this->assertStringContainsString('produtos?estoque=baixo', $html);
        $this->assertStringNotContainsString('bx-', $html);
        $this->assertStringNotContainsString(' style=', $html);
    }

    public function testSemPermissoesNadaDosModulosAparece(): void
    {
        $html = $this->renderizar(['hoje' => '2026-10-09', 'pode' => ['os' => false, 'vendas' => false, 'lancamentos' => false, 'balanco' => false, 'produtos' => false]]);

        $this->assertStringContainsString('Bem-vindo ao Map-OS', $html);
        $this->assertStringNotContainsString('data-agenda', $html);
        $this->assertStringNotContainsString('painel-dados', $html);
        $this->assertStringNotContainsString('kpi', $html);
    }

    public function testCadaBlocoSoComASuaPermissao(): void
    {
        $dados = $this->dadosCompletos();
        $dados['pode'] = ['os' => false, 'vendas' => true, 'lancamentos' => false, 'balanco' => false, 'produtos' => false];

        $html = $this->renderizar($dados);
        $this->assertStringContainsString('Vendas em aberto', $html);
        $this->assertStringNotContainsString('Agenda de entregas', $html);
        $this->assertStringNotContainsString('Lançamentos a vencer', $html);
        $this->assertStringNotContainsString('Saldo do mês', $html);
        $this->assertStringNotContainsString('Estoque baixo', $html);
        $this->assertStringNotContainsString('Balanço de', $html);
    }

    public function testControllerChecaAsPermissoesEOCalendarioDevolveSoTexto(): void
    {
        $codigo = (string) file_get_contents(APPPATH . 'controllers/Mapos.php');
        preg_match('/public function index\(\).*?\n    }\n/s', $codigo, $index);
        preg_match('/public function calendario\(\).*?\n    }\n/s', $codigo, $calendario);

        $this->assertNotEmpty($index);
        foreach (['vOs', 'vVenda', 'vLancamento', 'rFinanceiro', 'vProduto'] as $permissao) {
            $this->assertStringContainsString("\$this->permite('{$permissao}')", $index[0]);
        }
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $index[0]);
        $this->assertStringContainsString('painelEventoDaOs(', $calendario[0]);
        $this->assertStringNotContainsString('<b>', $calendario[0]);
        $this->assertStringContainsString('array_keys(OS_STATUS_VARIANTES)', $calendario[0], 'O filtro de status só aceita os status de OS.');
    }
}
