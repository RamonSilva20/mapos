<?php

namespace Tests\Support\Clone;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\Database\TestDatabase;

/**
 * As pré-condições de `ensureWorkerDatabase()`, e só elas.
 *
 * Os dois casos daqui recusam antes de copiar, e nenhum dos dois toca no schema
 * sintético: um modelo velho e um destino homônimo são decididos por nome e por
 * impressão digital, sem DDL. Ficam num arquivo só porque a consequência de
 * ignorá-los é sempre a mesma e é sempre cara — a suíte serial derrubando o banco
 * dela, ou N processos rodando a cadeia de migrations ao mesmo tempo.
 */
final class TestSchemaCloneGuardsTest extends TestCase
{
    use TestSchemaCloneShared;

    /**
     * Modelo que não está em dia não gera worker, e a mensagem diz o que rodar.
     *
     * Montar aqui em vez de recusar pareceria mais atencioso e seria pior: N
     * processos encontram o modelo velho ao mesmo tempo e cada um roda a cadeia de
     * ~9,8s de migrations sobre o mesmo banco, o que é lentíssimo e uma corrida
     * de DDL. A correção é uma linha na ponta (`composer test:db`), e qualquer um
     * dos dois custos é mais caro que isso.
     */
    #[Test]
    public function testAModelThatIsNotCurrentIsRefusedInsteadOfCloned(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/composer test:db/');

        (new TestSchemaClone(TestDatabase::fromEnvironment()))
            ->ensureWorkerDatabase(self::destination(), 'mapos_nunca_construido_test');
    }

    /**
     * Worker e modelo com o mesmo nome não é caso de clone.
     *
     * É o caminho da execução serial, e ele tem que devolver false sem tocar em
     * nada: se clonasse, o `composer test` normal derrubaria e remontaria o banco
     * da suíte antes de começar.
     */
    #[Test]
    public function testTheSameNameIsNotCloned(): void
    {
        $this->assertFalse(
            (new TestSchemaClone(TestDatabase::fromEnvironment()))
                ->ensureWorkerDatabase('mapos_test', 'mapos_test'),
            'Com os dois nomes iguais não há o que clonar, e a execução serial depende de não haver.'
        );
    }
}
