<?php

namespace Tests\Schema;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\Database\SchemaReader;
use Tests\Support\Database\TestDatabase;

/**
 * Afirma propriedades do schema que a suíte monta.
 *
 * O banco de teste é construído pela cadeia de migrações, então o estado dele é
 * o estado de produção. Isto é diferente do check-schema-parity.php: aquele
 * compara banco.sql com as migrações, e passa se as duas pontas divergirem
 * juntas. Aqui a afirmação é sobre um valor absoluto, então nenhum par de
 * edições convergentes consegue Mascará-lo.
 */
final class SchemaTest extends TestCase
{
    /**
     * As colunas de dinheiro têm duas casas decimais.
     *
     * O create_base declarava estas quatro assim:
     *
     *     'constraint' => 10, 2,
     *
     * que o PHP lê como 'constraint' => 10 — o 2 vira elemento posicional e é
     * descartado. O dbforge só usa o CONSTRAINT, então saía DECIMAL(10), e o
     * MySQL trata isso como DECIMAL(10,0): dinheiro sem centavos.
     *
     * Quem rodou a migration entre 2012 e a correção ficou com as quatro colunas
     * assim, porque nenhuma migration posterior as repara — a 20220320173741 toca
     * lancamentos, os, vendas, cobrancas, produtos_os, servicos_os e
     * itens_de_vendas. A 20260927111019 é a que repara.
     *
     * produtos.precoVenda e produtos.precoCompra importam porque
     * Relatorios_model::produtosCustom() faz
     * SUM(produtos.estoque * produtos.precoVenda) e filtra por
     * precoVenda BETWEEN. A avaliação de estoque era arredondada para reais.
     *
     * @param  string  $table
     * @param  string  $column
     */
    #[DataProvider('moneyColumns')]
    public function testMoneyColumnsKeepTheirDecimals(string $table, string $column): void
    {
        $this->assertSame(
            'decimal(10,2)',
            $this->columnType($table, $column),
            "{$table}.{$column} perdeu as casas decimais."
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function moneyColumns(): iterable
    {
        yield 'contas.saldo' => ['contas', 'saldo'];
        yield 'produtos.precoCompra' => ['produtos', 'precoCompra'];
        yield 'produtos.precoVenda' => ['produtos', 'precoVenda'];
        yield 'servicos.preco' => ['servicos', 'preco'];
    }

    /**
     * O tipo declarado de uma coluna, lido pelo mesmo caminho que o resto da suíte.
     *
     * Passa pela SchemaReader em vez da própria consulta a information_schema
     * porque este arquivo afirma um valor absoluto: uma segunda leitura do schema
     * seria uma segunda resposta para a mesma pergunta, e as duas poderiam divergir
     * sem que nenhuma delas estivesse errada. `strtolower()` é do teste, e não da
     * leitora, porque a normalização pertence a quem compara.
     */
    private function columnType(string $table, string $column): string
    {
        $test = TestDatabase::fromEnvironment();

        $type = SchemaReader::columnType($test->pdo(), $test->database(), $table, $column);

        $this->assertNotNull(
            $type,
            "A tabela {$table} não tem a coluna {$column}, ou ela não aparece no information_schema."
        );

        return strtolower($type);
    }
}
