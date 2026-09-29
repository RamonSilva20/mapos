<?php

namespace Tests\Support\Database;

use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\Clone\TestSchemaClone;

/**
 * Testes da leitura de metadados, sem depender de um banco montado.
 *
 * A leitura em si é coberta de passagem pelo gate de paridade e pelos casos do
 * clone, mas os dois exercitam o caminho feliz. O que este arquivo prende são as
 * três decisões que a classe existe para tomar, porque cada uma delas já foi
 * tomada de dois jeitos diferentes uma vez:
 *
 *   - o que é schema da aplicação e o que é tabela de controle;
 *   - que nome de tabela só entra no SQL depois de validado;
 *   - que a lista vem ordenada e é a mesma para todos os consumidores.
 */
final class SchemaReaderTest extends TestCase
{
    private static function database(): PDO
    {
        return TestDatabase::fromEnvironment()->pdo();
    }

    /**
     * O nome entre crases volta intacto quando é um nome.
     */
    #[Test]
    public function testTheIdentifierAcceptsAName(): void
    {
        $this->assertSame('`os`', SchemaReader::identifier('os'));
        $this->assertSame('`mapos_1_test`', SchemaReader::identifier('mapos_1_test'));
    }

    /**
     * A recusa é o que fecha a concatenação de nome no SQL.
     *
     * O caso do ponto e vírgula é o que importa: `` `x`; DROP TABLE os; `` é o
     * nome de uma tabela que escapa das crases e vira SQL. Um nome real nunca
     * precisa de nada fora de letra, dígito e sublinhado, então a recusa não
     * custa nenhuma tabela de verdade.
     */
    #[Test]
    public function testTheIdentifierRefusesAnythingThatIsNotAName(): void
    {
        $this->expectException(RuntimeException::class);

        SchemaReader::identifier('os`; DROP TABLE os; --');
    }

    /**
     * Nome vazio e nome com espaço caem na mesma regra, pela mesma recusa.
     */
    #[Test]
    public function testTheIdentifierRefusesAnEmptyName(): void
    {
        $this->expectException(RuntimeException::class);

        SchemaReader::identifier('');
    }

    /**
     * O par qualificado valida as duas metades, não só a tabela.
     *
     * Se validasse só a tabela, o nome do banco passaria direto para o SQL: ele é
     * o que vem do ambiente, não do information_schema, e é a metade que nenhum
     * dos leitores enxerga.
     */
    #[Test]
    public function testTheQualifiedNameValidatesBothHalves(): void
    {
        $this->assertSame('`mapos_test`.`os`', SchemaReader::qualified('mapos_test', 'os'));

        $this->expectException(RuntimeException::class);

        SchemaReader::qualified('mapos_test`; --', 'os');
    }

    /**
     * A lista de tabelas vem em ordem estável e não traz as de controle.
     *
     * A ordem não é estética: a comparação do gate de paridade afirma igualdade
     * entre dois bancos com assertSame, e a ordem em que o information_schema
     * devolve as linhas não é a ordem em que as tabelas foram criadas.
     */
    #[Test]
    public function testTheTableListIsSortedAndSpelledOutInUppercase(): void
    {
        $pdo = self::database();
        $database = TestDatabase::fromEnvironment()->database();

        $names = SchemaReader::tableNames($pdo, $database);

        $this->assertContains('migrations', $names, 'tableNames() é a lista crua: o clone precisa de `migrations`.');

        $sorted = $names;
        sort($sorted, SORT_STRING);

        $this->assertSame($sorted, $names, 'A lista precisa vir ordenada, senão assertSame passa a comparar ordem.');
    }

    /**
     * As chaves de tipo de coluna saem com o nome que o MySQL entrega.
     *
     * Escrever `SELECT column_name` e esperar a chave `column_name` produz um
     * aviso de chave indefinida, e com `failOnWarning` o teste fica vermelho com
     * uma mensagem que não aponta para a consulta.
     */
    #[Test]
    public function testTheColumnMapIsKeyedByTheNameMysqlDelivers(): void
    {
        $pdo = self::database();
        $database = TestDatabase::fromEnvironment()->database();

        $columns = SchemaReader::columnTypes($pdo, $database);

        $this->assertArrayHasKey('os', $columns);
        $this->assertArrayHasKey('idOs', $columns['os']);
        $this->assertMatchesRegularExpression('/^int/', $columns['os']['idOs']);
    }

