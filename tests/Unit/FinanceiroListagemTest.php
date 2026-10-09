<?php

require_once APPPATH . 'models/Os_model.php';
require_once APPPATH . 'models/Financeiro_model.php';

/**
 * Model de lançamentos (#2844): filtros da listagem (que antes eram um WHERE
 * montado em texto), totais com o mesmo critério para receitas e despesas,
 * sugestões do formulário e a exclusão que desfaz a fatura de venda e de OS.
 */
final class FinanceiroListagemTest extends MaposTestCase
{
    private const HOJE = '2026-10-14';

    private function model(): Financeiro_model
    {
        $this->db->query('CREATE TABLE clientes (idClientes INTEGER PRIMARY KEY AUTOINCREMENT, nomeCliente TEXT, documento TEXT, celular TEXT)');
        $this->db->query('CREATE TABLE usuarios (idUsuarios INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT)');
        $this->db->query('CREATE TABLE lancamentos (idLancamentos INTEGER PRIMARY KEY AUTOINCREMENT, descricao TEXT, valor REAL NOT NULL DEFAULT 0, data_vencimento TEXT NOT NULL,
            data_pagamento TEXT, baixado INTEGER DEFAULT 0, cliente_fornecedor TEXT, forma_pgto TEXT, tipo TEXT, clientes_id INTEGER, observacoes TEXT, usuarios_id INTEGER,
            desconto REAL DEFAULT 0, valor_desconto REAL DEFAULT 0, tipo_desconto TEXT)');
        $this->db->query('CREATE TABLE vendas (idVendas INTEGER PRIMARY KEY AUTOINCREMENT, lancamentos_id INTEGER, faturado INTEGER, status TEXT)');
        $this->db->query('CREATE TABLE os (idOs INTEGER PRIMARY KEY AUTOINCREMENT, lancamento INTEGER, faturado INTEGER, status TEXT)');
        $this->db->query('CREATE TABLE os_historico (idHistorico INTEGER PRIMARY KEY AUTOINCREMENT, os_id INTEGER, status_anterior TEXT, status_novo TEXT, usuarios_id INTEGER, data_hora TEXT)');

        $this->db->query("INSERT INTO clientes (nomeCliente, documento, celular) VALUES ('Ana Souza', '111.111.111-11', '11999998888'), ('Bruno Lima', '222', '')");
        $this->db->query("INSERT INTO usuarios (nome) VALUES ('Maria')");

        // descricao, valor, vencimento, baixado, fornecedor, tipo, desconto, valor_desconto, observacoes
        $linhas = [
            ['Venda balcão', 1000, '2026-10-01', 1, 'Ana Souza', 'receita', 250, 750, 'Contrato 7'],
            ['Aluguel', 2000, '2026-10-05', 0, 'Imobiliária Central', 'despesa', 0, 0, ''],
            ['Peças 100% novas', 300, '2026-10-14', 0, 'Bruno Lima', 'receita', 0, 300, ''],
            ['Internet', 150, '2026-10-20', 1, 'Provedor', 'despesa', 50, 0, 'Plano antigo'],
            ['Serviço antigo', 500, '2026-09-10', 0, 'Ana Souza', 'receita', 0, 0, ''],
        ];
        foreach ($linhas as $l) {
            $this->db->query('INSERT INTO lancamentos (descricao, valor, data_vencimento, baixado, cliente_fornecedor, tipo, desconto, valor_desconto, observacoes, usuarios_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)', $l);
        }

        $model = $this->makeInstance(Financeiro_model::class);
        $this->setPrivateProperty($model, 'db', $this->db);

        $os = $this->makeInstance(Os_model::class);
        $this->setPrivateProperty($os, 'db', $this->db);
        $model->osModel = $os;

        return $model;
    }

    private function ids(array $linhas): array
    {
        return array_map('intval', array_column($linhas, 'idLancamentos'));
    }

    private function listar(Financeiro_model $model, array $filtros = []): array
    {
        return $model->listar($filtros, 50, 0, self::HOJE);
    }

    public function testListaPorVencimentoComOLiquidoEQuemAlterou(): void
    {
        $model = $this->model();

        $linhas = $this->listar($model);

        $this->assertSame([5, 1, 2, 3, 4], $this->ids($linhas), 'Vencimento mais antigo primeiro.');
        $this->assertSame('Maria', $linhas[1]->modificado_por);
        // Líquido: o valor_desconto quando preenchido; senão, valor - desconto.
        $this->assertSame([500.0, 750.0, 2000.0, 300.0, 100.0], array_map(static fn ($l) => (float) $l->liquido, $linhas));
    }

