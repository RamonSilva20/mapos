<?php

namespace Tests\Support\Clone;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\Database\TestDatabase;

/**
 * A chave estrangeira chega no clone com a regra certa, e o statement que a monta
 * é conferível sem banco nenhum.
 *
 * São duas perguntas que o diff de array não responde de forma legível, então
 * ficam nomeadas aqui: o que a cópia carrega, e o SQL que a produziu. A terceira
 * e a quarta são as guardas do mesmo statement, e moram aqui porque é o statement
 * que elas recusam.
 */
final class TestSchemaCloneForeignKeysTest extends TestCase
{
    use TestSchemaCloneSyntheticOrigin;

    /**
     * As constraints chegam no clone com a regra certa, e não com o default.
     *
     * Separado do caso acima porque é a informação que o diff de um array não dá
     * de forma legível: aqui cada chave é nomeada, e uma que falte diz o que era.
     *
     * A `fk_composta` é a que mais importa, e por dois motivos que um teste só de
     * chave simples não veria. Ela tem duas colunas, e o agrupamento das linhas do
     * information_schema é o que monta `(pai_id, pai_seg)` em vez de duas
     * constraints de uma coluna cada com nome repetido — que é uma forma de
     * passar vergonha silenciosamente, porque count() continuaria 2. E ela tem
     * `ON UPDATE CASCADE`, que não é o default: se o `ALTER TABLE` montado
     * esquecesse as regras, o MySQL aceitaria o statement, o clone nasceria com
     * NO ACTION, e a suíte passaria.
     */
    #[Test]
    public function testEveryForeignKeyOfTheOriginExistsInTheCloneWithItsRule(): void
    {
        $test = TestDatabase::fromEnvironment();
        $pdo = $test->pdo();

        (new TestSchemaClone($test))->ensureWorkerDatabase(self::destination(), self::origin());

        $fromOrigin = TestSchemaClone::describeForeignKeys($pdo, self::origin());

        $this->assertNotEmpty(
            $fromOrigin,
            'A origem ficou sem chave estrangeira nenhuma, o que significa que este caso não está '
                . 'testando o que pensa.'
        );

        $this->assertCount(
            2,
            $fromOrigin,
            'A origem tem duas constraints e a cópia tem ' . count($fromOrigin) . '. Uma chave de duas '
                . 'colunas contada como duas de uma coluna daria o mesmo total com nomes errados.'
        );

        $fromClone = TestSchemaClone::describeForeignKeys($pdo, self::destination());

        foreach ($fromOrigin as $name => $description) {
            $this->assertArrayHasKey(
                $name,
                $fromClone,
                "A chave estrangeira '{$name}' existe na origem e não no clone: {$description}. "
                    . 'É o que `CREATE TABLE ... LIKE` não copia, e é a razão de o TestSchemaClone '
                    . 'recriar as constraints.'
            );

            $this->assertSame(
                $description,
                $fromClone[$name],
                "A chave estrangeira '{$name}' mudou de regra ou de coluna entre a origem e o clone. "
                    . 'Trocar NO ACTION por CASCADE muda o que acontece com as linhas filhas quando a '
                    . 'pai é apagada, e uma suíte que roda contra a regra errada não está testando o '
                    . 'schema que a produção tem.'
            );
        }

        // Os dois casos que a contagem acima não separa: a composta tem de ter
        // as duas colunas, e o CASCADE tem de ter vindo junto.
        $this->assertStringContainsString(
            '(pai_id,pai_seg) -> pai(id,seg)',
            $fromClone['fk_composta'] ?? '',
            'A chave de duas colunas perdeu uma das colunas no caminho. Count() continuaria dizendo 2.'
        );

        $this->assertStringContainsString(
            'ON UPDATE CASCADE',
            $fromClone['fk_composta'] ?? '',
            'A regra de UPDATE não sobreviveu ao clone. O statement é aceito pelo MySQL sem ela, e o '
                . 'default é NO ACTION — que só aparece como bug no dia em que alguém precisar do CASCADE.'
        );
    }