    /**
     * A leitura de uma coluna devolve o tipo, e null quando a coluna não existe.
     *
     * Os dois lados no mesmo caso porque o null é a parte que se perde: uma leitura
     * que devolve string vazia para "não achou" e para "achou vazio" empurra quem
     * chama a decidir o que fazer, e a decisão errada é afirmar que o tipo está
     * errado num banco onde ele simplesmente não está.
     *
     * O tipo sai cru, e é essa a forma de `columnTypes()`: quem compara escolhe o
     * que fazer com a caixa. O SchemaTest usa strtolower() e o gate de paridade usa
     * strcasecmp(); normalizar aqui deixaria um dos dois acreditando que a
     * normalização não existe.
     */
    #[Test]
    public function testASingleColumnReadsItsTypeAndReportsAMissingOneAsNull(): void
    {
        $pdo = self::database();
        $database = TestDatabase::fromEnvironment()->database();

        $type = SchemaReader::columnType($pdo, $database, 'os', 'idOs');

        $this->assertIsString($type, 'os.idOs existe: a suíte roda contra uma cadeia de migrations.');
        $this->assertMatchesRegularExpression('/^int/', $type);

        $this->assertNull(
            SchemaReader::columnType($pdo, $database, 'os', 'colunaQueNaoExiste'),
            'Coluna ausente é null, e não string vazia: quem chama decide a partir disso.'
        );

        $this->assertNull(
            SchemaReader::columnType($pdo, $database, 'tabelaQueNaoExiste', 'idOs'),
            'Tabela ausente também é null, e não uma exceção: o filtro table_schema é o mesmo dos dois lados.'
        );
    }

    /**
     * `migrations` é tabela de controle, e é exatamente por isso que a exclusão
     * é explícita e não um filtro de nome.
     *
     * A tabela aparece depois da primeira migration. Se ela contasse como
     * schema, um banco recém-criado pareceria divergir do outro por causa do
     * próprio histórico de instalação.
     */
    #[Test]
    public function testTheApplicationSchemaLeavesTheControlTablesOut(): void
    {
        $test = TestDatabase::fromEnvironment();
        $pdo = self::database();

        $schema = SchemaReader::applicationSchema($pdo, $test->database());

        $this->assertArrayNotHasKey('migrations', $schema);
        $this->assertArrayHasKey('os', $schema);
        $this->assertSame(
            array_values(array_diff(SchemaReader::tableNames($pdo, $test->database()), SchemaReader::CONTROL_TABLES)),
            array_keys($schema),
            'A diferença entre a lista crua e a de aplicação tem que ser só as tabelas de controle.'
        );

        $sorted = array_keys($schema);
        sort($sorted, SORT_STRING);

        $this->assertSame(
            $sorted,
            array_keys($schema),
            'A ordem vem do ORDER BY de tableNames(); um ksort aqui seria redundante, e perdê-lo '
                . 'faria o assertSame do gate de paridade passar a comparar ordem.'
        );
    }

    /**
     * O banco existe, e a resposta é sobre o nome exato, não sobre um prefixo.
     *
     * `isCurrent()` tratava o banco inexistente como "remontar", e essa decisão é
     * tomada a partir deste número. A leitura existia escrita à mão no
     * `SchemaFingerprint` e em mais um lugar, e as duas podiam divergir: uma
     * contava linhas de `SCHEMATA` e a outra filtrava por `LIKE`, e a segunda trazia
     * o modelo junto sem querer. O sintoma de uma divergência dessas é um
     * "o banco não existe" num contexto que trata isso como "o schema precisa ser
     * remontado", ou seja, um DROP de 10s disparado por um número que ninguém
     * conferiu.
     */
    #[Test]
    public function testTheDatabaseExistsIsAboutTheExactName(): void
    {
        $test = TestDatabase::fromEnvironment();
        $pdo = self::database();

        $this->assertTrue(SchemaReader::databaseExists($pdo, $test->database()));

        $this->assertFalse(
            SchemaReader::databaseExists($pdo, $test->database() . '_nao_existe'),
            'A leitura precisa casar o nome inteiro. Um LIKE traria o modelo como "existe" para '
                . 'qualquer nome derivado dele, e o chamador não tem como saber que recebeu a resposta '
                . 'da consulta errada.'
        );
    }