    public function testFiltroDeVencimento(): void
    {
        $model = $this->model();

        $this->assertSame([1, 2, 3, 4], $this->ids($this->listar($model, ['de' => '2026-10-01', 'ate' => '2026-10-31'])));
        $this->assertSame([3], $this->ids($this->listar($model, ['de' => '2026-10-14', 'ate' => '2026-10-14'])));
        $this->assertSame([5], $this->ids($this->listar($model, ['ate' => '2026-09-30'])));
    }

    public function testFiltroDeTipoESituacao(): void
    {
        $model = $this->model();

        $this->assertSame([5, 1, 3], $this->ids($this->listar($model, ['tipo' => 'receita'])));
        $this->assertSame([2, 4], $this->ids($this->listar($model, ['tipo' => 'despesa'])));
        $this->assertSame([1, 4], $this->ids($this->listar($model, ['status' => 'pago'])));
        $this->assertSame([5, 2, 3], $this->ids($this->listar($model, ['status' => 'pendente'])));
        $this->assertSame([5, 2], $this->ids($this->listar($model, ['status' => 'vencido'])), 'Pendente com vencimento antes de hoje; o que vence hoje não conta.');
        $this->assertSame([2], $this->ids($this->listar($model, ['status' => 'vencido', 'tipo' => 'despesa', 'de' => '2026-10-01'])));
        $this->assertCount(5, $this->listar($model, ['tipo' => 'qualquer', 'status' => 'qualquer']), 'Valor fora da lista é ignorado.');
    }

    public function testBaixadoNuloContaComoPendente(): void
    {
        $model = $this->model();
        $this->db->query('UPDATE lancamentos SET baixado = NULL WHERE idLancamentos = 3');

        $this->assertSame([5, 2, 3], $this->ids($this->listar($model, ['status' => 'pendente'])));
        $this->assertSame([3], $this->ids($this->listar($model, ['status' => 'pendente', 'de' => '2026-10-10', 'ate' => '2026-10-15'])));
    }

    public function testPesquisaPorFornecedorDescricaoObservacoesENumero(): void
    {
        $model = $this->model();

        $this->assertSame([5, 1], $this->ids($this->listar($model, ['pesquisa' => 'ana'])));
        $this->assertSame([2], $this->ids($this->listar($model, ['pesquisa' => 'aluguel'])));
        $this->assertSame([4], $this->ids($this->listar($model, ['pesquisa' => 'plano antigo'])), 'Procura nas observações.');
        $this->assertSame([3], $this->ids($this->listar($model, ['pesquisa' => '3'])), 'Número procura também o Nº do lançamento.');
    }

    public function testNumeroNaPesquisaNaoAnulaOsOutrosFiltros(): void
    {
        $model = $this->model();

        $this->assertSame([], $this->ids($this->listar($model, ['pesquisa' => '3', 'tipo' => 'despesa'])));
        $this->assertSame([3], $this->ids($this->listar($model, ['pesquisa' => '3', 'tipo' => 'receita'])));
    }

    /**
     * Os filtros chegam pela URL. Antes da 4.55.1 eles entravam no SQL como
     * texto (SQL injection); hoje são valores ligados pelo Query Builder.
     */
    public function testPayloadsNaoViramSql(): void
    {
        $model = $this->model();

        foreach (["' OR '1'='1", "x%' OR 1=1 -- ", "1; DROP TABLE lancamentos", "\\' OR 1=1 #"] as $payload) {
            $this->assertSame([], $this->listar($model, ['pesquisa' => $payload]), $payload);
            $this->assertSame(0, $model->contar(['pesquisa' => $payload], self::HOJE), $payload);
        }

        // Como texto, a data do payload é maior que todos os vencimentos; se
        // virasse SQL, o OR devolveria a tabela inteira.
        $this->assertSame([], $this->listar($model, ['de' => "2999-01-01' OR '1'='1"]));
        $this->assertCount(5, $this->listar($model), 'A tabela continua inteira.');
    }

