<?php

namespace Tests\Support;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;

/**
 * Envolve cada teste numa transação e descarta no fim.
 *
 * O equivalente ao DatabaseTransactions do Laravel, e pelo mesmo motivo de ser
 * opt-in: `use TransactsDatabase;` numa classe de teste, nada herdado. Uma
 * classe que não escreve no banco não tem o que ganhar aqui, e paga um BEGIN
 * e um ROLLBACK a cada caso.
 *
 * O que isto resolve na prática é o vazamento. `log_info()` grava na tabela
 * `logs` e o `Login::verificarLogin()` chama log_info() em todo login bem
 * escorado, então a suíte deixava uma linha para trás por execução. Antes
 * disto o `logs` já estava com 5 linhas e só crescia. A outra forma de não
 * vazar é salvar e restaurar cada valor na mão, que é o que o teste de conta
 * sem expiração fazia, e que um `fail()` no meio do caminho transformava em
 * lixo permanente.
 *
 * A transação roda na conexão do CI3 (`get_instance()->db`), que é a mesma que
 * o `database` do autoload abriu no boot e que a suíte in-process compartilha
 * entre todos os testes do processo. Por isso o fechamento é defensivo: uma
 * transação que vaza de um teste para o outro é exatamente o tipo de falha que
 * não dá mensagem nenhuma, e só aparece como um teste que passa sozinho.
 *
 * Três armadilhas do CI3 que o fechamento trata, e que não têm equivalente no
 * Laravel porque lá existe SAVEPOINT:
 *
 * 1. `trans_begin()` com `_trans_depth` já maior que zero só incrementa o
 *    contador, sem SAVEPOINT. Um controller sob teste que abre a própria
 *    transação não consegue commitar a nossa, porque o commit só acontece no
 *    depth 1. Mas ele também não consegue reverter só o que dele é: um
 *    rollback interno derruba a transação inteira, o que é o comportamento
 *    correto em produção e o errado para o isolamento do teste.
 *
 * 2. `trans_rollback()` só fala com o banco quando o depth é exatamente 1
 *    (DB_driver.php:380). Nos níveis acima ele só decrementa. Um controller
 *    que deixe a pilha em 3 exige três chamadas até chegar no ROLLBACK de
 *    verdade, então um rollback único não desfaz nada.
 *
 * 3. `_trans_rollback()` e `_trans_commit()` ligam de novo o autocommit
 *    (mysqli_driver.php:362). Se o fechamento não rodar, a conexão fica com
 *    autocommit desligado e todo teste seguinte do processo passa a escrever
 *    dentro de uma transação que ninguém fecha. Por isso o autocommit é
 *    religado mesmo quando a transação já desapareceu.
 *
 * O que esta trait NÃO cobre, e é preciso saber antes de adotá-la:
 *
 *   - DDL. `CREATE`, `ALTER`, `DROP` e `TRUNCATE` fazem commit implícito no
 *     MySQL e derrubam a transação sem aviso. Por isso
 *     TestApplicationTest::testMigrateLeavesTheOutputBufferLevelUntouched()
 *     não usa isto: ele roda Tools::migrate(), que hoje não executa nada
 *     porque todas as migrations já estão aplicadas — mas isso é uma leitura
 *     do estado atual, não uma garantia, e a primeira migration nova
 *     quebraria o isolamento do próprio teste sem nenhuma mensagem.
 *
 *   - MyISAM, que não tem transação. Não é caso aqui: as 28 tabelas do banco
 *     de teste são InnoDB, e o create_base força o motor com ALTER TABLE
 *     depois de criar cada tabela. Vale reconferir se um dia alguma tabela
 *     entrar pelo dump com o motor padrão errado, porque a transação vira
 *     no-op e passa sem avisar.
 *
 *   - Grupos de conexão além do `default`. O `autoload['libraries']` do CI3
 *     tem um grupo só, e é ele que esta trait usa; um
 *     `$this->load->database('outro')` abriria uma segunda conexão que a
 *     transação não alcança.
 *
 * ## A reinstateção da linha de base
 *
 * A transação desfaz o que o caso escreve, mas não desfaz o que a execução
 * ANTERIOR deixou, e é aí que entra o opt-in. Uma classe que sobrescreve
 * `protected function resetsBaselineData(): bool` para devolver true recebe,
 * antes de cada caso e dentro da transação que a trait acabou de abrir:
 *
 *   1. a conferência de que `logs` está vazia;
 *   2. o DELETE dos usuários e a reinstallação deles por TestFixtures.
 *
 * Opt-in, e não automático, por dois motivos. O primeiro é de custo: uma
 * classe que não escreve no banco não tem o que ganhar aqui, e paga o preço em
 * todo caso. O segundo é o TransactsDatabaseTest, que precisa que `logs` NÃO
 * seja limpa entre os seus dois casos: é justamente a linha que sobrou ou não
 * que prova que a trait descarta em vez de commitar. Limpar `logs` aqui não
 * custaria nada e tornaria essa prova uma tautologia.
 *
 * Um limite deste opt-in que vale registrar: a chamada em si é invisível de
 * dentro da suíte. Como tudo roda dentro de transação, o estado de `usuarios` no
 * corpo de um caso é o das fixtures com ou sem a reinstateção — desligá-la por
 * completo deixa a suíte inteira verde, o que já foi verificado. O
 * BaselineDataResetTest por isso chama o método diretamente, e o que pega a
 * direção perigosa (commit em vez de rollback) é o guard de `logs`. A
 * reinstalação que chega de uma execução anterior é barrada pelo
 * setup-db.php, que confere as três contas antes de reaproveitar o banco.
 */
