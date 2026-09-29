<?php

namespace Tests\Support\Clone;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\Database\SchemaReader;
use Tests\Support\Database\TestDatabase;

/**
 * O schema real: o banco deste worker é cópia do modelo, e a constraint copiada
 * aponta para dentro dele.
 *
 * Os dois casos daqui não usam o schema sintético das outras três classes, e é
 * proposital. Montar duas tabelas de mentira responde sobre o caminho de código,
 * e a pergunta de produção é outra: se os 28 tabelas e as 26 constraints reais
 * sobreviveram à cópia. Aqui a origem é o `mapos_test` de verdade, que o bootstrap
 * já clonou antes do boot do CI3, então comparar os dois não custa uma cópia.
 *
 * A origem real vem com uma consequência que o caso de paridade documenta por conta
 * própria: sob o PHPUnit normal não há `TEST_TOKEN`, os dois nomes são o mesmo banco
 * e a comparação seria uma tautologia. Os dois casos são pulados, e nenhum job de CI
 * liga `composer test:parallel`, então em toda execução da série esta classe é
 * pulada.
 *
 * Por isso ela NÃO usa o `TestSchemaCloneSyntheticOrigin`, que era o que esta
 * classe usava. O `#[BeforeClass]` e o `#[AfterClass]` daquele trait montavam e
 * derrubavam `mapos_clone_origem_solo_test` e `mapos_clone_destino_solo_test` antes
 * de qualquer caso rodar — e como o pulo acontece DENTRO do caso, esse custo era pago
 * em toda execução da série para zero asserções. O `describe()` de que o caso de
 * paridade precisa vem de `TestSchemaCloneShared`, que é o mesmo trait sem os ganchos
 * de banco, e é esse que entra aqui.
 */
final class TestSchemaCloneWorkerParityTest extends TestCase
{
    use TestSchemaCloneShared;

    /**
     * O banco deste processo é cópia do modelo, com as 26 constraints de verdade.
     *
     * Esta é a pergunta sobre o schema real, e ela é a que protege a execução
     * paralela. O bootstrap já clonou o banco deste worker antes do boot do CI3,
     * então comparar os dois aqui não custa uma cópia: os dois já existem, e a
     * diferença entre eles é zero justamente quando o clone fez o seu trabalho.
     *
     * Sob o PHPUnit normal não há worker, os dois nomes são o mesmo banco e a
     * comparação seria uma tautologia — passaria sempre e não conferiria nada. O
     * caso é pulado e o motivo fica escrito, porque um teste pulado que não diz
     * por quê parece um teste quebrado.
     */
    #[Test]
    public function testTheDatabaseOfThisProcessMatchesTheModel(): void
    {
        $test = TestDatabase::fromEnvironment();

        if ($test->database() === $test->templateDatabase()) {
            $this->markTestSkipped(
                'Sem TEST_TOKEN este processo usa o banco modelo diretamente, então compará-lo com o '
                    . 'modelo seria a mesma consulta ao mesmo banco. O caminho de código é conferido pelo '
                    . 'TestSchemaCloneReproductionTest::testTheCloneReproducesTheOriginSchema(), e o clone '
                    . 'do schema real só existe quando alguém roda `composer test:parallel` — nenhum job de '
                    . 'CI roda, porque o custo frio de quatro clones é maior que o que a série economiza.'
            );
        }

        $pdo = $test->pdo();

        $this->assertSame(
            self::describe($pdo, $test->templateDatabase()),
            self::describe($pdo, $test->database()),
            'O banco deste processo não é cópia do modelo. A consequência prática é que a suíte está '
                . 'rodando contra um schema sem as chaves estrangeiras que a produção tem, e ela não '
                . 'vai avisar: um banco sem constraint é um banco que aceita mais escrita do que devia.'
        );
    }

    /**
     * A constraint do worker aponta para o pai DO WORKER, e é o banco real que diz.
     *
     * Este caso existe por causa de um defeito que a paridade não enxergava. O
     * `REFERENCES` era qualificado com o nome do modelo, e tanto a comparação de
     * `describe()` quanto o teste do SQL acima passavam: a forma da constraint
     * estava certa, e o schema para onde ela aponta é uma informação que nenhuma
     * das duas conferia.
     *
     * A consequência era o oposto do que este arquivo existe para entregar. O pai
     * copiado para dentro do worker deixava de valer para a constraint — então um
     * filho órfão passava, e a suíte podia gravar o que a produção recusa — e todos
     * os workers terminavam segurando X-lock nas mesmas linhas do pai no modelo,
     * que é a contenção entre processos que o banco por worker veio para matar,
     * trocada por uma menor e muito mais difícil de ver.
     *
     * Ler `REFERENCED_TABLE_SCHEMA` é o que enxerga isso. `describe()` não traz o
     * schema de propósito: ele compara worker contra worker, e o nome do banco de
     * cada um é diferente justamente por isso.
     *
     * A leitura é `SchemaReader::foreignKeyTargetSchemas()`, e a consulta em vez de
     * `CONSTRAINT_SCHEMA = DATABASE()` que este caso usava. `DATABASE()` é o banco
     * que a CONEXÃO tem selecionado, o que é uma suposição sobre quem conectou, e
     * a suposição quebrava em silêncio: passar um `$schema` diferente do selecionado
     * media um banco e afirmava sobre o outro. O nome vai explícito, e é a mesma
     * leitura que serve o resto da suíte.
     *
     * O banco inspecionado é `$test->database()`, que é o worker do próprio
     * processo — a cópia que o bootstrap já fez antes do boot do CI3. Nenhuma cópia
     * é feita aqui, e isso é uma correção e não uma economia: este caso já clonava
     * para `self::destination()`, que é um banco à parte, e depois media o worker.
     * A cópia não era lida por nenhuma das afirmações, então ela pagava o custo de
     * uma clonagem — a operação mais cara da suíte — para não responder nada. Num
     * fingerprint frio são os ~3.7s que o AGENTS.md documenta, e o resultado ficava
     * lá esperando uma leitura que não existia.
     */
    #[Test]
    public function testTheClonedConstraintPointsInsideTheWorker(): void
    {
        $test = TestDatabase::fromEnvironment();

        // Mesmo motivo do caso acima, e pelo mesmo motivo ele não é um detalhe: sem
        // worker, `$test->database()` é o modelo, e a consulta mediria o modelo
        // apontando para si mesmo. Isso é verdade e não prova nada sobre cópia.
        if ($test->database() === $test->templateDatabase()) {
            $this->markTestSkipped(
                'Sem TEST_TOKEN este processo usa o banco modelo, então não há cópia de worker para '
                    . 'conferir. O caso roda em cada worker do ParaTest, e é lá que ele vale.'
            );
        }

        $schema = $test->database();

        $this->assertNotEmpty(
            $targets = SchemaReader::foreignKeyTargetSchemas($test->pdo($schema), $schema),
            'O destino clonado não tem nenhuma constraint; a cópia não levou as FKs.'
        );

        $this->assertSame(
            [$schema],
            $targets,
            'Há constraint apontando para fora do worker. Uma chave estrangeira que cruza de banco '
                . 'não confere a cópia local do pai e ainda segura lock no modelo, que é o que o '
                . 'banco por worker existe para não acontecer.'
        );
    }
}
