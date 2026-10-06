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
     * resets_de_senha.token_utilizado tem DEFAULT 0.
     *
     * A coluna é NOT NULL e o fluxo de recuperação de senha nunca escreve nela:
     * o INSERT da 20220307173741 manda só email, token e data_expiracao. Ela
     * sobreviveu sem DEFAULT porque a aplicação desliga o modo estrito —
     * database.php traz 'stricton' => false, e o mysqli_driver remove
     * STRICT_TRANS_TABLES e STRICT_ALL_TABLES do sql_mode da sessão — e sem
     * esses dois o MySQL preenche a coluna com o zero implícito. É o mesmo
     * caminho que faz `datetime` responder '0000-00-00 00:00:00'.
     *
     * Então nada quebra hoje, e é exatamente por isso que o DEFAULT merece uma
     * afirmação: a dependência é invisível. Quem liga 'stricton' => true, ou quem
     * roda a suíte contra um servidor que não deixa a sessão ser ajustada, recebe
     * 1364 Field 'token_utilizado' doesn't have a default value no meio do
     * recuperação de senha — que é justamente o caminho que um usuário visitou
     * para testar o produto.
     *
     * A migration 20261005120000 é a que instala o DEFAULT. A prova de que ela
     * continua de pé é o DEFAULT existir, e não o INSERT succeeding: sem ele o
     * MineControllerTest passaria igual, porque ele também roda com o modo
     * estrito desligado.
     */
    public function testResetTokenUsedColumnKeepsItsDefault(): void
    {
        $this->assertSame(
            '0',
            $this->columnDefault('resets_de_senha', 'token_utilizado'),
            'resets_de_senha.token_utilizado perdeu o DEFAULT 0 e voltou a depender do MySQL em modo não estrito.'
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

    /**
     * O DEFAULT de uma coluna, lido pelo mesmo caminho que o resto da suíte.
     *
     * Deliberadamente não passa por assertNotNull() como columnType(): a ausência
     * de DEFAULT é um valor legítimo que o teste precisa ver, e não um erro de
     * leitura. Uma coluna que sumiu do information_schema também responderia
     * null, e a diferença interessa — a primeira é o DEFAULT perdido, a segunda
     * é a coluna perdida. Por isso o método afirma o nome da coluna junto.
     */
    private function columnDefault(string $table, string $column): ?string
    {
        $test = TestDatabase::fromEnvironment();

        $exists = SchemaReader::columnType($test->pdo(), $test->database(), $table, $column);

        $this->assertNotNull(
            $exists,
            "A tabela {$table} não tem a coluna {$column}, ou ela não aparece no information_schema."
        );

        return SchemaReader::columnDefault($test->pdo(), $test->database(), $table, $column);
    }
}