trait TransactsDatabase
{
    /**
     * A classe pede a reinstateção da linha de base antes de cada caso.
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

        $herdado = $this->transactionDepth($db);

        if ($herdado !== 0) {
            $this->fail(
                "A transação do teste anterior não foi fechada (depth {$herdado}). "
                . 'Um tearDown sem transação, um controller que chamou '
                . 'trans_rollback() sem trans_start(), ou um teste que '
                . 'levantou exceção antes do fechamento são as causas usuais.'
            );
        }

        if ($db->trans_begin() === false) {
            $this->fail('A transação do teste não pôde ser aberta: trans_begin() retornou false.');
        }

        $aberto = $this->transactionDepth($db);

        if ($aberto !== 1) {
            $this->fail("trans_begin() retornou verdadeiro, mas o depth ficou em {$aberto} em vez de 1.");
        }

        // Depois do BEGIN, nunca antes: a reinstateção também tem de ser
        // descartada. Feita antes, um DELETE escapa da transação e sobrevive ao
        // rollback, que é o vazamento que a trait existe para impedir.
        if ($this->resetsBaselineData()) {
            $this->resetBaselineData($db);
        }
    }

    /**
     * Devolve a linha de base ao estado em que a montagem do banco a deixou.
     *
     * "Linha de base" é o que existe quando nada foi escrito ainda: as três
     * contas de usuário das fixtures. Nenhuma outra tabela é tocada, e a escolha
     * de `usuarios` não é arbitrária — é a única que os testes alteram, porque
     * `LoginControllerTest` muda o `dataExpiracao` de uma conta. Quando outra
     * passar a ser alterada, é aqui que entra, e a conferência de `logs` abaixo
     * não serve de aviso para isso: ela só enxerga o que sobreviveu a um commit.
     *
     * O DELETE, e não o TRUNCATE, porque TRUNCATE é DDL: ele faz commit
     * implícito e derrubaria a transação que a trait acabou de abrir, deixando o
     * `trans_begin()` de antes sem efeito e os testes seguintes escrevendo em
     * autocommit. O efeito de um DELETE InnoDB dentro de transação é o mesmo e
     * o custo é irrelevante para três linhas.
     *
     * E a comparação na chave primária, e não um `where('1 = 1')`: o CI3 recusa
     * um DELETE sem WHERE (DB_query_builder.php:2192, `db_del_must_use_where`),
     * e `idUsuarios >` diz o que quer dizer sem depender do query builder
     * deixar passar uma condição constante. O operador vai na chave e não como
     * segundo argumento — `where('idUsuarios', '>', 0)` monta `idUsuarios = '>'`,
     * não casa com nada e ainda assim devolve `true`, que é a forma mais
     * silenciosa de um DELETE não apagar nada.
     */
    private function resetBaselineData(object $db): void
    {
        // `logs` crescendo é a assinatura de uma transação que foi commitada em
        // vez de descartada, e esta suíte grava uma linha por login bem
        // escorado. A conferência é aqui, e não no setup, porque os dois casos
        // do TransactsDatabaseTest são a prova de que a trait funciona: se
        // alguma coisa limpasse `logs` entre eles, os dois passariam tanto com
        // o rollback quanto sem ele.
        //
        // Limpar aqui esconderia justamente o bug que este arquivo existe para
        // pegar, então a falha aponta a saída em vez de mascará-la.
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

        $restaurados = $db->count_all_results('usuarios');

        if ($restaurados !== 3) {
            $this->fail(
                "A reinstateção da linha de base deixou {$restaurados} usuário(s) em vez de 3. "
                . "O TestFixtures::installUsers() parou no meio, ou outro teste mexeu na tabela "
                . "sem transação. Rode 'composer test:fresh' para remontar o banco."
            );
        }
    }