    /**
     * As duas regras entram no `ALTER TABLE` mesmo quando são o default.
     *
     * `ADD CONSTRAINT` sem `ON UPDATE`/`ON DELETE` explícito usa RESTRICT, e um
     * `SET NULL` ou um `CASCADE` passariam a sumir do clone sem erro nenhum — o
     * statement executa, o banco aceita, e a diferença só aparece quando um teste
     * que depende do CASCADE falha. A montagem fica numa função pública e
     * separada para poder ser conferida sem banco nenhum.
     */
    #[Test]
    public function testTheForeignKeyStatementCarriesBothRulesAndPointsInsideTheWorker(): void
    {
        $sql = TestSchemaClone::addForeignKeyStatement(
            'mapos_1_test',
            'fk_anexos_os1',
            [
                'table' => 'anexos',
                'columns' => ['os_id'],
                'referenced_table' => 'os',
                'referenced_columns' => ['idOs'],
                'update_rule' => 'CASCADE',
                'delete_rule' => 'SET NULL',
            ]
        );

        $this->assertSame(
            'ALTER TABLE `mapos_1_test`.`anexos` ADD CONSTRAINT `fk_anexos_os1` '
                . 'FOREIGN KEY (`os_id`) REFERENCES `mapos_1_test`.`os` (`idOs`) '
                . 'ON UPDATE CASCADE ON DELETE SET NULL',
            $sql,
            'O `ALTER TABLE` precisa trazer as duas regras e qualificar as duas pontas. O `REFERENCES` '
                . 'é o WORKER, e não o modelo: um filho que aponta para o pai do modelo não é conferido '
                . 'contra a cópia do pai que está no worker, e ainda segura lock na linha do modelo '
                . 'que ele queria isolar.'
        );
    }

    /**
     * Uma regra de referência desconhecida é recusada, e não interpolada.
     *
     * `UPDATE_RULE` e `DELETE_RULE` são palavras-chave com espaço (`NO ACTION`), não
     * identificadores, então não passam pela checagem de nome de objeto. Elas têm
     * uma lista fechada, e é contra essa lista que são conferidas: um valor
     * inesperado ali significa que a coluna do information_schema mudou de
     * sentido, e a falha precisa dizer isso em vez de montar um SQL que o MySQL
     * talvez aceite.
     */
    #[Test]
    public function testAnUnknownReferentialRuleIsRefused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Regra de referência/');

        TestSchemaClone::addForeignKeyStatement(
            'mapos_1_test',
            'fk_qualquer',
            [
                'table' => 'anexos',
                'columns' => ['os_id'],
                'referenced_table' => 'os',
                'referenced_columns' => ['idOs'],
                'update_rule' => 'DROP EVERYTHING',
                'delete_rule' => 'CASCADE',
            ]
        );
    }

    /**
     * Um identificador que não é nome é recusado antes de chegar ao SQL.
     *
     * Os nomes vêm do information_schema e do nome do banco, e os dois são
     * normalmente inofensivos. A concatenação continua sendo a forma de injeção
     * que o projeto proíbe, e a garantia de que eles são inofensivos não é deste
     * arquivo: é do `assertDatabaseNameIsSafe()`. Um dia uma constraint se chamar
     * `` `x`, `DROP TABLE `os `` e o clone passa a executar o que o nome mandava.
     * Checar custa uma regex.
     */
    #[Test]
    public function testAnIdentifierThatIsNotANameIsRefused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Identificador fora do esperado/');

        TestSchemaClone::addForeignKeyStatement(
            'mapos_1_test',
            'fk_qualquer',
            [
                'table' => 'anexos`, `x`, `DROP TABLE `os',
                'columns' => ['os_id'],
                'referenced_table' => 'os',
                'referenced_columns' => ['idOs'],
                'update_rule' => 'NO ACTION',
                'delete_rule' => 'NO ACTION',
            ]
        );
    }
}
