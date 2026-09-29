<?php

namespace Tests\Support\App;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * O fechamento da transação do driver, que é o `tearDown` de que ninguém duvida.
 *
 * `TransactsDatabase::tearDownDatabaseTransaction()` chama `closeDriverTransaction()`
 * num `finally`, ou seja, ele roda mesmo quando o caso estourou. É o lugar onde um
 * harness se protege de si mesmo: se o `ROLLBACK` falhou no meio, sobraria uma
 * conexão com autocommit desligado e depth > 0, e todos os testes seguintes do
 * processo escreveriam dentro de uma transação que ninguém abre nem fecha — sem um
 * único assert reclamando.
 *
 * Este arquivo existe porque esse `tearDown` tem uma propriedade que o caminho
 * feliz não exercita e que é a que mais importa: ele se recusa a fingir que
 * terminou. A versão anterior zerava o contador para qualquer driver que não fosse
 * mysqli, o que produzia exatamente o estado que o método existe para desfazer.
 */
final class Ci3IntrospectionTest extends TestCase
{
    /**
     * Um driver que não é mysqli nem PDO é configuração não suportada, e o
     * fechamento avisa em vez de deixar a conexão meio fechada.
     *
     * O caminho do `finally` é o que torna isso importante. Uma exceção aqui
     * sobe pelo `tearDown` e aparece como erro do caso — legível, na hora da
     * configuração. A versão anterior não lançava nada: zerava `_trans_depth` e
     * devolvia, e o processo seguia com autocommit desligado e a transação aberta.
     * O sintoma disso não era uma falha, era a ausência de uma: o próximo teste
     * escrevia, o `ROLLBACK` do `tearDown` seguinte não encontrava depth para
     * desfazer, e a série inteira passava com o banco num estado que ninguém
     * autorizou.
     *
     * O objeto é um `stdClass` porque o método só precisa de `conn_id` e
     * `_trans_depth`: nenhum driver real é instalado, e um banco de verdade aqui
     * testaria a configuração de quem roda, e não a decisão deste método.
     */
    #[Test]
    public function testAnUnsupportedDriverIsRefusedInsteadOfHalfClosed(): void
    {
        $db = new class {
            /** @var object um driver que o CI3 não carrega por padrão */
            public $conn_id;

            public int $_trans_depth = 1;

            public function __construct()
            {
                $this->conn_id = new class {
                };
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/só conhece mysqli e PDO/');

        Ci3Introspection::closeDriverTransaction($db);
    }

    /**
     * O depth fica intacto quando o driver é recusado, porque a exceção sai antes.
     *
     * A ordem dentro do método importa e é o que o caso anterior não prende: se o
     * `_trans_depth = 0` viesse antes da checagem, o método continuaria levantando
     * a exceção — o `expectException` acima passaria igual — e mesmo assim teria
     * zerado o contador, que é o estado que o `tearDown` do próximo caso espera
     * encontrar. Um teste que passa com as duas ordens não está prendendo a ordem,
     * e a ordem é a coisa que este método faz.
     */
    #[Test]
    public function testTheDepthSurvivesTheRefusal(): void
    {
        $db = new class {
            /** @var object um driver que o CI3 não carrega por padrão */
            public $conn_id;

            public int $_trans_depth = 1;

            public function __construct()
            {
                $this->conn_id = new class {
                };
            }
        };

        try {
            Ci3Introspection::closeDriverTransaction($db);
        } catch (RuntimeException) {
            // A exceção é o outro teste. O que este caso afirma é o estado em que a
            // conexão ficou depois dela.
        }

        $this->assertSame(
            1,
            Ci3Introspection::transactionDepth($db),
            'O contador zerado junto com a exceção faria o tearDown do caso seguinte achar que '
                . 'não há transação para desfazer, e o estado poisonous passaria a parecer limpo.'
        );
    }

    /**
     * Um depth zero sai antes de olhar o driver, e é por isso que o caminho normal
     * não paga a checagem.
     *
     * `tearDownDatabaseTransaction()` chama este método no `finally` de TODOS os
     * casos transacionados, e no caminho normal — rollback que funcionou — o depth
     * já é zero. Se a recusa fosse avaliada antes do `return`, uma conexão PDO de
     * uma configuração suportada derrubaria o `tearDown` inteiro em todos os casos
     * da série, em vez de só nos que realmente deixaram transação aberta.
     */
    #[Test]
    public function testAClosedTransactionNeverLooksAtTheDriver(): void
    {
        $db = new class {
            /** @var object um driver que o CI3 não carrega por padrão */
            public $conn_id;

            public int $_trans_depth = 0;

            public function __construct()
            {
                $this->conn_id = new class {
                };
            }
        };

        Ci3Introspection::closeDriverTransaction($db);

        $this->assertSame(0, Ci3Introspection::transactionDepth($db));
    }
}
