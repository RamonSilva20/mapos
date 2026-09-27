<?php

namespace Tests\Support;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/**
 * Prova que a TransactsDatabase realmente isola.
 *
 * A trait cuida do descarte, e o descarte acontece depois do caso terminar —
 * ou seja, nada dentro da suíte observa se ela funcionou. Uma trait que
 * abrisse BEGIN e não fechasse, ou fechasse no depth errado, deixaria a suíte
 * inteira verde e o vazamento continuaria aparecendo só no `logs`, que é
 * exatamente o sintoma que ela existe para eliminar.
 *
 * Por isso estes casos interrogam a conexão de dentro do teste, e não depois
 * dele. E a fonte é `@@autocommit`, pelo mesmo motivo que a trait usa: é o
 * que o driver CI3 religa no commit e no rollback, então é o que distingue de
 * fato uma transação viva de uma que já acabou. O `_trans_depth` é um contador
 * em PHP e pode dizer 1 com o banco sem transação nenhuma.
 */
final class TransactsDatabaseTest extends TestCase
{
    use TransactsDatabase;

    /**
     * A trait abriu a transação e a conexão está de fato dentro dela.
     *
     * O que pega: o gancho não ser chamado, `trans_begin()` falhar em silêncio,
     * ou o caso estar rodando numa conexão diferente da que a trait controlava.
     */
    public function testTheHarnessTransactionIsReallyOpen(): void
    {
        $this->assertSame(
            0,
            $this->autocommit(),
            'A trait deveria ter deixado o autocommit desligado, que é como o CI3 marca a transação aberta.'
        );

        $this->assertSame(
            1,
            $this->transactionDepth(get_instance()->db),
            'O depth do CI3 deveria ser 1 durante o caso.'
        );
    }

    /**
     * Um controller sob teste não consegue commitar a transação do harness.
     *
     * Este é o motivo de a trait existir em vez de um BEGIN/ROLLBACK cru: o CI3
     * não cria SAVEPOINT (DB_driver.php:trans_begin só incrementa o contador
     * acima de zero), então o `trans_complete()` de um controller apenas desce o
     * depth de 2 para 1 e nada é gravado. Sem essa garantia, qualquer
     * controller que commita vazaria o estado do caso para os seguintes.
     */
    public function testCodeUnderTestCannotCommitTheHarnessTransaction(): void
    {
        $db = get_instance()->db;

        $db->trans_start();
        $this->assertSame(2, $this->transactionDepth($db), 'trans_start() aninhado deveria subir o depth.');

        $db->trans_complete();

        $this->assertSame(1, $this->transactionDepth($db), 'trans_complete() aninhado deveria só descer o depth.');

        $this->assertSame(
            0,
            $this->autocommit(),
            'O commit aninhado não pode ter religado o autocommit: a transação do harness continua aberta.'
        );
    }

    /**
     * O caminho sem volta: um rollback interno derruba a transação do harness.
     *
     * Documenta o limite conhecido em vez de escondê-lo atrás de um caso verde.
     * É o que `Financeiro::excluirLancamento()` faz no caminho de erro
     * (trans_complete() e depois trans_rollback()), e o tearDown acusaria isso
     * como transação roubada.
     */
    public function testAnInnerRollbackConsumesTheHarnessTransaction(): void
    {
        $db = get_instance()->db;

        $db->trans_start();
        $this->assertSame(2, $this->transactionDepth($db));

        $db->trans_rollback();
        $this->assertSame(1, $this->transactionDepth($db));

        // Este é o ponto: o rollback interno não tinha SAVEPOINT para usar, então
        // o depth voltou para 1 sem desfazer nada, e a transação do harness
        // continua no servidor. O commit aninhado do caso anterior é o
        // comportamento correto; este é o que o harness não consegue impedir.
        $this->assertSame(
            0,
            $this->autocommit(),
            'Um rollback interno não pode derrubar a transação do harness, porque ela não tem SAVEPOINT.'
        );

        $this->assertSame(1, $this->transactionDepth($db));
    }

