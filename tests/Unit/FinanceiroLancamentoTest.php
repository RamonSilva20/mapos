<?php

/**
 * Regras puras dos lançamentos financeiros (#2844, financeiro_helper): períodos
 * da listagem, valor líquido, validação do formulário (valores calculados no
 * servidor, controle de baixa, vínculo com o cliente) e parcelamento.
 */
final class FinanceiroLancamentoTest extends MaposTestCase
{
    protected function setUp(): void
    {
    }

    // --- Período e situação ----------------------------------------------

    public function testPeriodosPredefinidos(): void
    {
        // Quarta-feira, 14/10/2026.
        $hoje = new DateTimeImmutable('2026-10-14 15:30:00');

        $this->assertSame(['2026-10-14', '2026-10-14'], financeiroPeriodo('dia', $hoje));
        $this->assertSame(['2026-10-11', '2026-10-17'], financeiroPeriodo('semana', $hoje), 'Domingo a sábado.');
        $this->assertSame(['2026-09-01', '2026-09-30'], financeiroPeriodo('mes_anterior', $hoje));
        $this->assertSame(['2026-10-01', '2026-10-31'], financeiroPeriodo('mes', $hoje));
        $this->assertSame(['2026-11-01', '2026-11-30'], financeiroPeriodo('mes_posterior', $hoje));
        $this->assertSame(['2026-01-01', '2026-12-31'], financeiroPeriodo('ano', $hoje));
        $this->assertNull(financeiroPeriodo('personalizado', $hoje));
        $this->assertNull(financeiroPeriodo('qualquer', $hoje));
    }

    public function testPeriodosNasViradasDeAnoEDeMes(): void
    {
        $this->assertSame(['2027-01-01', '2027-01-31'], financeiroPeriodo('mes_posterior', new DateTimeImmutable('2026-12-31')));
        $this->assertSame(['2026-12-01', '2026-12-31'], financeiroPeriodo('mes_anterior', new DateTimeImmutable('2027-01-31')));
        // 31/03 menos um mês cairia em 31/02: o mês anterior é fevereiro.
        $this->assertSame(['2027-02-01', '2027-02-28'], financeiroPeriodo('mes_anterior', new DateTimeImmutable('2027-03-31')));
        $this->assertSame(['2025-12-28', '2026-01-03'], financeiroPeriodo('semana', new DateTimeImmutable('2026-01-01')));
    }

    public function testLiquidoUsaValorDescontoOuValorMenosDesconto(): void
    {
        $this->assertSame(750.0, financeiroLiquido((object) ['valor' => '1000.00', 'desconto' => '250.00', 'valor_desconto' => '750.00']));
        // Linhas antigas, com valor_desconto zerado.
        $this->assertSame(750.0, financeiroLiquido((object) ['valor' => '1000.00', 'desconto' => '250.00', 'valor_desconto' => '0.00']));
        $this->assertSame(1000.0, financeiroLiquido((object) ['valor' => '1000.00', 'desconto' => null, 'valor_desconto' => null]));
        $this->assertSame(80.5, financeiroLiquido(['valor' => 100, 'desconto' => 0, 'valor_desconto' => 80.5]));
    }

    public function testDescontoEmReaisNuncaFicaNegativo(): void
    {
        $this->assertSame(250.0, financeiroDescontoEmReais((object) ['valor' => '1000.00', 'desconto' => '999', 'valor_desconto' => '750.00']), 'Vale o líquido gravado.');
        $this->assertSame(0.0, financeiroDescontoEmReais((object) ['valor' => '100.00', 'desconto' => '0', 'valor_desconto' => '120.00']));
    }

    public function testPillDeSituacao(): void
    {
        $this->assertSame(['label' => 'Pago', 'variant' => 'success'], financeiroStatusPill((object) ['baixado' => 1, 'data_vencimento' => '2020-01-01'], '2026-10-14'));
        $this->assertSame(['label' => 'Vencido', 'variant' => 'danger'], financeiroStatusPill((object) ['baixado' => 0, 'data_vencimento' => '2026-10-13'], '2026-10-14'));
        $this->assertSame(['label' => 'Pendente', 'variant' => 'warning'], financeiroStatusPill((object) ['baixado' => 0, 'data_vencimento' => '2026-10-14'], '2026-10-14'), 'Vence hoje ainda não venceu.');
        $this->assertSame(['label' => 'Pendente', 'variant' => 'warning'], financeiroStatusPill(['baixado' => null, 'data_vencimento' => '2026-11-01'], '2026-10-14'));
    }

