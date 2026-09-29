<?php

namespace Tests\Support\Transaction;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

use Tests\Support\App\Ci3Introspection;
use Tests\Support\Database\TestFixtures;

/**
 * Envolve cada teste numa transação e descarta no fim.
 *
 * Opt-in por `use TransactsDatabase;`, sem herança: uma classe que não escreve
 * no banco paga um BEGIN e um ROLLBACK por caso sem ganhar nada. O que resolve é
 * o vazamento — `log_info()` grava em `logs` e o `Login::verificarLogin()` chama
 * log_info() em todo login, então a suíte deixava uma linha para trás por
 * execução. A transação roda na conexão do CI3 (`get_instance()->db`), que a suíte
 * in-process compartilha entre todos os testes do processo, e por isso o
 * fechamento é defensivo: uma transação que vaza de um caso para o outro não dá
 * mensagem nenhuma e só aparece como um teste que passa sozinho.
 *
 * ## As três armadilhas do CI3 que o fechamento trata
 *
 * Não têm equivalente no Laravel porque lá existe SAVEPOINT.
 *
 * 1. `trans_begin()` com depth acima de zero só incrementa o contador. Um
 *    controller sob teste que abre a própria transação não consegue commitar a
 *    nossa, porque o commit só acontece no depth 1 — nem reverter só o que é
 *    dele, porque um rollback interno derruba a transação inteira.
 * 2. `trans_rollback()` só fala com o banco no depth exatamente 1
 *    (DB_driver.php:380); nos acima, só decrementa. Uma pilha deixada em 3 exige
 *    três chamadas até chegar no ROLLBACK de verdade.
 * 3. `_trans_rollback()` e `_trans_commit()` religam o autocommit
 *    (mysqli_driver.php:362). Sem o fechamento, todo caso seguinte escreve numa
 *    transação que ninguém fecha — por isso o autocommit é religado mesmo quando
 *    a transação já sumiu.
 *
 * ## O que esta trait NÃO cobre
 *
 *   - DDL. `CREATE`, `ALTER`, `DROP` e `TRUNCATE` fazem commit implícito e
 *     derrubam a transação sem aviso. É por isso que
 *     TestApplicationTest::testMigrateLeavesTheOutputBufferLevelUntouched() não
 *     usa isto: ele roda Tools::migrate(), que hoje não executa nada porque as
 *     migrations já estão aplicadas. Isso é uma leitura do estado atual, não uma
 *     garantia — a primeira migration nova quebraria o isolamento do próprio teste.
 *   - MyISAM, que não tem transação. As 28 tabelas do banco de teste são
 *     InnoDB e o create_base força o motor com ALTER TABLE depois de cada
 *     CREATE. Vale reconferir se alguma vez entrar pelo dump com o motor
 *     padrão errado, porque a transação vira no-op e passa sem avisar.
 *   - Grupos de conexão além do `default`. O autoload do CI3 tem um grupo só;
 *     um `$this->load->database('outro')` abriria uma segunda conexão que a
 *     transação não alcança.
 *
 * ## A reinstalação da linha de base
 *
 * `resetsBaselineData(): true` reinstala os usuários antes de cada caso, dentro da
 * transação que a trait acabou de abrir; AGENTS.md traz o opt-in, o `DELETE` em
 * vez de `TRUNCATE` e o motivo de TransactsDatabaseTest não optar.
 *
 * Um limite que AGENTS.md não registra, e que o contador `baselineResets()`
 * resolve: como tudo roda em transação, o estado de `usuarios` no corpo de um caso
 * é o das fixtures com ou sem a reinstalação. Apagar a chamada no
 * `setUpDatabaseTransaction()` deixava a suíte inteira verde, e ninguém acharia.
 * BaselineDataResetTest afirma que o gancho rodou, então a direção perigosa — a
 * reinstalação existir e não ser chamada, que é a que importa — vira um teste
 * vermelho. O que pega a outra direção, commit em vez de rollback, é o guard de
 * `logs`.
 */
trait TransactsDatabase
{
    /**
     * A classe pede a reinstalação da linha de base antes de cada caso.
     *
     * Um método, e não uma propriedade, por um motivo concreto: o PHP trata a
     * colisão entre uma propriedade de trait e uma da classe que a usa como
     *_definition diferente_ e aborta o processo inteiro com "define the same
     * property" — mesmo quando os tipos batem, porque os valores padrão não
     * batem. Um método tem sobrescrita normal, e o padrão `false` é a resposta
     * para o caso de a classe não se manifestar.
     */
    protected function resetsBaselineData(): bool
    {
        return false;
    }

