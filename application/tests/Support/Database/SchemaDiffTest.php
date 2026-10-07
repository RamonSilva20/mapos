<?php

namespace Tests\Support\Database;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
     * Uma coluna do schema, com o default que o gate vai comparar.
     *
     * Existe porque a forma do schema mudou de `coluna => string` para
     * `coluna => atributos`, e escrever os seis atributos em cada caso sintético
     * enterraria o teste na forma do dado. O que importa em quase todo teste é o
     * `type`, e é o que os casos abaixo mexem.
     *
     * @param  array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private static function column(string $type = 'int', array $overrides = []): array
    {
        return $overrides + [
            'type' => $type,
            'default' => null,
            'charset' => null,
            'collation' => null,
            'nullable' => false,
            'extra' => '',
        ];
    }

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
            'clientes' => ['idClientes' => self::column('int'), 'nome' => self::column('varchar(255)')],
            'os' => ['idOs' => self::column('int'), 'valor' => self::column('decimal(10,2)')],
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
        $dump = ['clientes' => ['idClientes' => self::column()], 'so_no_dump' => ['id' => self::column()]];
        $migrations = ['clientes' => ['idClientes' => self::column()], 'so_nas_migrations' => ['id' => self::column()]];

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
        $dump = ['os' => ['idOs' => self::column(), 'numero' => self::column(), 'despejo' => self::column()]];
        $migrations = ['os' => ['idOs' => self::column(), 'numero' => self::column()]];

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
        $dump = ['os' => ['idOs' => self::column()]];
        $migrations = ['clientes' => ['idClientes' => self::column()]];

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
        $dump = ['os' => ['valor' => self::column('decimal(10,0)')]];
        $migrations = ['os' => ['valor' => self::column('decimal(12,2)')]];

        $this->assertSame(
            ['type      divergente: os.valor                 banco.sql=decimal(10,0)      migracoes=decimal(12,2)'],
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
                ['os' => ['valor' => self::column('VARCHAR(255)')]],
                ['os' => ['valor' => self::column('varchar(255)')]]
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
        $dump = ['a' => ['x' => self::column()], 'b' => ['y' => self::column()]];
        $migrations = ['b' => ['y' => self::column('varchar(4)')], 'a' => ['x' => self::column()]];

        $sameContentOtherOrder = [
            'a' => ['x' => self::column()],
            'b' => ['y' => self::column()],
        ];
        $sameContentOtherOrderMigrations = [
            'a' => ['x' => self::column()],
            'b' => ['y' => self::column('varchar(4)')],
        ];

        $expected = [
            'type      divergente: b.y                      banco.sql=int                migracoes=varchar(4)',
        ];

        $this->assertSame($expected, schemaDiffBetween($dump, $migrations));
        $this->assertSame(
            $expected,
            schemaDiffBetween($sameContentOtherOrder, $sameContentOtherOrderMigrations)
        );
    }

    /**
     * Um DEFAULT de um lado e ausente do outro é uma divergência nomeada.
     *
     * Este é o defeito que o gate não via e que a migration 20261005131000 removeu
     * de `usuarios.cep`: um `DEFAULT '70005-115'` que existia em banco.sql e não
     * existia na cadeia. O tipo era o mesmo nos dois lados, então o gate dizia
     * que o schema estava em paridade com um default Having Date leaking de um
     * endereço de desenvolvimento para dentro de toda linha nova.
     *
     * E o inverso também diverge, que é a direção mais difícil de enxergar: uma
     * coluna com `DEFAULT ''` de um lado e sem DEFAULT do outro se comporta igual
     * quando alguém passa o valor, e diferente quando ninguém passa.
     */
    #[Test]
    public function testADivergentDefaultIsReportedInBothDirections(): void
    {
        $withDefault = ['usuarios' => ['cep' => self::column('varchar(9)', ['default' => '70005-115'])]];
        $withoutDefault = ['usuarios' => ['cep' => self::column('varchar(9)', ['default' => null])]];

        $this->assertSame(
            ['default   divergente: usuarios.cep             banco.sql=70005-115          migracoes=NULL'],
            schemaDiffBetween($withDefault, $withoutDefault)
        );

        $this->assertSame(
            ['default   divergente: usuarios.cep             banco.sql=NULL               migracoes=70005-115'],
            schemaDiffBetween($withoutDefault, $withDefault)
        );
    }

    /**
     * `DEFAULT ''` e coluna sem DEFAULT são duas coisas diferentes.
     *
     * Se o gate tratasse a string vazia como "não há valor", estas duas colunas
     * seriam iguais e o teste passaria sem exercitar nada. É o mesmo cuidado que
     * `SchemaReader::columnDefault()` documenta do outro lado da leitura: quem
     * normaliza a ausência para a string vazia perde a diferença que o MySQL
     * distingue entre SQL NULL e ''.
     */
    #[Test]
    public function testAnEmptyStringDefaultIsNotTreatedAsAnAbsentDefault(): void
    {
        $emptyDefault = ['usuarios' => ['cep' => self::column('varchar(9)', ['default' => ''])]];
        $noDefault = ['usuarios' => ['cep' => self::column('varchar(9)', ['default' => null])]];

        $this->assertSame(
            ['default   divergente: usuarios.cep             banco.sql=\'\'                 migracoes=NULL'],
            schemaDiffBetween($emptyDefault, $noDefault)
        );
    }

    /**
     * Um charset ou collation de um lado e de outro é uma divergência nomeada.
     *
     * `latin1` e `utf8mb4` produzem o mesmo tipo e as mesmas linhas, e a diferença
     * só aparece quando alguém grava um caractere de 4 bytes. Um gate que olhasse só
     * o tipo declarava em paridade um banco que não sabe guardar um emoji.
     */
    #[Test]
    public function testADivergentCharsetAndCollationAreReportedByName(): void
    {
        $dump = ['os' => ['descricao' => self::column('mediumtext', [
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
        ])]];
        $migrations = ['os' => ['descricao' => self::column('mediumtext', [
            'charset' => 'latin1',
            'collation' => 'latin1_swedish_ci',
        ])]];

        $this->assertSame(
            [
                'charset   divergente: os.descricao             banco.sql=utf8mb4            migracoes=latin1',
                'collation divergente: os.descricao             banco.sql=utf8mb4_general_ci migracoes=latin1_swedish_ci',
            ],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * Uma coluna que só um lado aceita nulo é uma divergência nomeada.
     *
     * O relatório escreve SIM e NAO, e não true e false nem 1 e 0, porque quem lê
     * a saída do CI está olhando uma frase, não um valor serializado.
     */
    #[Test]
    public function testADivergentNullabilityIsReportedAsYesOrNo(): void
    {
        $dump = ['os' => ['numero' => self::column('int', ['nullable' => true])]];
        $migrations = ['os' => ['numero' => self::column('int', ['nullable' => false])]];

        $this->assertSame(
            ['nullable  divergente: os.numero                banco.sql=SIM                migracoes=NAO'],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * Um `AUTO_INCREMENT` que um lado perdeu é uma divergência nomeada.
     *
     * `os.idOs` sem `AUTO_INCREMENT` é um esquema que não insere linha nenhuma, e
     * o tipo continuaria `int` dos dois lados — que é o motivo de `extra` estar na
     * lista de atributos comparados.
     */
    #[Test]
    public function testADivergentExtraIsReportedByName(): void
    {
        $dump = ['os' => ['idOs' => self::column('int')]];
        $migrations = ['os' => ['idOs' => self::column('int', ['extra' => 'auto_increment'])]];

        $this->assertSame(
            ["extra     divergente: os.idOs                  banco.sql=''                 migracoes=auto_increment"],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * Duas colunas com vários atributos divergentes geram uma linha cada.
     *
     * Uma linha por coluna, com tudo junto, obrigaria quem lê a abrir os dois
     * lados para saber qual dos atributos divergia. Nomear o atributo é o que faz a
     * linha apontar para a migration, e é por isso que o laço é por atributo.
     */
    #[Test]
    public function testEachDivergentAttributeProducesItsOwnLine(): void
    {
        $dump = ['os' => ['idOs' => self::column()]];
        $migrations = ['os' => ['idOs' => self::column('int', [
            'extra' => 'auto_increment',
            'nullable' => true,
        ])]];

        $this->assertSame(
            [
                'nullable  divergente: os.idOs                  banco.sql=NAO                migracoes=SIM',
                'extra     divergente: os.idOs                  banco.sql=\'\'                 migracoes=auto_increment',
            ],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * O `default` é a comparação que NÃO ignora a caixa.
     *
     * `type`, `charset`, `collation` e `extra` são case-insensitive no MySQL, e o
     * gate os compara assim de propósito. O `default` é dado de aplicação: um
     * `DEFAULT 'ADMIN'` grava ADMIN a cada linha nova e um `DEFAULT 'admin'` grava
     * admin, e um perfil com o tipo trocado é um defeito que um gate case-insensitive
     * deixaria passar como "em paridade".
     */
    #[Test]
    public function testTheDefaultComparisonIsCaseSensitive(): void
    {
        $dump = ['usuarios' => ['tipo' => self::column('varchar(16)', ['default' => 'ADMIN'])]];
        $migrations = ['usuarios' => ['tipo' => self::column('varchar(16)', ['default' => 'admin'])]];

        $this->assertSame(
            ['default   divergente: usuarios.tipo            banco.sql=ADMIN              migracoes=admin'],
            schemaDiffBetween($dump, $migrations)
        );
    }

    /**
     * O `collation` continua case-insensitive mesmo com o resto estrito.
     *
     * O par acima troca só a caixa do valor: se o `===` da comparação de default
     * vazasse para os outros atributos, este par divergiria e o gate acusaria um
     * schema que o MySQL lê como idêntico.
     */
    #[Test]
    public function testCollationAndCharsetComparisonStillIgnoreCaseAfterTheStrictDefault(): void
    {
        $schema = ['os' => ['descricao' => self::column('mediumtext', [
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
        ])]];
        $sameValuesOtherCase = ['os' => ['descricao' => self::column('mediumtext', [
            'charset' => 'UTF8MB4',
            'collation' => 'UTF8MB4_GENERAL_CI',
        ])]];

        $this->assertSame([], schemaDiffBetween($schema, $sameValuesOtherCase));
    }

    /**
     * O `extra` continua case-insensitive: AUTO_INCREMENT e auto_increment são o mesmo.
     *
     * Este é o atributo que o stricton do caso acima NÃO cobre — o tipo inteiro não
     * está no caminho case-insensitive da mesma forma que charset. O MySQL devolve
     * as letras do jeito que o usuário escreveu o DDL, e o dump não tem por que
     * grafar igual à migration.
     */
    #[Test]
    public function testExtraComparisonStillIgnoresCase(): void
    {
        $this->assertSame(
            [],
            schemaDiffBetween(
                ['os' => ['idOs' => self::column('int', ['extra' => 'AUTO_INCREMENT'])]],
                ['os' => ['idOs' => self::column('int', ['extra' => 'auto_increment'])]]
            )
        );
    }

    /**
     * Um atributo que um dos lados não sabe descrever é defeito, e não divergência.
     *
     * Garantir a forma dos dois lados é o que deixa a comparação honesta: repetir o
     * valor que o lado não tem — `$dump ?? ''` — transforma "o leitor não trouxe o
     * atributo" em "os dois têm o atributo e um é vazio", que é a divergência que o
     * nosso schema de verdade pode ter de verdade. A exceção nomeia a coluna e o
     * atributo para o erro apontar direto para o leitor que mudou.
     */
    #[Test]
    public function testASchemaLackingAComparedAttributeThrowsNamingIt(): void
    {
        $withoutExtra = ['os' => ['idOs' => self::column()]];
        unset($withoutExtra['os']['idOs']['extra']);
        $complete = ['os' => ['idOs' => self::column('int')]];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("atributo 'extra' ausente de os.idOs");

        schemaDiffBetween($complete, $withoutExtra);
    }

    /**
     * Collations de tabela iguais não produzem diferença.
     *
     * Este é o caso base do irmão novo do gate, e ele existe porque o par de
     * schemas de verdade já está em paridade — uma regressão que comparasse contrário
     * apareceria como "tabelas divergentes" sem ninguém ter mexido em collation.
     */
    #[Test]
    public function testIdenticalTableCollationsProduceNoDifference(): void
    {
        $collations = ['os' => 'utf8mb4_general_ci', 'itens_de_vendas' => 'utf8mb4_general_ci'];

        $this->assertSame([], schemaTableDiffBetween($collations, $collations));
    }

    /**
     * A tabela que só um dos lados tem não é relatada aqui.
     *
     * A presença de tabela é a meia dúzia de linhas "tabela só no banco.sql" /
     * "só nas migrations" da irmã, e contar a mesma diferença duas vezes — uma por
     * tabela, uma por collation — faria o relatório dizer "7 tabelas divergentes"
     * para um problema de 4 linhas.
     */
    #[Test]
    public function testATableOnlyOnOneSideIsNotReportedAsACollationDivergence(): void
    {
        $this->assertSame(
            [],
            schemaTableDiffBetween(
                ['solo_no_dump' => 'utf8mb4_general_ci'],
                ['solo_nas_migrations' => 'utf8mb4_general_ci']
            )
        );
    }

    /**
     * A collation de tabela divergente é relatada pelo nome, dos dois lados.
     *
     * `itens_de_vendas` é a tabela que este relatório existe para pegar: só colunas
     * numéricas, nenhum CHARACTER_SET_NAME para a irmã comparar, e a collation dela
     * só aparece no nível da tabela. Um banco.sql que a tivesse em utf8mb3 passaria
     * no gate antigo e gravaria o VARCHAR normalizado de um jeito e o número de
     * outro — na verdade guardaria errado o total de uma venda.
     */
    #[Test]
    public function testADivergentTableCollationIsReportedByTableName(): void
    {
        $this->assertSame(
            [
                'collation divergente: itens_de_vendas          banco.sql=utf8mb3_general_ci migracoes=utf8mb4_general_ci',
            ],
            schemaTableDiffBetween(
                ['itens_de_vendas' => 'utf8mb3_general_ci'],
                ['itens_de_vendas' => 'utf8mb4_general_ci']
            )
        );
    }
}
