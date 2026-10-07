<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Filtros da listagem de lançamentos financeiros.
 *
 * Cobre a montagem do WHERE: os filtros chegam pela URL, então o que importa é
 * que nenhum deles entre no SQL como código. Os casos de payload existiam como
 * SQL injection nos filtros de status, tipo e cliente antes da 4.55.1.
 */
final class FinanceiroFiltrosTest extends MaposTestCase
{
    private const INICIO = '01/01/2026';
    private const FIM = '31/12/2026';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLancamentos();
    }

    private function where(array $filtros): string
    {
        return financeiroLancamentosWhere($this->db, array_merge([
            'vencimento_de' => self::INICIO,
            'vencimento_ate' => self::FIM,
        ], $filtros));
    }

    public function testSemFiltrosRetornaTodosOsLancamentosDoPeriodo(): void
    {
        $this->assertSame(4, $this->countLancamentosWhere($this->where([])));
    }

    public function testIntervaloDeDatasRestringeOResultado(): void
    {
        $where = financeiroLancamentosWhere($this->db, [
            'vencimento_de' => '01/02/2026',
            'vencimento_ate' => '28/02/2026',
        ]);

        $this->assertSame(1, $this->countLancamentosWhere($where));
    }

    public function testStatusAceitaZero(): void
    {
        $this->assertSame(3, $this->countLancamentosWhere($this->where(['status' => '0'])));
    }

    public function testStatusAceitaUm(): void
    {
        $this->assertSame(1, $this->countLancamentosWhere($this->where(['status' => '1'])));
    }

    public function testStatusInvalidoEhDescartadoEmVezDeGerarClausula(): void
    {
        $where = $this->where(['status' => 'lixo']);

        $this->assertStringNotContainsStringIgnoringCase('baixado', $where);
        $this->assertSame(4, $this->countLancamentosWhere($where));
    }

    public function testTipoFiltraReceitaEDespesa(): void
    {
        $this->assertSame(2, $this->countLancamentosWhere($this->where(['tipo' => 'receita'])));
        $this->assertSame(2, $this->countLancamentosWhere($this->where(['tipo' => 'despesa'])));
    }

    public function testTipoInvalidoEhDescartado(): void
    {
        $where = $this->where(['tipo' => 'transferencia']);

        $this->assertStringNotContainsStringIgnoringCase('tipo =', $where);
        $this->assertSame(4, $this->countLancamentosWhere($where));
    }

    public function testClienteFiltraPorTrecho(): void
    {
        $this->assertSame(2, $this->countLancamentosWhere($this->where(['cliente' => 'Acme'])));
    }

    public function testClienteVazioNaoGeraClausulaLike(): void
    {
        $where = $this->where(['cliente' => '']);

        $this->assertStringNotContainsStringIgnoringCase('LIKE', $where);
        $this->assertSame(4, $this->countLancamentosWhere($where));
    }

    public function testCoringasDoClienteSaoTratadasComoTexto(): void
    {
        // Sem o ESCAPE '!', um "%" no filtro trazeria todas as linhas.
        $where = $this->where(['cliente' => '100%']);

        $this->assertStringContainsString("ESCAPE '!'", $where);
        $this->assertSame(0, $this->countLancamentosWhere($where));
    }

    /**
     * Cada payload informa o total esperado de linhas.
     *
     * Há duas defesas diferentes: em `cliente` o valor é escapado e vira
     * literal, então casa com nada e o total é 0. Em `status` e `tipo` há
     * allowlist, então o payload é descartado antes do SQL e o filtro simplesmente
     * não é aplicado, o que devolve o período inteiro. O importante nos dois
     * casos é o mesmo: nenhum payload amplia o resultado.
     */
    #[DataProvider('payloadsDeInjecao')]
    public function testPayloadDeInjecaoNaoViraSqlExecutavel(string $campo, string $payload, int $esperado): void
    {
        $this->assertSame(
            $esperado,
            $this->countLancamentosWhere($this->where([$campo => $payload])),
            sprintf('O payload de %s não teve o efeito esperado.', $campo)
        );
    }

    public static function payloadsDeInjecao(): array
    {
        return [
            'cliente com OR sempre verdadeiro' => ['cliente', "x' OR '1'='1", 0],
            'cliente com tautologia numerica' => ['cliente', "x' OR 1=1 --", 0],
            'cliente com UNION' => ['cliente', "' UNION SELECT id, tipo, 1, data_vencimento FROM lancamentos --", 0],
            'cliente com quebra de aspas e comentario' => ['cliente', "'; DROP TABLE lancamentos; --", 0],
            'cliente com subquery' => ['cliente', "x' OR id IN (SELECT id FROM lancamentos) OR '1'='1", 0],
            'cliente com escape do proprio ESCAPE' => ['cliente', "x' OR 1=1 OR '!%'='!%", 0],
            'status com payload' => ['status', "1 OR '1'='1", 4],
            'status com payload numerico' => ['status', '1; DROP TABLE lancamentos', 4],
            'tipo com payload' => ['tipo', "'receita' OR '1'='1", 4],
        ];
    }

    public function testPayloadDeInjecaoNoStatusEDescartadoEmVezDeVirarClausula(): void
    {
        // Para status e tipo a proteção é allowlist, então o payload é
        // descartado antes do escape. O filtro não pode virar SQL.
        $whereStatus = $this->where(['status' => "1 OR '1'='1"]);
        $whereTipo = $this->where(['tipo' => "'receita' OR '1'='1"]);

        $this->assertStringNotContainsStringIgnoringCase('baixado', $whereStatus);
        $this->assertStringNotContainsStringIgnoringCase('tipo =', $whereTipo);
    }

    public function testPayloadDeInjecaoNoClienteFicaComoLiteralEscapado(): void
    {
        // A defesa de cliente é escape, então o payload precisa continuar
        // visível como texto entre aspas no SQL compilado.
        $where = $this->where(['cliente' => "x' OR '1'='1"]);

        $this->assertStringContainsString('OR', $where);
        $this->assertStringContainsString("ESCAPE '!'", $where);
        // As aspas internas ficam duplicadas, que é o que impede a saída do literal.
        $this->assertStringContainsString("''''", $where);
    }

    /**
     * Datas que o createFromFormat não consegue interpretar caem no dia de hoje,
     * em vez de gerar erro fatal ao chamar format() sobre o false devolvido.
     */
    #[DataProvider('datasInvalidas')]
    public function testDataInvalidaNaoQuebraAMontagem(string $data): void
    {
        $where = financeiroLancamentosWhere($this->db, [
            'vencimento_de' => $data,
            'vencimento_ate' => $data,
        ]);

        $hoje = (new DateTime())->format('Y-m-d');
        $this->assertStringContainsString("'" . $hoje . "'", $where);
    }

    public static function datasInvalidas(): array
    {
        return [
            'texto' => ['nao-e-data'],
            'formato americano' => ['2026-01-10'],
            'string vazia' => [''],
            'apenas o dia' => ['10'],
        ];
    }

    #[DataProvider('datasComOverflow')]
    public function testDataComOverflowSilenciosamenteUmaDataErrada(string $data, string $resultado): void
    {
        // knownIssue: createFromFormat não devolve false para datas com
        // estouro de campo; ele devolve um DateTime já normalizado. Então o
        // fallback para o dia de hoje nunca roda e uma data digitada errada
        // consulta a janela errada sem avisar: "32/13/2026" vira 2027-02-01 e
        // "31/02/2026" vira 2026-03-03. O filtro por período passa a listar o
        // conjunto errado de lançamentos em silêncio. A correção é validar com
        // DateTime::getLastErrors() (#2835).
        //
        // O teste fixa o comportamento atual. Quando for corrigido, deve passar a
        // esperar o dia de hoje e a KnownIssue sai daqui.
        $where = financeiroLancamentosWhere($this->db, [
            'vencimento_de' => $data,
            'vencimento_ate' => $data,
        ]);

        $this->assertStringContainsString("'" . $resultado . "'", $where);
        $this->assertStringNotContainsString("'" . (new DateTime())->format('Y-m-d') . "'", $where);
    }

    public static function datasComOverflow(): array
    {
        return [
            'dia e mes fora de faixa' => ['32/13/2026', '2027-02-01'],
            'dia inexistente no mes' => ['31/02/2026', '2026-03-03'],
        ];
    }

    public function testFiltroAusenteUsaODiaDeHoje(): void
    {
        $hoje = (new DateTime())->format('Y-m-d');
        $where = financeiroLancamentosWhere($this->db, []);

        $this->assertStringContainsString("data_vencimento >= '" . $hoje . "'", $where);
        $this->assertStringContainsString("data_vencimento <= '" . $hoje . "'", $where);
    }
}