    // --- Centavos e datas ------------------------------------------------

    public function testDividirCentavosSomaSempreOTotal(): void
    {
        $this->assertSame([3334, 3333, 3333], financeiroDividirCentavos(10000, 3));
        $this->assertSame([2500, 2500, 2500, 2500], financeiroDividirCentavos(10000, 4));
        $this->assertSame([1, 0, 0], financeiroDividirCentavos(1, 3));
        $this->assertSame([500], financeiroDividirCentavos(500, 0));
        $this->assertSame(99999, array_sum(financeiroDividirCentavos(99999, 7)));
    }

    public function testCentavosEDecimal(): void
    {
        $this->assertSame(123456, financeiroCentavos('1234.56'));
        $this->assertSame(1999, financeiroCentavos('19.99'), 'Sem erro de ponto flutuante.');
        $this->assertSame('19.99', financeiroDecimal(1999));
        $this->assertSame('0.05', financeiroDecimal(5));
    }

    public function testMesesMantemODiaOuOUltimoDoMes(): void
    {
        $this->assertSame('2026-02-28', financeiroAdicionarMeses('2026-01-31', 1));
        $this->assertSame('2026-03-31', financeiroAdicionarMeses('2026-01-31', 2), 'Volta ao dia 31 quando o mês tem.');
        $this->assertSame('2028-02-29', financeiroAdicionarMeses('2028-01-30', 1));
        $this->assertSame('2027-01-15', financeiroAdicionarMeses('2026-10-15', 3));
        $this->assertSame('2026-10-15', financeiroAdicionarMeses('2026-10-15', 0));
    }

    // --- Formulário --------------------------------------------------------

    private function post(array $mudancas = []): array
    {
        return $mudancas + [
            'tipo' => 'receita',
            'descricao' => 'Venda de balcão',
            'cliente_fornecedor' => 'Ana Souza',
            'valor' => '1.000,00',
            'desconto' => '250,00',
            'data_vencimento' => '2026-10-20',
            'observacoes' => 'Combinado por telefone',
        ];
    }

    private function contexto(array $mudancas = []): array
    {
        return $mudancas + ['hoje' => '2026-10-14'];
    }

    public function testLiquidoEOsValoresSaemDoServidor(): void
    {
        [$dados, $erros] = financeiroLancamentoDoFormulario($this->post(['valor_desconto' => '1,00', 'valor_total' => '99999,00']), $this->contexto());

        $this->assertSame([], $erros);
        $this->assertSame('1000.00', $dados['valor']);
        $this->assertSame('250.00', $dados['desconto']);
        $this->assertSame('750.00', $dados['valor_desconto'], 'O líquido é calculado; o que o navegador mandar em valor_desconto não vale.');
        $this->assertSame('real', $dados['tipo_desconto']);
        $this->assertSame('2026-10-20', $dados['data_vencimento']);
        $this->assertNull($dados['data_pagamento'], 'Pendente não tem data de pagamento.');
        $this->assertSame(0, $dados['baixado']);
        $this->assertNull($dados['forma_pgto']);
        $this->assertNull($dados['clientes_id']);
    }

    public function testSemDescontoOLiquidoEOValor(): void
    {
        [$dados, $erros] = financeiroLancamentoDoFormulario($this->post(['desconto' => '']), $this->contexto());

        $this->assertSame([], $erros);
        $this->assertSame('0.00', $dados['desconto']);
        $this->assertSame('1000.00', $dados['valor_desconto']);
    }

    public function testCamposObrigatoriosEValoresInvalidos(): void
    {
        [, $erros] = financeiroLancamentoDoFormulario(
            ['tipo' => 'outro', 'descricao' => '', 'cliente_fornecedor' => '', 'valor' => '0,00', 'data_vencimento' => '31/02/2026'],
            $this->contexto()
        );

        $this->assertSame(['tipo', 'descricao', 'cliente_fornecedor', 'valor', 'data_vencimento'], array_keys($erros));

        [, $erros] = financeiroLancamentoDoFormulario($this->post(['valor' => 'abc', 'desconto' => 'x']), $this->contexto());
        $this->assertSame(['valor', 'desconto'], array_keys($erros));

        [, $erros] = financeiroLancamentoDoFormulario($this->post(['desconto' => '1.000,00']), $this->contexto());
        $this->assertSame('O desconto tem de ser menor que o valor.', $erros['desconto']);

        [, $erros] = financeiroLancamentoDoFormulario($this->post(['descricao' => str_repeat('a', 256)]), $this->contexto());
        $this->assertArrayHasKey('descricao', $erros);
    }