    /**
     * Abre a transação do caso, e recusa continuar se a anterior ficou aberta.
     *
     * Recusar aqui, e não no tearDown, é o que mantém a falha legível: um
     * depth herdado de 1 faria a transação deste caso aninhar na anterior, e
     * o rollback do fim desfaria a do teste anterior em vez da dele, produzindo
     * um resultado que depende da ordem de execução.
     */
    #[Before]
    protected function setUpDatabaseTransaction(): void
    {
        $db = get_instance()->db;

        $inherited = Ci3Introspection::transactionDepth($db);

        if ($inherited !== 0) {
            $this->fail(
                "A transação do teste anterior não foi fechada (depth {$inherited}). "
                . 'Um tearDown sem transação, um controller que chamou '
                . 'trans_rollback() sem trans_start(), ou um teste que '
                . 'levantou exceção antes do fechamento são as causas usuais.'
            );
        }

        if ($db->trans_begin() === false) {
            $this->fail('A transação do teste não pôde ser aberta: trans_begin() retornou false.');
        }

        $openedDepth = Ci3Introspection::transactionDepth($db);

        if ($openedDepth !== 1) {
            $this->fail("trans_begin() retornou verdadeiro, mas o depth ficou em {$openedDepth} em vez de 1.");
        }

        // Depois do BEGIN, nunca antes: a reinstalação também tem de ser
        // descartada. Feita antes, um DELETE escapa da transação e sobrevive ao
        // rollback, que é o vazamento que a trait existe para impedir.
        if ($this->resetsBaselineData()) {
            $this->resetBaselineData($db);
        }
    }

    /**
     * Devolve a linha de base ao estado em que a montagem do banco a deixou.
     *
     * "Linha de base" é o que existe quando nada foi escrito ainda: as contas das
     * fixtures. Nenhuma outra tabela é tocada, e a escolha de `usuarios` não é
     * arbitrária — é a única que os testes alteram, porque LoginControllerTest
     * muda o `dataExpiracao` de uma conta. Quando outra passar a ser alterada, é
     * aqui que entra, e o guard de `logs` não serve de aviso para isso: ele só
     * enxerga o que sobreviveu a um commit.
     *
     * A comparação vai na chave primária, e não como segundo argumento:
     * `where('idUsuarios', '>', 0)` monta `idUsuarios = '>'`, não casa com nada e
     * ainda assim devolve `true`, que é a forma mais silenciosa de um DELETE não
     * apagar nada. Por isso o operador está dentro da string da chave.
     *
     * @see \Tests\Support\Database\TestFixtures::installUsers()
     */
    private function resetBaselineData(object $db): void
    {
        // `logs` crescendo é a assinatura de uma transação commitada em vez de
        // descartada. A conferência fica aqui, e não no setup, porque os dois
        // casos do TransactsDatabaseTest são a prova de que a trait funciona: se
        // alguma coisa limpasse `logs` entre eles, os dois passariam tanto com o
        // rollback quanto sem ele.
        $vazamento = $this->countLogs($db);

        if ($vazamento > 0) {
            $this->fail(
                "A tabela `logs` tem {$vazamento} linha(s) que sobreviveram à transação de um teste "
                . 'anterior. Uma transação foi commitada, ou um tearDown sem transação, ou um teste '
                . 'que levantou exceção antes do fechamento. Este arquivo não limpa `logs` de '
                . "propósito, porque o par que prova o rollback depende delas. Rode 'composer test:fresh' "
                . 'se a sujeira veio de fora da suíte.'
            );
        }

        $db->where('idUsuarios >', 0)->delete('usuarios');

        TestFixtures::installUsers();

        self::$baselineResets++;

        $restored = $db->count_all_results('usuarios');

        if ($restored !== TestFixtures::USER_COUNT) {
            $this->fail(
                sprintf(
                    'A reinstalação da linha de base deixou %d usuário(s) em vez de %d. ',
                    $restored,
                    TestFixtures::USER_COUNT
                )
                . 'O TestFixtures::installUsers() parou no meio, ou outro teste mexeu na tabela '
                . "sem transação. Rode 'composer test:fresh' para remontar o banco."
            );
        }
    }

