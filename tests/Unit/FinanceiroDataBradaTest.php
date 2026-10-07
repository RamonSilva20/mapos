<?php

use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Conversão das datas do formulário do financeiro.
 *
 * As telas mandam dd/mm/aaaa e as colunas são date. A conversão ficava
 * inline no controller com explode() e índice de array: um campo vazio não
 * gerava exceção, só warning, e a data acabava gravada como a string '-'.
 */
final class FinanceiroDataBradaTest extends MaposTestCase
{
    #[DataProvider('datasValidas')]
    public function testConverteDataValida($entrada, string $esperado): void
    {
        $this->assertSame($esperado, financeiroDataBrada($entrada));
    }

    public static function datasValidas(): array
    {
        return [
            'data futura' => ['15/03/2027', '2027-03-15'],
            'data passada' => ['15/03/2020', '2020-03-15'],
            'dia com zero' => ['07/01/2027', '2027-01-07'],
            'ultimo dia do mes' => ['31/12/2027', '2027-12-31'],
            'bissexto existe' => ['29/02/2028', '2028-02-29'],
        ];
    }

    /**
     * Data que o createFromFormat normaliza em vez de recusar.
     *
     * Sem a conferência do getLastErrors, 31/02 viraria 2027-03-03 e o
     * lançamento seria gravado numa data que ninguém digitou.
     */
    #[DataProvider('datasComEstouro')]
    public function testDataComEstouroCaiParaHoje($entrada): void
    {
        $this->assertSame(
            (new DateTime())->format('Y-m-d'),
            financeiroDataBrada($entrada),
            'Uma data impossível não pode ser normalizada em outra data.'
        );
    }

    public static function datasComEstouro(): array
    {
        return [
            'dia inexistente no mes' => ['31/02/2027'],
            'dia fora de faixa' => ['32/01/2027'],
            'mes fora de faixa' => ['07/13/2027'],
            'dia e mes fora de faixa' => ['32/13/2027'],
            'bissexto em ano nao bissexto' => ['29/02/2027'],
        ];
    }

    #[DataProvider('entradasNaoData')]
    public function testEntradaNaoDataCaiParaHoje($entrada): void
    {
        $this->assertSame((new DateTime())->format('Y-m-d'), financeiroDataBrada($entrada));
    }

    public static function entradasNaoData(): array
    {
        return [
            'string vazia' => [''],
            'null' => [null],
            'espacos' => ['   '],
            'texto' => ['nao-e-data'],
            'formato iso' => ['2026-10-07'],
        ];
    }

    /**
     * Sem fallback, o valor não preenchido tem que virar string vazia e não a
     * data de hoje.
     *
     * É o caso do campo de recebimento, que é opcional: gravar a data de hoje
     * num recebimento que ninguém informou seria inventar dado.
     */
    #[DataProvider('entradasParaValorOpcional')]
    public function testSemFallbackDevolveVazio($entrada): void
    {
        $this->assertSame('', financeiroDataBrada($entrada, false));
    }

    public static function entradasParaValorOpcional(): array
    {
        return [
            'string vazia' => [''],
            'null' => [null],
            'espacos' => ['   '],
            'texto' => ['nao-e-data'],
            'overflow' => ['31/02/2027'],
        ];
    }

    /**
     * Com o fallback desligado, uma data válida continua sendo convertida.
     */
    public function testSemFallbackAindaConverteDataValida(): void
    {
        $this->assertSame('2027-03-15', financeiroDataBrada('15/03/2027', false));
    }

    /**
     * O resultado é sempre no formato aceito por uma coluna date, o que
     * garante que nada como '-' chegue ao INSERT.
     */
    #[DataProvider('todasAsEntradas')]
    public function testResultadoSempreNoFormatoDeData($entrada, bool $fallback): void
    {
        $resultado = financeiroDataBrada($entrada, $fallback);

        if ($resultado === '') {
            $this->assertSame('', $resultado);

            return;
        }

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            $resultado,
            'A conversão precisa devolver aaaa-mm-dd ou string vazia, nada mais.'
        );
    }

    public static function todasAsEntradas(): iterable
    {
        foreach (self::datasValidas() as $rotulo => $caso) {
            yield 'valida/' . $rotulo => [$caso[0], true];
        }

        foreach (self::datasComEstouro() as $rotulo => $caso) {
            yield 'estouro/' . $rotulo => [$caso[0], true];
        }

        foreach (self::entradasNaoData() as $rotulo => $caso) {
            yield 'nao-data/' . $rotulo => [$caso[0], true];
            yield 'nao-data-sem-fallback/' . $rotulo => [$caso[0], false];
        }
    }
}