    public function testCoringasDoLikeSaoLiterais(): void
    {
        $model = $this->model();

        $this->assertSame([3], $this->ids($this->listar($model, ['pesquisa' => '100%'])));
        $this->assertSame([3], $this->ids($this->listar($model, ['pesquisa' => '%'])), '"%" casa só a descrição que tem "%", não todas as linhas.');
        $this->assertSame([], $this->ids($this->listar($model, ['pesquisa' => '_____'])), '"_" não é curinga de um caractere.');
    }

    public function testPaginacaoEContagemUsamOsMesmosFiltros(): void
    {
        $model = $this->model();
        $filtros = ['de' => '2026-10-01', 'ate' => '2026-10-31', 'tipo' => 'receita'];

        $this->assertSame(2, $model->contar($filtros, self::HOJE));
        $this->assertSame(2, count($this->listar($model, $filtros)));
        $this->assertSame([3], $this->ids($model->listar($filtros, 1, 1, self::HOJE)));
        $this->assertSame(5, $model->contar([], self::HOJE));
    }

    public function testTotaisUsamOMesmoCriterioParaReceitasEDespesas(): void
    {
        $model = $this->model();

        $totais = $model->totais(['de' => '2026-10-01', 'ate' => '2026-10-31'], self::HOJE);

        // Receitas: 750 (pago) + 300 (pendente, vence hoje). Despesas: 2000
        // (pendente, vencida) + 100 (150 - 50 de desconto, paga).
        $this->assertSame(1050.0, $totais['receitas']);
        $this->assertSame(2100.0, $totais['despesas']);
        $this->assertSame(750.0, $totais['receitas_pagas']);
        $this->assertSame(100.0, $totais['despesas_pagas']);
        $this->assertSame(300.0, $totais['receitas_pendentes']);
        $this->assertSame(2000.0, $totais['despesas_pendentes']);
        $this->assertSame(0.0, $totais['receitas_vencidas']);
        $this->assertSame(2000.0, $totais['despesas_vencidas']);
        $this->assertSame(1.0, $totais['vencidos']);
    }

    public function testTotaisSemLancamentosSaoZero(): void
    {
        $model = $this->model();

        $totais = $model->totais(['de' => '2030-01-01', 'ate' => '2030-01-31'], self::HOJE);

        $this->assertSame(array_fill_keys(array_keys($totais), 0.0), $totais);
        $this->assertContains('receitas', array_keys($totais));
    }

    public function testVisaoGeral(): void
    {
        $model = $this->model();

        $geral = $model->visaoGeral();

        $this->assertSame(650.0, $geral['saldo_realizado'], 'Receita paga 750 menos despesa paga 100.');
        $this->assertSame(800.0, $geral['a_receber'], '300 + 500 pendentes.');
        $this->assertSame(2000.0, $geral['a_pagar']);
        $this->assertSame(300.0, $geral['descontos'], '250 + 50 de desconto.');
    }

    public function testGetLancamentoEGetCliente(): void
    {
        $model = $this->model();

        $this->assertSame('Aluguel', $model->getLancamento(2)->descricao);
        $this->assertSame('Maria', $model->getLancamento(2)->modificado_por);
        $this->assertNull($model->getLancamento(99));
        $this->assertSame('Bruno Lima', $model->getCliente(2)->nomeCliente);
        $this->assertNull($model->getCliente(99));
    }

    public function testAdicionarEAtualizar(): void
    {
        $model = $this->model();
        $linha = ['descricao' => 'Nova', 'valor' => '10.00', 'data_vencimento' => '2026-10-30', 'tipo' => 'receita', 'baixado' => 0];

        $id = $model->adicionar($linha);
        $this->assertSame(6, $id);

        $this->assertTrue($model->atualizar($id, ['descricao' => 'Editada', 'valor' => '12.50']));
        $this->assertSame('Editada', $model->getLancamento($id)->descricao);
        $this->assertSame(12.5, (float) $model->getLancamento($id)->valor);
        $this->assertSame('Aluguel', $model->getLancamento(2)->descricao, 'Só o lançamento pedido muda.');
    }