    /**
     * Descreve a transação perdida, ou devolve null se ela ainda é nossa.
     *
     * A pergunta é "a nossa transação ainda existe?", e a resposta vem de
     * `Ci3Introspection::autocommit()`, que é a informação que o driver usa para
     * decidir isso e que o `_trans_depth` em 1 não é. O porquê inteiro, inclusive
     * por que INNODB_TRX e `@@in_transaction` não servem, está no método.
     *
     * O que este detector não pega: commit implícito por DDL. O MySQL encerra a
     * transação, mas o autocommit continua desligado e a leitura mente. É a mesma
     * razão pela qual teste com migration fica fora da trait, e a mensagem abaixo
     * diz isso para quem bater nela.
     */
    private function describeLostTransaction(object $db): ?string
    {
        if (Ci3Introspection::autocommit($db) === 0) {
            return null;
        }

        return 'A transação do teste foi commitada ou revertida pelo código sob '
            . 'teste: o autocommit desta conexão está ligado, e quem o religa é '
            . 'o commit ou o rollback do driver, não o fechamento do harness. '
            . 'O isolamento deste teste não valeu. O caminho conhecido que faz '
            . 'isto é Financeiro::excluirLancamento(), que chama trans_complete() '
            . 'e depois trans_rollback() no mesmo bloco: em produção o segundo é '
            . 'no-op no depth 0, e aqui ele derruba a transação do harness. '
            . 'Se o culpado for DDL, o commit foi implícito e este detector não '
            . 'o vê — nenhum teste com migration pode usar esta trait.';
    }

    /**
     * Quantas vezes a reinstalação da linha de base rodou nesta classe.
     *
     * Um contador, e não um log do que foi reinstalado, porque a pergunta que a
     * suíte não conseguia responder é "o gancho chegou a ser chamado?", e a
     * resposta é um número. Ele fecha o buraco que este arquivo registra: sem isto,
     * apagar a chamada em `setUpDatabaseTransaction()` deixa a suíte inteira verde,
     * porque o estado de `usuarios` dentro de um caso é o mesmo com e sem a
     * reinstalação — tudo roda em transação.
     *
     * Estático, e por necessidade: o gancho roda em `$this` e o teste que quer
     * conferir isso é um caso a parte, que não é a mesma instância. A leitura é por
     * método de instância, e não `TransactsDatabase::baselineResets()`: uma chamada
     * estática pelo nome do trait resolve para outra propriedade que a incremento
     * não toca, e devolve sempre zero. Cada classe que usa a trait tem a sua
     * própria, o que é o que interessa — a pergunta é sobre esta classe.
     */
    private static int $baselineResets = 0;

    protected function baselineResetCount(): int
    {
        return self::$baselineResets;
    }

    /**
     * Quantas linhas o último teste deixou em `logs`.
     *
     * Só o guard de resetBaselineData() usa, e o valor não é interessante por si:
     * interessa que ele seja maior que zero.
     */
    private function countLogs(object $db): int
    {
        return (int) $db->count_all_results('logs');
    }

    /**
     * Descarta tudo que o caso escreveu, aconteça o que acontecer com ele.
     *
     * O `finally` é o que evita a armadilha 3 acima quando o caso estoura: sem
     * ele, uma exceção deixa o autocommit desligado e o processo inteiro passa
     * a vazar estado em silêncio. É também por isso que a verificação da
     * transação roubada acontece antes do fechamento e apenas guarda a
     * mensagem — falhar ali interromperia o fechamento, que é justamente a
     * parte que não pode ser pulada.
     */
    #[After]
    protected function tearDownDatabaseTransaction(): void
    {
        $db = get_instance()->db;

        $diagnosis = $this->describeLostTransaction($db);

        try {
            $this->rollbackToDepthZero($db);
        } finally {
            $this->resetDriverAfterFailedClose($db);
        }

        if ($diagnosis !== null) {
            $this->fail($diagnosis);
        }
    }

    /**
     * Rola para trás até o depth zero, chamando o suficiente para chegar lá.
     *
     * O laço não é defensive-programming genérico: `trans_rollback()` só
     * emite ROLLBACK quando o depth é 1 (DB_driver.php:380), e só decrementa
     * acima disso. Uma chamada só deixaria a pilha em N-1 e nada seria
     * gravado no banco.
     */
    private function rollbackToDepthZero(object $db): void
    {
        while (($depth = Ci3Introspection::transactionDepth($db)) > 0) {
            if ($db->trans_rollback() === false) {
                $this->fail("trans_rollback() falhou com o depth em {$depth}; o fechamento parou antes de zerar.");
            }

            if (Ci3Introspection::transactionDepth($db) >= $depth) {
                $this->fail('trans_rollback() não decrementou o depth; o laço não progrediria.');
            }
        }
    }

    /**
     * Recoloca o driver em um estado em que o próximo teste começa limpo.
     *
     * No caminho normal `_trans_rollback()` já devolve o autocommit e zera o
     * depth, e este método não faz nada. Ele existe para o caminho em que o
     * fechamento falhou no meio, porque aí sobraria uma conexão com autocommit
     * desligado e depth > 0: todos os testes seguintes do processo escrevem
     * dentro de uma transação que ninguém abre nem fecha, e nenhum deles
     * reclama.
     */
    private function resetDriverAfterFailedClose(object $db): void
    {
        Ci3Introspection::closeDriverTransaction($db);
    }
}