    public function testArrayNoPostNaoQuebra(): void
    {
        [, $erros] = financeiroLancamentoDoFormulario(['tipo' => ['receita'], 'descricao' => ['a'], 'valor' => ['1'], 'observacoes' => ['x']], $this->contexto());

        $this->assertArrayHasKey('tipo', $erros);
        $this->assertArrayHasKey('descricao', $erros);
        $this->assertArrayHasKey('valor', $erros);
    }

    public function testBaixaExigeDataEFormaDePagamento(): void
    {
        [$dados, $erros] = financeiroLancamentoDoFormulario($this->post(['baixado' => '1', 'data_pagamento' => '2026-10-14', 'forma_pgto' => 'Pix']), $this->contexto());
        $this->assertSame([], $erros);
        $this->assertSame(1, $dados['baixado']);
        $this->assertSame('2026-10-14', $dados['data_pagamento']);
        $this->assertSame('Pix', $dados['forma_pgto']);

        [, $erros] = financeiroLancamentoDoFormulario($this->post(['baixado' => '1']), $this->contexto());
        $this->assertSame(['data_pagamento', 'forma_pgto'], array_keys($erros));

        [, $erros] = financeiroLancamentoDoFormulario($this->post(['baixado' => '1', 'data_pagamento' => '2026-10-14', 'forma_pgto' => 'Escambo']), $this->contexto());
        $this->assertSame(['forma_pgto'], array_keys($erros));

        // Sem baixa, a data enviada é ignorada.
        [$dados] = financeiroLancamentoDoFormulario($this->post(['data_pagamento' => '2026-10-14', 'forma_pgto' => 'Pix']), $this->contexto());
        $this->assertNull($dados['data_pagamento']);
        $this->assertSame('Pix', $dados['forma_pgto']);
    }

    public function testControleDeBaixaSoAceitaHojeOuAMesmaDataJaGravada(): void
    {
        $baixa = $this->post(['baixado' => '1', 'data_pagamento' => '2026-10-10', 'forma_pgto' => 'Pix']);

        [, $erros] = financeiroLancamentoDoFormulario($baixa, $this->contexto(['controle_baixa' => true]));
        $this->assertArrayHasKey('data_pagamento', $erros);

        [, $erros] = financeiroLancamentoDoFormulario($baixa, $this->contexto(['controle_baixa' => true, 'pagamento_atual' => '2026-10-10']));
        $this->assertSame([], $erros, 'Editar outro campo de um lançamento já baixado não exige a data de hoje.');

        [, $erros] = financeiroLancamentoDoFormulario(['data_pagamento' => '2026-10-14'] + $baixa, $this->contexto(['controle_baixa' => true]));
        $this->assertSame([], $erros);

        [, $erros] = financeiroLancamentoDoFormulario($baixa, $this->contexto(['controle_baixa' => false]));
        $this->assertSame([], $erros);
    }

    public function testVinculoComOClienteSoValeComOMesmoNome(): void
    {
        $cliente = (object) ['idClientes' => 7, 'nomeCliente' => 'Ana Souza'];

        [$dados] = financeiroLancamentoDoFormulario($this->post(), $this->contexto(), $cliente);
        $this->assertSame(7, $dados['clientes_id']);

        [$dados] = financeiroLancamentoDoFormulario($this->post(['cliente_fornecedor' => ' ana souza ']), $this->contexto(), $cliente);
        $this->assertSame(7, $dados['clientes_id'], 'Maiúsculas e espaços não desfazem o vínculo.');
        $this->assertSame('ana souza', $dados['cliente_fornecedor']);

        [$dados] = financeiroLancamentoDoFormulario($this->post(['cliente_fornecedor' => 'Papelaria Central']), $this->contexto(), $cliente);
        $this->assertNull($dados['clientes_id'], 'Texto diferente do cliente escolhido: o id postado não vale.');

        [$dados] = financeiroLancamentoDoFormulario($this->post(), $this->contexto(), null);
        $this->assertNull($dados['clientes_id']);
    }

