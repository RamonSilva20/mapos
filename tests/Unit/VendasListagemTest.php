<?php

require_once APPPATH . 'models/Vendas_model.php';
require_once APPPATH . 'controllers/Vendas.php';

/**
 * Listagem de vendas migrada (#2843): filtros, total calculado no SQL, a
 * regra de edição e a exclusão da fatura.
 */
final class VendasListagemTest extends MaposTestCase
{
    private function model(): Vendas_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, documento TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT)');
        $this->db->query('CREATE TABLE vendas (idVendas INTEGER PRIMARY KEY AUTOINCREMENT, dataVenda TEXT, status TEXT, faturado INTEGER DEFAULT 0, garantia TEXT, desconto REAL, valor_desconto REAL, clientes_id INTEGER, usuarios_id INTEGER, lancamentos_id INTEGER)');
        $this->db->query('CREATE TABLE itens_de_vendas (idItens INTEGER PRIMARY KEY AUTOINCREMENT, vendas_id INTEGER, produtos_id INTEGER, quantidade INTEGER, preco REAL, subTotal REAL)');
        $this->db->query('CREATE TABLE lancamentos (idLancamentos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, vendas_id INTEGER)');

        $this->db->query("INSERT INTO clientes (nomeCliente, documento) VALUES ('Ana Souza', '111.111.111-11'), ('Bruno 100% Lima', '222'), ('Carla', '333')");
        $this->db->query("INSERT INTO usuarios (nome) VALUES ('Vendedor')");

        $vendas = [
            // status, cliente, dataVenda, valor_desconto
            ['Aberto', 1, '2026-10-01', 0],
            ['Orçamento', 2, '2026-10-02', 0],
            ['Faturado', 1, '2026-09-01', 150],
            ['Cancelado', 3, '2026-10-03', 0],
        ];
        foreach ($vendas as $v) {
            $this->db->query('INSERT INTO vendas (status, clientes_id, usuarios_id, dataVenda, valor_desconto) VALUES (?, ?, 1, ?, ?)', [$v[0], $v[1], $v[2], $v[3]]);
        }

        // Venda 1: 2 x 50 + 1 x 30 = 130. Venda 3: itens somam 200, mas o valor com desconto (150) vale.
        $this->db->query('INSERT INTO itens_de_vendas (vendas_id, produtos_id, quantidade, preco, subTotal) VALUES (1, 1, 2, 50, 100), (1, 2, 1, 30, 30), (3, 1, 4, 50, 200)');

        $model = $this->makeInstance(Vendas_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        return $model;
    }

    private function ids(array $linhas): array
    {
        return array_map('intval', array_column($linhas, 'idVendas'));
    }

    public function testListaTodasComTotalClienteEVendedorEmOrdemDecrescente(): void
    {
        $model = $this->model();
        $linhas = $model->listar([], 10, 0);

        $this->assertSame([4, 3, 2, 1], $this->ids($linhas));
        $this->assertSame('Vendedor', $linhas[0]->vendedor);
        $this->assertSame('Carla', $linhas[0]->nomeCliente);

        $totais = array_column(array_map(static fn ($l) => ['id' => (int) $l->idVendas, 'total' => $l->total], $linhas), 'total', 'id');
        $this->assertSame(130.0, $totais[1], 'Soma dos itens.');
        $this->assertSame(0.0, $totais[2], 'Venda sem itens.');
        $this->assertSame(150.0, $totais[3], 'O valor com desconto vale mais que a soma dos itens.');
    }

    public function testPesquisaPorClienteDocumentoENumero(): void
    {
        $model = $this->model();

        $this->assertSame([3, 1], $this->ids($model->listar(['pesquisa' => 'Ana'], 10, 0)));
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '222'], 10, 0)), 'Documento do cliente.');
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '2'], 10, 0)), 'Nº da venda 2 (e o documento 222 do mesmo cliente).');
        $this->assertSame([], $this->ids($model->listar(['pesquisa' => 'inexistente'], 10, 0)));
        $this->assertSame([2], $this->ids($model->listar(['pesquisa' => '100%'], 10, 0)), '% e _ são literais na busca.');
        $this->assertSame([], $this->ids($model->listar(['pesquisa' => "x' OR '1'='1"], 10, 0)));
    }

    public function testNumeroDaVendaEntraNaPesquisaSemAnularOsOutrosFiltros(): void
    {
        $model = $this->model();

        $this->assertSame([1], $this->ids($model->listar(['pesquisa' => '1', 'status' => 'Aberto'], 10, 0)));
        $this->assertSame([], $this->ids($model->listar(['pesquisa' => '1', 'status' => 'Cancelado'], 10, 0)));
    }

    public function testFiltrosDeStatusEDatas(): void
    {
        $model = $this->model();

        $this->assertSame([3], $this->ids($model->listar(['status' => 'Faturado'], 10, 0)));
        $this->assertSame([4, 2, 1], $this->ids($model->listar(['de' => '2026-10-01'], 10, 0)));
        $this->assertSame([3], $this->ids($model->listar(['ate' => '2026-09-30'], 10, 0)));
        $this->assertSame([2, 1], $this->ids($model->listar(['de' => '2026-10-01', 'ate' => '2026-10-02'], 10, 0)), 'Os dois limites entram.');
    }

    public function testContarUsaOsMesmosFiltros(): void
    {
        $model = $this->model();

        $this->assertSame(4, $model->contar([]));
        $this->assertSame(2, $model->contar(['pesquisa' => 'Ana']));
        $this->assertSame(1, $model->contar(['pesquisa' => 'Ana', 'status' => 'Faturado']));
        $this->assertSame(0, $model->contar(['pesquisa' => 'Ana', 'de' => '2026-12-01']));
    }

    public function testPaginacaoUsaLimiteEOffsetComOsFiltros(): void
    {
        $model = $this->model();

        $this->assertSame([4, 3], $this->ids($model->listar([], 2, 0)));
        $this->assertSame([2, 1], $this->ids($model->listar([], 2, 2)));
        $this->assertSame([1], $this->ids($model->listar(['pesquisa' => 'Ana'], 1, 1)));
    }

    public function testExcluirFaturaPeloVinculoEPelasDescricoes(): void
    {
        $model = $this->model();
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Fatura de Venda Nº: 3 ', 3]);
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Fatura de Venda Nº: 3', 3]);
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Fatura de Venda - #3', 3]);
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Fatura de Venda Nº: 30 ', 30]);
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Descrição livre do vínculo', 3]);
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Outro lançamento', null]);
        // Fatura da venda 3 só com o vínculo (descrição editada no faturar).
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Venda do balcão', 3]);

        $model->excluirFatura(3, 7);

        $restantes = array_column($this->db->query('SELECT descricao FROM lancamentos ORDER BY idLancamentos')->result_array(), 'descricao');
        $this->assertSame(['Fatura de Venda Nº: 30 ', 'Descrição livre do vínculo', 'Outro lançamento'], $restantes);
    }

    public function testExcluirFaturaNaoApagaLancamentoDeOutraVendaComAMesmaDescricao(): void
    {
        $model = $this->model();
        $this->db->query('INSERT INTO lancamentos (descricao, vendas_id) VALUES (?, ?)', ['Fatura de Venda Nº: 3', 9]);

        $model->excluirFatura(3, null);

        $this->assertSame(1, (int) $this->db->query('SELECT COUNT(*) AS n FROM lancamentos')->row()->n);
    }

    public function testFiltrosDaListagemSaoOsStatusDeOs(): void
    {
        $this->assertSame(array_keys(OS_STATUS_VARIANTES), Vendas::FILTROS['status']);
        $this->assertSame(['pesquisa', 'status', 'de', 'ate'], array_keys(Vendas::FILTROS));
    }

    public function testGarantiaAteSomaOsDiasADataDaVenda(): void
    {
        $this->assertSame('2026-11-09', vendaGarantiaAte('2026-10-10', '30'));
        $this->assertSame('2026-11-09', vendaGarantiaAte('2026-10-10 14:30:00', 30));
        $this->assertNull(vendaGarantiaAte('2026-10-10', '0'));
        $this->assertNull(vendaGarantiaAte('2026-10-10', ''));
        $this->assertNull(vendaGarantiaAte('2026-10-10', 'abc'));
        $this->assertNull(vendaGarantiaAte(null, '30'));
        $this->assertNull(vendaGarantiaAte('0000-00-00', '30'));
    }

    public function testGarantiaPillVigenteOuVencida(): void
    {
        $this->assertNull(vendaGarantiaPill(null));
        $this->assertSame(['label' => 'Até 09/11/2026', 'variant' => 'success'], vendaGarantiaPill('2026-11-09', '2026-10-10'));
        $this->assertSame(['label' => 'Até 10/10/2026', 'variant' => 'success'], vendaGarantiaPill('2026-10-10', '2026-10-10'), 'No último dia ainda vale.');
        $this->assertSame(['label' => 'Venceu em 09/10/2026', 'variant' => 'neutral'], vendaGarantiaPill('2026-10-09', '2026-10-10'), 'Vencida não é erro: neutral, não danger.');
    }

    public function testDescricoesDaFatura(): void
    {
        $this->assertSame(['Fatura de Venda Nº: 7 ', 'Fatura de Venda Nº: 7', 'Fatura de Venda - #7'], vendaDescricoesDaFatura(7));
    }

    public function testEditavelSegueARegraDoIsEditable(): void
    {
        $aberta = (object) ['status' => 'Aberto', 'faturado' => 0];
        $faturada = (object) ['status' => 'Faturado', 'faturado' => 1];
        $cancelada = (object) ['status' => 'Cancelado', 'faturado' => 0];

        $this->assertTrue(osEditavel($aberta, true, false));
        $this->assertFalse(osEditavel($aberta, false, true));
        $this->assertFalse(osEditavel($faturada, true, false));
        $this->assertFalse(osEditavel($cancelada, true, false));
        $this->assertTrue(osEditavel($faturada, true, true));
    }

    public function testIsEditableDoModelBarraFaturadaECanceladaSemAConfiguracao(): void
    {
        $model = new class() extends Vendas_model {
            public array $data = ['configuration' => ['control_edit_vendas' => '0']];

            public ?object $venda = null;

            public function __construct()
            {
            }

            public function getById($id)
            {
                return $this->venda;
            }
        };

        $model->venda = (object) ['status' => 'Aberto', 'faturado' => 0];
        $this->assertTrue($model->isEditable(1));

        foreach ([['Faturado', 1], ['Cancelado', 0], ['Finalizado', 1]] as [$status, $faturado]) {
            $model->venda = (object) ['status' => $status, 'faturado' => $faturado];
            $model->data['configuration']['control_edit_vendas'] = '0';
            $this->assertFalse($model->isEditable(1), "{$status} sem a configuração.");
            $model->data['configuration']['control_edit_vendas'] = '1';
            $this->assertTrue($model->isEditable(1), "{$status} com a configuração.");
        }

        $model->venda = null;
        $this->assertTrue($model->isEditable(99), 'Venda inexistente: quem trata é o controller.');
    }

    /**
     * A listagem segue o padrão das telas migradas e o excluir limpa tudo o que
     * é da venda.
     */
    public function testControllerEViewSeguemOPadrao(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'controllers/Vendas.php');
        preg_match('/public function gerenciar\(\).*?\n    }\n/s', $controller, $gerenciar);
        preg_match('/public function excluir\(\).*?\n    }\n/s', $controller, $excluir);

        $this->assertNotEmpty($gerenciar);
        $this->assertStringContainsString("\$this->data['legacy_assets'] = false;", $gerenciar[0]);
        $this->assertStringContainsString("'Nova venda'", $gerenciar[0]);
        $this->assertStringContainsString('->contar($filtros)', $gerenciar[0], 'O total conta com os mesmos filtros da listagem.');
        $this->assertNotEmpty($excluir);
        $this->assertStringContainsString('excluirFatura(', $excluir[0]);
        $this->assertStringContainsString('listagemQuery(', $excluir[0]);
        $this->assertStringNotContainsString('${', $excluir[0]);

        $view = (string) file_get_contents(APPPATH . 'views/vendas/vendas.php');
        $this->assertStringContainsString("component('modal-confirm'", $view);
        $this->assertStringContainsString('osStatusPill(', $view);
        $this->assertStringNotContainsString('<script', $view);
        $this->assertStringNotContainsString('bx-', $view);
    }
}