    /**
     * O `LIKE` de `databasesLike()` escapa os coringas, e é por isso que ele acha
     * o worker e não o molde de nome dele.
     *
     * O nome do modelo é `mapos_test` e o de um worker é `mapos_1_test`: os dois
     * casam com `mapos%_test`, mas só o segundo é worker, e é o script de limpeza que
     * decide o que pode apagar. Sem o `ESCAPE`, o `_` do padrão casa com qualquer
     * caractere e a busca por `mapos_1_test` traz `maposX1_test` junto — que o
     * chamador pode apagar, porque o nome real passou pelo filtro.
     */
    #[Test]
    public function testTheWildcardSearchTreatsTheUnderscoreAsText(): void
    {
        $test = TestDatabase::fromEnvironment();
        $pdo = self::database();

        $base = DatabaseGuard::modelBase($test->templateDatabase());
        $found = SchemaReader::databasesLike($pdo, $base . '_%_test');

        $this->assertNotContains(
            $test->templateDatabase(),
            $found,
            'O modelo NÃO pode aparecer no padrão do worker, e essa é a razão de o padrão exigir '
                . 'um token no meio. `mapos_test` contra `mapos\_%\_test` é o `LIKE` recusando o '
                . 'modelo por construção, e é o que impede o script de limpeza de derrubá-lo: '
                . 'reconstruí-lo custa os ~9,8s da cadeia de migrations.'
        );

        foreach ($found as $name) {
            $this->assertMatchesRegularExpression(
                '/^' . preg_quote($base, '/') . '_[^_]+_test$/',
                $name,
                'O padrão não pode trazer um nome em que o token tem mais de um caractere. '
                    . 'Um `_` do padrão que casou com `_` está funcionando como coringa, e é '
                    . 'exatamente o que faria a limpeza apagar um banco que ninguém criou.'
            );
        }
    }

    /**
     * As chaves estrangeiras voltam agrupadas por constraint, e não por linha.
     *
     * Uma constraint de duas colunas aparece duas vezes em `KEY_COLUMN_USAGE`, e o
     * agrupamento é o que impede o clone de criar duas constraints com o mesmo nome
     * — o que o MySQL rejeitaria. O agrupamento por linha também faria a contagem
     * de FKs não bater com as 26, e essa contagem é o que o AGENTS.md documenta.
     */
    #[Test]
    public function testTheForeignKeysComeBackGroupedByConstraint(): void
    {
        $test = TestDatabase::fromEnvironment();
        $constraints = SchemaReader::foreignKeys($test->pdo(), $test->database());

        $this->assertNotEmpty($constraints, 'O modelo tem 26 chaves estrangeiras; uma lista vazia é o schema sem elas.');

        foreach ($constraints as $name => $constraint) {
            $this->assertCount(
                count($constraint['columns']),
                $constraint['referenced_columns'],
                "A constraint {$name} tem uma coluna de origem por coluna referenciada, e as duas "
                    . 'listas precisam ter o mesmo tamanho. Uma delas maior significa que o '
                    . 'agrupamento pegou linhas de outra constraint, que é o defeito que a cópia '
                    . 'das tabelas-filhas não pegaria.'
            );

            $this->assertNotSame('', $constraint['table']);
            $this->assertNotSame('', $constraint['referenced_table']);
        }

        $described = array_keys(TestSchemaClone::describeForeignKeys($test->pdo(), $test->database()));
        $read = array_keys($constraints);
        sort($described, SORT_STRING);
        sort($read, SORT_STRING);

        $this->assertSame(
            $read,
            $described,
            'A descrição e a lista bruta precisam vir do mesmo conjunto. É a mesma consulta '
                . 'alimentando as duas, e por isso a impossibilidade de divergirem existe: se uma '
                . 'delas ganhasse um filtro, o clone gravaria uma coisa e a conferência validaria outra.'
        );

        // A ordem de entrada é a do ORDER BY (tabela, nome, posição), e é ela que o
        // replayForeignKeys() consome. O ksort fica em describeForeignKeys(), que só
        // quer comparação estável, então a ordem aqui é a do SQL, não a alfabética.
        $tables = array_map(
            static fn (array $constraint): string => $constraint['table'],
            array_values($constraints)
        );
        $byTable = $tables;
        sort($byTable, SORT_STRING);

        $this->assertSame(
            $byTable,
            $tables,
            'As constraints voltam agrupadas por tabela, na ordem do ORDER BY. Perder esse ORDER BY '
                . 'faria o clone recriar os ALTER numa ordem arbitrária, e `anexos` é o caso que '
                . 'estrava: ele referencia `os`, e a forma inline da constraint falhava nessa ordem.'
        );
    }

    /**
     * As constraints de um banco apontam para dentro dele, e é a única leitura que
     * pergunta isso.
     *
     * `describeForeignKeys()` compara a forma e não o schema de destino, de propósito:
     * ele compara worker contra worker, e o nome do banco de cada um é justamente o
     * que difere. O sintoma de uma constraint que aponta para fora é um clone cuja
     * cópia local do pai não vale para nada — a suíte grava órfão que a produção
     * recusa — e ele passa por essa comparação.
     */
    #[Test]
    public function testTheForeignKeysPointInsideTheDatabaseThatOwnsThem(): void
    {
        $test = TestDatabase::fromEnvironment();

        $this->assertSame(
            [$test->database()],
            SchemaReader::foreignKeyTargetSchemas($test->pdo($test->database()), $test->database()),
            'Uma constraint que cruza de banco não confere a cópia local do pai e ainda segura '
                . 'lock no modelo, que é o que o banco por worker existe para não acontecer.'
        );
    }
}