    /**
     * Um commit reaching depth zero de fato reverte a transação inteira.
     *
     * O par trans_complete() + trans_rollback() de Financeiro chega no depth 0
     * com um rollback verdadeiro, e aí o autocommit é religado. É o caminho
     * que o tearDown detecta e acusa, então o caso fixa o sintoma para que a
     * mensagem tenha um contrajeito verificável.
     */
    public function testRollbackAtDepthZeroEndsTheHarnessTransaction(): void
    {
        $db = get_instance()->db;

        $db->trans_start();
        $this->assertSame(2, $this->transactionDepth($db));

        $db->trans_complete();
        $db->trans_rollback();

        $this->assertSame(0, $this->transactionDepth($db));
        $this->assertSame(
            1,
            $this->autocommit(),
            'O rollback no depth 0 deveria ter encerrado a transação e religado o autocommit.'
        );

        // O tearDown desta classe roda depois e encontraria a transação perdida.
        // Reabrir devolve o processo ao estado em que o caso começou, senão a
        // detecção acusaria este próprio teste de vazamento.
        $db->trans_begin();
    }

    /**
     * Prova de ponta a ponta que o descarte acontece de verdade.
     *
     * Os casos acima interrogam a conexão de dentro do teste, e todos passam
     * mesmo que a trait feche a transação com `trans_complete()` em vez de
     * rollback — o `@@autocommit` volta a 1 nos dois casos e o sintoma é o
     * mesmo. Só a linha na tabela denuncia a diferença, e é a linha que de
     * fato importa: o vazamento que motivou a trait aparecia no `logs`.
     *
     * Por isso o par de casos. O primeiro grava e confirma que a gravação está
     * visível dentro da própria transação; o segundo, que só roda depois, tem
     * de encontrá-la ausente. Se o rollback não tivesse acontecido, sobraria
     * uma linha a cada execução da suíte — exatamente o `logs` que subia de 5
     * em 5.
     */
    public function testTheRowWrittenHereIsVisibleInsideTheTransaction(): void
    {
        $this->escreverLinhaDeLog();
        $this->assertSame(
            1,
            $this->contarLinhasDeLog(),
            'A gravação deveria estar visível de dentro da transação que a trait abriu.'
        );
    }

    #[Depends('testTheRowWrittenHereIsVisibleInsideTheTransaction')]
    public function testTheRowWrittenByThePreviousTestIsGone(): void
    {
        $this->assertSame(
            0,
            $this->contarLinhasDeLog(),
            'A linha do caso anterior sobreviveu, então a trait está descartando com commit em vez de rollback.'
        );
    }

    /**
     * Grava uma linha em `logs` com a marca do par de casos.
     */
    private function escreverLinhaDeLog(): void
    {
        get_instance()->db->insert('logs', [
            'usuario' => 'harness',
            'tarefa' => 'TransactsDatabaseTest',
            'data' => '2026-01-01',
            'hora' => '00:00:00',
            'ip' => '127.0.0.1',
        ]);
    }

    /**
     * Quantas linhas o par de casos deixou em `logs`.
     */
    private function contarLinhasDeLog(): int
    {
        return (int) get_instance()->db
            ->query("SELECT COUNT(*) AS total FROM `logs` WHERE `tarefa` = 'TransactsDatabaseTest'")
            ->row_array()['total'];
    }

    /**
     * O autocommit da conexão, que é como o CI3 marca a transação aberta.
     *
     * Detalhe do protocolo: com a conexão em buffering, o ROLLBACK do caso
     * anterior deixaria um resultado não lido e a próxima consulta falharia com
     * "Cannot execute queries while other unbuffered queries are active". Por
     * isso o CAST para inteiro vem na mesma consulta, sem uma segunda ida ao
     * servidor entre a medição e o seu uso.
     */
    private function autocommit(): int
    {
        $row = get_instance()->db->query('SELECT CAST(@@autocommit AS UNSIGNED) AS autocommit')->row_array();

        return (int) ($row['autocommit'] ?? 1);
    }

    /**
     * Lê o `_trans_depth`, que é protected. Ver TransactsDatabase.
     */
    private function transactionDepth(object $db): int
    {
        return (function (): int {
            return $this->_trans_depth;
        })->call($db);
    }
}