    /**
     * @return int quantas linhas o último teste deixou em `logs`
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

        $roubada = $this->describeLostTransaction($db);

        try {
            $this->rollbackToDepthZero($db);
        } finally {
            $this->resetDriverAfterFailedClose($db);
        }

        if ($roubada !== null) {
            $this->fail($roubada);
        }
    }

    /**
     * Descreve a transação perdida, ou devolve null se ela ainda é nossa.
     *
     * O CI3 não expõe o `_trans_depth` e o `trans_status()` mente quando
     * `trans_strict` está desligado, que devolve o estado anterior para
     * permitir o próximo grupo. A pergunta que interessa é outra — a nossa
     * transação ainda existe? — e a resposta vem de `@@autocommit`, porque é
     * exatamente a informação que o driver usa para decidir isso.
     *
     * O CI3 implementa a transação como `autocommit(0)` seguido de
     * `START TRANSACTION` (mysqli_driver.php:347), e tanto `_trans_commit()`
     * quanto `_trans_rollback()` terminam religando o autocommit
     * (mysqli_driver.php:362 e 380). Então o autocommit estar desligado é
     * equivalente, observável de fora, a "o harness ainda está dentro da
     * própria transação", e o `_trans_depth` continuar em 1 é o contador
     * mentindo.
     *
     * As alternativas que parecem melhores não funcionam:
     *
     *   - information_schema.INNODB_TRX não aparece. Uma transação que só fez
     *     `START TRANSACTION` não se registra ali, e mesmo depois de um DML o
     *     registro continua ausente, então a consulta responde 0 com a
     *     transação aberta. Medido no MySQL 8.4 deste projeto.
     *
     *   - `@@in_transaction` seria o nome exato do que se quer, mas é
     *     variável do MariaDB. No MySQL 8.4 ela não existe, e o CI roda MySQL.
     *
     * O que este detector não pega: commit implícito por DDL. O MySQL encerra a
     * transação, mas o autocommit continua desligado, então a leitura mente.
     * É a mesma razão pela qual teste com migration fica fora da trait, e o
     * texto de `$roubada` diz isso para quem bater nele.
     */
    private function describeLostTransaction(object $db): ?string
    {
        $row = $db->query('SELECT @@autocommit AS autocommit')->row_array();

        $autocommit = (int) ($row['autocommit'] ?? 1);

        if ($autocommit === 0) {
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
     * Rola para trás até o depth zero, chamando o suficiente para chegar lá.
     *
     * O laço não é defensive-programming genérico: `trans_rollback()` só
     * emite ROLLBACK quando o depth é 1 (DB_driver.php:380), e só decrementa
     * acima disso. Uma chamada só deixaria a pilha em N-1 e nada seria
     * gravado no banco.
     */
    private function rollbackToDepthZero(object $db): void
    {
        while (($depth = $this->transactionDepth($db)) > 0) {
            if ($db->trans_rollback() === false) {
                $this->fail("trans_rollback() falhou com o depth em {$depth}; o fechamento parou antes de zerar.");

                return;
            }

            if ($this->transactionDepth($db) >= $depth) {
                $this->fail('trans_rollback() não decrementou o depth; o laço não progrediria.');

                return;
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
        if ($this->transactionDepth($db) === 0) {
            return;
        }

        (function (): void {
            if ($this->conn_id instanceof \mysqli) {
                $this->conn_id->autocommit(true);
            }

            $this->_trans_depth = 0;
        })->call($db);
    }

    /**
     * Lê o `_trans_depth` do driver, que é protected.
     *
     * O CI3 não tem `trans_depth()` na API pública (DB_driver.php só expõe
     * trans_status()), e Reflection seria mais frágil que uma closure
     * amarrada ao escopo do driver — a mesma técnica que TestApplication usa
     * para o `_ci_models` do Loader.
     */
    private function transactionDepth(object $db): int
    {
        return (function (): int {
            return $this->_trans_depth;
        })->call($db);
    }
}
