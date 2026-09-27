<?php

namespace Tests\Support;

use PHPUnit\Framework\TestCase;

/**
 * Afirma invariantes do harness, e não o resultado das migrações.
 *
 * O ponto não é "as migrações rodam" — isso o `composer test` já provou ao
 * montar o banco. O ponto é que a sequência em `TestApplication::migrate()`
 * não deixa o processo num estado diferente do que o encontrou, porque um
 * harness que suja o estado não falha de forma legível: ele faz o próximo
 * teste escrever no lugar errado e o relatório do PHPUnit desaparecer.
 */
final class TestApplicationTest extends TestCase
{
    /**
     * `migrate()` abre um buffer para engolir o relatório do Migrator, que é
     * saída e não resultado, e tem que devolver o processo no mesmo nível de
     * buffer em que o encontrou.
     *
     * O que este teste pega: um `return` antecipado, um `ob_start()` a mais ou
     * qualquer condição introduzida entre abrir e fechar o buffer.
     *
     * O que este teste **não** pega, e é preciso dizer: o caminho em que
     * `Tools::migrate()` estoura. Aqui ele não estoura, então o `finally` não
     * é exercido — o balanço de `ob_get_level()` happens igual com e sem ele.
     * O `finally` se justifica pelo modo de falha, não por esta asserção: sem
     * ele, uma cadeia de migrações quebrada deixaria o buffer aberto, o
     * PHPUnit passaria a escrever dentro dele, e o erro apareceria como um
     * processo mudo em vez de uma migration quebrada com nome e número.
     *
     * Testar esse caminho exigiria uma cadeia de migrações quebrada de
     * propósito, o que trocaria um teste de 6 linhas por uma fixture que
     * mente sobre o schema. A Alternative seria injetar o migrador, o que é
     * uma abstração a mais para um único `finally`.
     */
    public function testMigrateLeavesTheOutputBufferLevelUntouched(): void
    {
        $before = ob_get_level();

        TestApplication::migrate();

        $this->assertSame(
            $before,
            ob_get_level(),
            'migrate() não pode deixar um output buffer aberto'
        );
    }
}