    public function testAdicionarVariosGravaTodosOuNenhum(): void
    {
        $model = $this->model();
        $parcela = static fn (string $nome) => ['descricao' => $nome, 'valor' => '10.00', 'data_vencimento' => '2026-11-01', 'tipo' => 'despesa', 'baixado' => 0];

        $this->assertTrue($model->adicionarVarios([$parcela('P1'), $parcela('P2'), $parcela('P3')]));
        $this->assertSame(8, $model->contar([], self::HOJE));

        // A segunda linha não tem vencimento (NOT NULL): nenhuma entra. O
        // driver do SQLite avisa a violação com um warning do PHP.
        $this->db->db_debug = false;
        $invalida = ['descricao' => 'P5', 'valor' => '10.00', 'data_vencimento' => null, 'tipo' => 'despesa', 'baixado' => 0];
        set_error_handler(static fn () => true, E_WARNING);
        try {
            $this->assertFalse($model->adicionarVarios([$parcela('P4'), $invalida]));
        } finally {
            restore_error_handler();
        }
        $this->assertSame(8, $model->contar([], self::HOJE));
    }

    public function testExcluirLancamentoComum(): void
    {
        $model = $this->model();

        $this->assertTrue($model->excluir(2, 1));

        $this->assertNull($model->getLancamento(2));
        $this->assertSame(4, $model->contar([], self::HOJE));
    }

    /**
     * A fatura excluída desfaz o faturamento: a venda e a OS ligadas a ela
     * voltam a não faturadas, e a mudança da OS entra no histórico dela.
     */
    public function testExcluirFaturaDesfazOFaturamentoDaVendaEDaOs(): void
    {
        $model = $this->model();
        $this->db->query("INSERT INTO vendas (lancamentos_id, faturado, status) VALUES (1, 1, 'Faturado'), (4, 1, 'Faturado')");
        $this->db->query("INSERT INTO os (lancamento, faturado, status) VALUES (1, 1, 'Faturado'), (4, 1, 'Faturado')");

        $this->assertTrue($model->excluir(1, 9));

        $venda = $this->db->query('SELECT * FROM vendas WHERE idVendas = 1')->row();
        $this->assertNull($venda->lancamentos_id);
        $this->assertSame(0, (int) $venda->faturado);
        $this->assertSame('Finalizado', $venda->status);

        $os = $this->db->query('SELECT * FROM os WHERE idOs = 1')->row();
        $this->assertNull($os->lancamento);
        $this->assertSame(0, (int) $os->faturado);
        $this->assertSame('Finalizado', $os->status);

        $historico = $this->db->query('SELECT * FROM os_historico')->result();
        $this->assertCount(1, $historico);
        $this->assertSame(1, (int) $historico[0]->os_id);
        $this->assertSame('Faturado', $historico[0]->status_anterior);
        $this->assertSame('Finalizado', $historico[0]->status_novo);
        $this->assertSame(9, (int) $historico[0]->usuarios_id);

        // A venda e a OS ligadas a outro lançamento não mudam.
        $this->assertSame(4, (int) $this->db->query('SELECT lancamentos_id FROM vendas WHERE idVendas = 2')->row()->lancamentos_id);
        $this->assertSame('Faturado', $this->db->query('SELECT status FROM os WHERE idOs = 2')->row()->status);
    }

    public function testExcluirLancamentoInexistenteNaoMudaNada(): void
    {
        $model = $this->model();
        $this->db->query("INSERT INTO vendas (lancamentos_id, faturado, status) VALUES (1, 1, 'Faturado')");

        $this->assertFalse($model->excluir(99, 1));

        $this->assertSame(5, $model->contar([], self::HOJE));
        $this->assertSame('Faturado', $this->db->query('SELECT status FROM vendas')->row()->status);
    }

    public function testSugestoesDeClientesENomesUsados(): void
    {
        $model = $this->model();

        $clientes = $model->sugerirClientes('ana');
        $this->assertCount(1, $clientes);
        $this->assertSame(1, $clientes[0]['id']);
        $this->assertSame('Ana Souza', $clientes[0]['valor']);
        $this->assertSame('111.111.111-11 · 11999998888', $clientes[0]['detalhe']);
        $this->assertSame([], $model->sugerirClientes('zzz'));
        $this->assertCount(1, $model->sugerirClientes('222'), 'Procura também pelo documento.');

        $nomes = $model->sugerirNomesUsados('prov');
        $this->assertSame([['id' => null, 'label' => 'Provedor', 'valor' => 'Provedor', 'detalhe' => 'Já usado em lançamentos']], $nomes);
        $this->assertCount(1, $model->sugerirNomesUsados('ana'), 'Nome repetido em várias linhas aparece uma vez.');
    }
}
