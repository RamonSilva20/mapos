<?php

namespace Tests\Support\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A comparação que decide se banco.sql e as migrations divergem.
 *
 * Sem trait de transação e sem banco: `schemaDiffBetween()` é pura, recebe dois
 * arrays e devolve uma lista de linhas. É a única parte do gate de paridade que
 * decide se há divergência, e foi a única que nunca teve teste — porque era uma
 * closure no corpo de um script procedural, impossível de exercitar sem montar um
 * banco dos dois lados.
 *
 * Os casos aqui são sintéticos e pequenos de propósito: a falha que esta função
 * já cometeu foi de assimetria, não de schema. Com banco.sql e as migrations de
 * verdade, um laço que só percorre um lado passa o tempo todo sem aparecer.
 *
 * A função mora em `bin/lib/schema-diff.php` e é carregada por este `require_once`,
 * que é a contrapartida de ela ser função: o autoloader de classes não acha
 * função, e o require fica num lugar só, o lugar que a exercita.
 */
require_once dirname(__DIR__, 2) . '/bin/lib/schema-diff.php';

final class SchemaDiffTest extends TestCase
{
    /**
     * Schemas idênticos não produzem diferença nenhuma.
     *
     * Este é o caso que o gate de paridade exercita em toda execução do CI, e é
     * por isso que uma regressão aqui não aparece sozinha: ela aparece como
     * "27 tabelas conferidas" num schema que não bate mais.
     */
    #[Test]
    public function testIdenticalSchemasProduceNoDifference(): void
    {
        $schema = [
            'clientes' => ['idClientes' => 'int', 'nome' => 'varchar(255)'],
            'os' => ['idOs' => 'int', 'valor' => 'decimal(10,2)'],
        ];

        $this->assertSame([], schemaDiffBetween($schema, $schema));
    }