    // --- Parcelamento ------------------------------------------------------

    public function testParcelamentoDoFormulario(): void
    {
        [$dados] = financeiroLancamentoDoFormulario($this->post(['desconto' => '100,00']), $this->contexto());

        [$parcelamento, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '3', 'entrada' => '200,00', 'data_entrada' => '2026-10-14'], $dados);
        $this->assertSame([], $erros);
        $this->assertSame(['parcelas' => 3, 'entrada' => 20000, 'data_entrada' => '2026-10-14'], $parcelamento);

        [$parcelamento, $erros] = financeiroParcelamentoDoFormulario([], $dados);
        $this->assertSame([], $erros);
        $this->assertSame(['parcelas' => 1, 'entrada' => 0, 'data_entrada' => null], $parcelamento, 'Sem o campo, é à vista.');
    }

    public function testParcelamentoRecusaValoresInvalidos(): void
    {
        [$dados] = financeiroLancamentoDoFormulario($this->post(['desconto' => '']), $this->contexto());

        [, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '13'], $dados);
        $this->assertArrayHasKey('parcelas', $erros);

        [, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '0'], $dados);
        $this->assertArrayHasKey('parcelas', $erros);

        [, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '1', 'entrada' => '100,00', 'data_entrada' => '2026-10-14'], $dados);
        $this->assertSame('A entrada só vale para lançamentos parcelados.', $erros['entrada']);

        [, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '3', 'entrada' => '1.000,00', 'data_entrada' => '2026-10-14'], $dados);
        $this->assertSame('A entrada tem de ser menor que o valor a pagar (R$ 1.000,00).', $erros['entrada']);

        [, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '3', 'entrada' => '100,00', 'data_entrada' => ''], $dados);
        $this->assertArrayHasKey('data_entrada', $erros);

        [, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '3', 'entrada' => 'x'], $dados);
        $this->assertArrayHasKey('entrada', $erros);

        [$pago] = financeiroLancamentoDoFormulario($this->post(['baixado' => '1', 'data_pagamento' => '2026-10-14', 'forma_pgto' => 'Pix']), $this->contexto());
        [, $erros] = financeiroParcelamentoDoFormulario(['parcelas' => '2'], $pago);
        $this->assertArrayHasKey('baixado', $erros, 'Parcelado nasce em aberto.');
    }

    public function testLinhasDoParcelamentoSomamOTotalESeguemOsMeses(): void
    {
        [$dados] = financeiroLancamentoDoFormulario($this->post(['descricao' => 'Notebook', 'valor' => '1.000,00', 'desconto' => '100,00', 'data_vencimento' => '2026-01-31', 'forma_pgto' => 'Boleto']), $this->contexto());
        $parcelamento = ['parcelas' => 3, 'entrada' => 30000, 'data_entrada' => '2026-01-10'];

        $linhas = financeiroLinhasDoParcelamento($dados, $parcelamento, 5);

        $this->assertCount(4, $linhas, 'Entrada mais três parcelas.');

        $entrada = $linhas[0];
        $this->assertSame('Notebook - Entrada', $entrada['descricao']);
        $this->assertSame(1, $entrada['baixado']);
        $this->assertSame('2026-01-10', $entrada['data_vencimento']);
        $this->assertSame('2026-01-10', $entrada['data_pagamento']);
        $this->assertSame('300.00', $entrada['valor']);
        $this->assertSame('300.00', $entrada['valor_desconto']);
        $this->assertSame('0.00', $entrada['desconto']);

        $parcelas = array_slice($linhas, 1);
        $this->assertSame(['2026-01-31', '2026-02-28', '2026-03-31'], array_column($parcelas, 'data_vencimento'));
        $this->assertSame(['Notebook - Parcela 1/3', 'Notebook - Parcela 2/3', 'Notebook - Parcela 3/3'], array_column($parcelas, 'descricao'));
        $this->assertSame([0, 0, 0], array_column($parcelas, 'baixado'));
        $this->assertSame([null, null, null], array_column($parcelas, 'data_pagamento'));

        // Líquido 900,00 - entrada 300,00 = 600,00 em três parcelas de 200,00;
        // o desconto de 100,00 também é dividido (33,34 + 33,33 + 33,33).
        $this->assertSame(['200.00', '200.00', '200.00'], array_column($parcelas, 'valor_desconto'));
        $this->assertSame(['33.34', '33.33', '33.33'], array_column($parcelas, 'desconto'));
        $this->assertSame(['233.34', '233.33', '233.33'], array_column($parcelas, 'valor'));

        $liquidos = array_sum(array_map('financeiroCentavos', array_column($linhas, 'valor_desconto')));
        $this->assertSame(90000, $liquidos, 'A soma dos líquidos é o líquido do lançamento.');
        $this->assertSame(10000, array_sum(array_map('financeiroCentavos', array_column($linhas, 'desconto'))));

        foreach ($linhas as $linha) {
            $this->assertSame(5, $linha['usuarios_id']);
            $this->assertSame('receita', $linha['tipo']);
            $this->assertSame('Ana Souza', $linha['cliente_fornecedor']);
            $this->assertSame('Boleto', $linha['forma_pgto']);
            $this->assertSame('real', $linha['tipo_desconto']);
        }
    }

    public function testParcelasSemEntradaDividemOLiquidoComCentavos(): void
    {
        [$dados] = financeiroLancamentoDoFormulario($this->post(['desconto' => '', 'valor' => '100,00']), $this->contexto());

        $linhas = financeiroLinhasDoParcelamento($dados, ['parcelas' => 3, 'entrada' => 0, 'data_entrada' => null], null);

        $this->assertSame(['33.34', '33.33', '33.33'], array_column($linhas, 'valor_desconto'));
        $this->assertNull($linhas[0]['usuarios_id']);
    }

    public function testDescricaoDasParcelasCabeNaColuna(): void
    {
        [$dados] = financeiroLancamentoDoFormulario($this->post(['descricao' => str_repeat('é', 255), 'desconto' => '']), $this->contexto());

        $linhas = financeiroLinhasDoParcelamento($dados, ['parcelas' => 12, 'entrada' => 1000, 'data_entrada' => '2026-10-14'], 1);

        foreach ($linhas as $linha) {
            $this->assertLessThanOrEqual(255, mb_strlen($linha['descricao']));
        }
        $this->assertStringEndsWith(' - Parcela 12/12', $linhas[12]['descricao']);
    }

    // --- Valores da tela -----------------------------------------------------

    public function testValoresDoFormularioVindosDoPostDoLancamentoOuDoPadrao(): void
    {
        $post = financeiroValoresDoFormulario(['descricao' => '  x ', 'valor' => '10,00', 'baixado' => ['1']], null);
        $this->assertSame('x', $post['descricao']);
        $this->assertSame('10,00', $post['valor']);
        $this->assertSame('', $post['baixado'], 'Array no POST vira vazio.');

        $lancamento = (object) [
            'tipo' => 'despesa', 'descricao' => 'Aluguel', 'cliente_fornecedor' => 'Imobiliária', 'clientes_id' => null,
            'valor' => '1234.5', 'desconto' => '34.50', 'valor_desconto' => '1200.00', 'data_vencimento' => '2026-10-05',
            'baixado' => 1, 'data_pagamento' => '2026-10-04', 'forma_pgto' => 'Pix', 'observacoes' => 'Contrato 7',
        ];
        $editar = financeiroValoresDoFormulario(null, $lancamento);
        $this->assertSame('1.234,50', $editar['valor']);
        $this->assertSame('34,50', $editar['desconto']);
        $this->assertSame('2026-10-05', $editar['data_vencimento']);
        $this->assertSame('1', $editar['baixado']);
        $this->assertSame('2026-10-04', $editar['data_pagamento']);

        $lancamento->desconto = '0';
        $lancamento->valor_desconto = '0';
        $lancamento->data_pagamento = null;
        $lancamento->baixado = 0;
        $semDesconto = financeiroValoresDoFormulario(null, $lancamento);
        $this->assertSame('', $semDesconto['desconto']);
        $this->assertSame('', $semDesconto['data_pagamento']);
        $this->assertSame('', $semDesconto['baixado']);

        $novo = financeiroValoresDoFormulario(null, null, ['tipo' => 'despesa', 'data_vencimento' => '2026-10-14']);
        $this->assertSame('despesa', $novo['tipo']);
        $this->assertSame('1', $novo['parcelas']);
        $this->assertSame('2026-10-14', $novo['data_vencimento']);
    }
}
