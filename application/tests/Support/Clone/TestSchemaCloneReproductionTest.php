<?php

namespace Tests\Support\Clone;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\Database\TestDatabase;

/**
 * A cópia reproduz a origem, e a segunda chamada não paga a cópia de novo.
 *
 * A barreira existe porque `LIKE` não copia constraints, o MySQL não avisa, e um
 * banco sem constraint é um banco que aceita mais coisa do que devia. A suíte
 * continuaria verde.
 *
 * Este é o caso que mede o mecanismo inteiro — `assertSame` sobre a descrição
 * completa das duas metades — e por isso fica sozinho: os outros três arquivos
 * olham uma peça do mesmo quebra-cabeça, e um diff de array apontando direto para
 * as duas chaves que faltam é o que este caso entrega.
 */
final class TestSchemaCloneReproductionTest extends TestCase
{
    use TestSchemaCloneSyntheticOrigin;

    /**
     * O clone reproduz o schema de origem, chave estrangeira por chave estrangeira.
     *
     * A comparação é de uma vez, com assertSame sobre a descrição inteira, e não
     * tabela por tabela. O motivo é o modo de falha que cada uma deixa passar: um
     * laço com um assert por tabela só diz QUAL tabela divergiu, e o defeito que
     * este arquivo existe para pegar — as constraints que sumiram — precisa que
     * alguém leia a diferença de uma lista curta para achar as duas que faltam. A
     * descrição inteira no diff aponta direto.
     */
    #[Test]
    public function testTheCloneReproducesTheOriginSchema(): void
    {
        $test = TestDatabase::fromEnvironment();
        $clone = new TestSchemaClone($test);

        $this->assertTrue(
            $clone->ensureWorkerDatabase(self::destination(), self::origin()),
            'A primeira chamada tem que clonar. Se devolveu false, o destino já estava em dia e o caso não testou nada.'
        );

        $pdo = $test->pdo();

        $this->assertSame(
            self::describe($pdo, self::origin()),
            self::describe($pdo, self::destination()),
            'O clone não é igual à origem. Se a diferença estiver nas chaves estrangeiras, é o '
                . '`CREATE TABLE ... LIKE` silenciosamente sem elas; se estiver em `linhas`, o `INSERT '
                . '... SELECT` pulou alguma tabela; se estiver em `colunas` ou `indices`, a origem mudou '
                . 'depois do clone.'
        );

        // O mesmo banco, uma segunda vez, e sem pagar a cópia. A verificação mora
        // neste caso e não num próprio porque o clone que ela confere é este: um
        // caso separado teria de clonar de novo para ter o que conferir, e um
        // clone do schema de duas tabelas custa ~400ms de DDL.
        //
        // O que está protegido é o custo. São 3,7s na primeira vez e 2ms depois
        // no schema real, e um clone que refizesse a cópia a cada execução seria
        // 3,7s por execução — o que jogaria fora o ganho inteiro do parallelismo.
        $this->assertFalse(
            $clone->ensureWorkerDatabase(self::destination(), self::origin()),
            'A segunda chamada devolveu que clonou de novo, o que significa que a impressão digital do '
                . 'worker não foi lida ou não foi gravada.'
        );
    }
}