    /**
     * Uma tabela que só existe de um dos lados é nomeada pelo lado que a tem.
     *
     * Os dois sentidos, porque a versão anterior da função comparava a união das
     * chaves e reportava a tabela ausente pelo nome do lado que não a tinha — o
     * que produzia "tabela só nas migrações" para uma tabela que estava no
     * banco.sql, invertendo a acusação.
     */
    #[Test]
    public function testATableOnlyOnOneSideIsReportedByThatSide(): void
    {
        $dump = ['clientes' => ['idClientes' => 'int'], 'so_no_dump' => ['id' => 'int']];
        $migrations = ['clientes' => ['idClientes' => 'int'], 'so_nas_migrations' => ['id' => 'int']];

        $this->assertSame(
            [
                'tabela só no banco.sql: so_no_dump',
                'tabela só nas migrações: so_nas_migrations',
            ],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * Uma coluna removida de um dos lados aparece, qualquer que seja o lado.
     *
     * Este é o defeito real que a função já teve, e o teste existe para o
     * próximo laço assimétrico não voltar. A versão anterior eram dois laços
     * quase idênticos, e o segundo não tinha o laço interno de colunas: uma coluna
     * removida aparecia num caminho e ficava invisível no outro, dependendo de qual
     * schema fosse percorrido por último. Um teste que exercitasse só este par,
     * nesta ordem, passaria com os dois laços.
     *
     * Por isso os dois sentidos e a ordem invertida: a mesma coluna removida, com
     * os mapas trocados de posição, tem de continuar sendo reportada.
     */
    #[Test]
    public function testAColumnRemovedOnEitherSideIsReportedInBothDirections(): void
    {
        $dump = ['os' => ['idOs' => 'int', 'numero' => 'int', 'despejo' => 'int']];
        $migrations = ['os' => ['idOs' => 'int', 'numero' => 'int']];

        $this->assertSame(
            ['coluna só no banco.sql: os.despejo int'],
            schemaDiffBetween($dump, $migrations),
            'coluna que o banco.sql tem e a migration não'
        );

        $this->assertSame(
            ['coluna só nas migrações: os.despejo int'],
            schemaDiffBetween($migrations, $dump),
            'a mesma coluna, com os lados trocados'
        );
    }

    /**
     * A tabela precisa existir dos dois lados para a coluna ser comparada.
     *
     * Este caso é o que separa "coluna divergente" de "tabela divergente": uma
     * coluna que existe num lado de uma tabela ausente no outro não é uma coluna
     * a mais, é uma tabela a mais, e reportar as duas coisas seria contar a mesma
     * diferença duas vezes.
     */
    #[Test]
    public function testAColumnIsNotReportedWhenTheWholeTableIsMissingOnTheOtherSide(): void
    {
        $dump = ['os' => ['idOs' => 'int']];
        $migrations = ['clientes' => ['idClientes' => 'int']];

        $this->assertSame(
            [
                'tabela só no banco.sql: os',
                'tabela só nas migrações: clientes',
            ],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * Tipo diferente na mesma coluna nomeia os dois lados e o valor de cada um.
     *
     * A mensagem é o produto desta função: é ela que o CI imprime quando o gate
     * falha, e quem lê é alguém procurando qual migration corrigir. Um relatório
     * que dissesse só "diferença em os.valor" obrigaria a abrir os dois lados
     * na mão.
     */
    #[Test]
    public function testADivergentTypeNamesBothSidesAndBothValues(): void
    {
        $dump = ['os' => ['valor' => 'decimal(10,0)']];
        $migrations = ['os' => ['valor' => 'decimal(12,2)']];

        $this->assertSame(
            ['tipo divergente: os.valor                 banco.sql=decimal(10,0)      migracoes=decimal(12,2)'],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * A comparação de tipo não distingue maiúscula de minúscula.
     *
     * `SHOW COLUMNS` e `information_schema.columns` grafam o mesmo tipo do mesmo
     * jeito, mas o gate já comparava com strcasecmp e continua comparando: mudar
     * isso aqui viraria um falso "divergente" por causa de caixa, que é o tipo de
     * falha que faz alguém caçar um bug de schema que não existe.
     */
    #[Test]
    public function testTypeComparisonIgnoresCase(): void
    {
        $this->assertSame(
            [],
            schemaDiffBetween(
                ['os' => ['valor' => 'VARCHAR(255)']],
                ['os' => ['valor' => 'varchar(255)']]
            )
        );
    }

    /**
     * A ordem em que as tabelas entraram no mapa não muda o resultado.
     *
     * Os dois mapas abaixo têm o mesmo conteúdo e as mesmas chaves em ordens
     * inversas. A diferença tem de ser a mesma nos dois casos, senão o gate
     * reportaria uma divergência que depende de como o MySQL devolveu as linhas.
     *
     * O que NÃO é invariante é trocar os argumentos: `schemaDiffBetween($dump, $migrations)`
     * nomeia `banco.sql` como o lado do primeiro argumento, e inverter os dois
     * inverte os rótulos. Isso é o comportamento certo — a mensagem existe para
     * dizer qual lado tem o quê, e um rótulo que não acompanhasse o argumento
     * apontaria o developer para a migration quando o problema está no banco.sql.
     */
    #[Test]
    public function testTheKeyInsertionOrderDoesNotChangeTheResult(): void
    {
        $dump = ['a' => ['x' => 'int'], 'b' => ['y' => 'int']];
        $migrations = ['b' => ['y' => 'varchar(4)'], 'a' => ['x' => 'int']];

        $sameContentOtherOrder = [
            'a' => ['x' => 'int'],
            'b' => ['y' => 'int'],
        ];
        $sameContentOtherOrderMigrations = [
            'a' => ['x' => 'int'],
            'b' => ['y' => 'varchar(4)'],
        ];

        $expected = [
            'tipo divergente: b.y                      banco.sql=int                migracoes=varchar(4)',
        ];

        $this->assertSame($expected, schemaDiffBetween($dump, $migrations));
        $this->assertSame(
            $expected,
            schemaDiffBetween($sameContentOtherOrder, $sameContentOtherOrderMigrations)
        );
    }
}
