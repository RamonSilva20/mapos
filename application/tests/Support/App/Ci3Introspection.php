<?php

namespace Tests\Support\App;

use CI_Controller;
use Closure;

/**
 * As escritas onde a suíte precisa mexer em estado que o CI3 não abre.
 *
 * A suíte é in-process: o app boota uma vez dentro do bootstrap.php e cada caso
 * instancia o controller direto, porque reiniciar o ciclo de vida do CI3 por
 * teste não é possível. Isso expõe reentrância bugs do framework, e este arquivo é
 * a lista deles. Não é uma lista para fugir — sem estas escritas, o segundo
 * controller da suíte já nasce errado:
 *
 *   - Loader::model() retorna cedo em in_array($name, $this->_ci_models) ANTES de
 *     anexar o model. A lista sobrevive entre casos, então do segundo controller
 *     em diante $this->Some_model é null.
 *   - CI_Controller::__construct() faz self::$instance =& $this. O último
 *     controller continua apontado para lá, e Loader::database() resolve o $CI por
 *     ali: sem a volta, a suíte abre uma conexão nova a cada controller.
 *   - O driver conta a profundidade em _trans_depth, que é protected, e o CI3 só
 *     expõe trans_status(). Um rollback que falha deixa o contador alto, e o caso
 *     seguinte passa a rodar dentro de uma transação que ninguém abriu nem fecha.
 *
 * A quarta escrita desta lista, `TestApplication::resetSharedState()`, mora no
 * outro arquivo e não está aqui: ela é o que o bootstrap chama uma vez por
 * controller, e o que permite que estes três pontos existam como método em vez de
 * escape manual espalhado pelos testes.
 *
 * Por que Closure::bind e não Reflection: `Closure::call()` amarra ao escopo do
 * objeto, alcança protected e private sem mexer em visibilidade, e o escopo some no
 * fim da chamada. Reflection precisaria de setAccessible() — global e permanente — e
 * um nome de propriedade digitado errado viraria um PropertyNotFoundException em vez
 * de um teste vermelho sobre o que a suíte tentava medir.
 */
final class Ci3Introspection
{
    /**
     * A profundidade de transação do driver, que é protected.
     *
     * O CI3 não tem trans_depth() na API pública — DB_driver.php só expõe
     * trans_status() — e é este número que decide se a suíte ainda está dentro de
     * uma transação.
     */
    public static function transactionDepth(object $db): int
    {
        return (function (): int {
            return $this->_trans_depth;
        })->call($db);
    }

    /**
     * O autocommit da conexão, que é como o CI3 marca a transação aberta.
     *
     * O driver não expõe isto, e era a informação que três lugares reescreviam à
     * mão — a trait e dois testes, com dois textos diferentes para o mesmo SELECT
     * (`CAST(@@autocommit AS UNSIGNED)` num, `@@autocommit` no outro). O cast é
     * redundante: o (int) abaixo faz o mesmo trabalho, e uma divergência entre os
     * dois textos seria uma divergência silenciosa, porque as duas versões devolvem
     * o mesmo número.
     *
     * O CI3 faz a transação como `autocommit(0)` seguido de `START TRANSACTION`
     * (mysqli_driver.php:347), e tanto `_trans_commit()` quanto `_trans_rollback()`
     * terminam religando o autocommit (linhas 362 e 380). Desligado equivale, de
     * fora, a "o harness ainda está na própria transação" — e é essa leitura, e
     * não `_trans_depth`, que denuncia uma transação perdida.
     *
     * O que a leitura não pega: commit implícito por DDL. O MySQL encerra a
     * transação, mas o autocommit continua desligado e a leitura mente.
     *
     * Detalhe do protocolo: com a conexão em buffering, um ROLLBACK do caso
     * anterior deixaria um resultado não lido e a próxima consulta falharia com
     * "Cannot execute queries while other unbuffered queries are active". Por isso
     * a conversão para int acontece no PHP, sobre a linha única que o SELECT
     * devolve, sem uma segunda ida ao servidor entre a medição e o seu uso.
     */
    public static function autocommit(object $db): int
    {
        $row = $db->query('SELECT @@autocommit AS autocommit')->row_array();

        return (int) ($row['autocommit'] ?? 1);
    }

    /**
     * Devolve o driver ao estado fechado, depois de um rollback que não rodou.
     *
     * `_trans_depth` sozinho não basta: o mysqli fica com autocommit desligado
     * enquanto a transação é aberta, e zerar o contador sem religar o autocommit
     * deixa a conexão num estado que o próximo caso não espera e que nenhum assert
     * sinaliza.
     *
     * Só o mysqli é tratado, e um driver que não seja ele lança em vez de ser
     *tratado pela metade. A versão anterior fazia o `instanceof` pular o
     * `autocommit(true)` e zerar o contador assim mesmo, para qualquer driver: o
     * contador voltava a zero, o método devolvia nada, e o `tearDown` terminava
     * tendo mentido — a conexão continuava com autocommit desligado e com a
     * transação aberta, que é o estado que este método existe para desfazer. Um
     * driver diferente do mysqli é configuração não suportada, e falhar alto custa
     * um erro na hora da configuração, contra uma conexão envenenada que só aparece
     * como um teste que falha três linhas depois por um motivo que aponta para outro
     * lugar.
     *
     * O PDO é justamente o driver que o `TransactsDatabase` pode encontrar, porque
     * `config/database.php` configurá-lo é uma escolha de quem instala, e por isso
     * ele é reconhecido e fechado: `inTransaction()` é a consulta de estado
     * equivalente ao `_trans_depth` do mysqli, e o PDO não tem `autocommit()` para
     * religar porque volta a auto-commit sozinho quando a transação termina.
     */
    public static function closeDriverTransaction(object $db): void
    {
        if (self::transactionDepth($db) === 0) {
            return;
        }

        (function (): void {
            if ($this->conn_id instanceof \mysqli) {
                $this->conn_id->autocommit(true);
            }

            if (! $this->conn_id instanceof \mysqli && ! $this->conn_id instanceof \PDO) {
                throw new \RuntimeException(
                    'closeDriverTransaction() só conhece mysqli e PDO, e este banco usa '
                        . get_debug_type($this->conn_id) . '. Zerar o contador sem fechar a '
                        . 'transação do driver deixa a conexão num estado que o próximo caso não '
                        . 'espera, e nenhum assert sinaliza. Trocar o driver em '
                        . 'application/config/database.php exige tratar o driver neste método.'
                );
            }

            // O PDO não tem autocommit() para religar: `inTransaction()` é o próprio
            // estado, e ele se desfaz no commit/rollback. Não há o que escrever além
            // do contador, que é o que o CI3 consulta para saber se está transacionando.
            $this->_trans_depth = 0;
        })->call($db);
    }

    /**
     * Esvazia o registro de models do Loader, que é protected.
     *
     * Sem isto, `Loader::model()` sai no in_array() e devolve null, e o primeiro
     * sintoma é um teste que passa com o model null e falha três linhas depois por
     * um motivo que aponta para outro lugar.
     */
    public static function clearModelRegistry(object $loader): void
    {
        (function (): void {
            $this->_ci_models = [];
        })->call($loader);
    }

    /**
     * Aponta CI_Controller::$instance para o superobjeto, que é private static.
     *
     * O bind é feito sem objeto (`null`) e com o escopo da CI_Controller, que é o
     * que dá acesso à propriedade estática. Precisa rodar depois de cada
     * controller construído, não só no fim do caso.
     *
     * O parâmetro é `object` e não `?object` porque quem chama já conferiu: um
     * superobjeto nulo é a condição que o `boot()` transforma em exceção, e aceitar
     * null aqui só permitiria que um chamador futuro reintroduzisse a mesma
     * verificação num lugar a um arquivo de distância — que é o que o
     * `RuntimeException` dentro deste método evitava.
     */
    public static function restoreControllerInstance(object $super): void
    {
        Closure::bind(
            function (object $super): void {
                self::$instance = $super;
            },
            null,
            CI_Controller::class
        )($super);
    }

    /**
     * Tira do Loader os nomes que ele copiou de um controller já morto.
     *
     * O `Loader::_ci_load()` é quem dá vida a `$this` dentro de uma view: ele
     * copia para o Loader, por referência, as propriedades do controller — e só
     * copia a que ainda não existe (`isset($this->$chave)`). Como o Loader é
     * singleton, a primeira view do processo congela aí o retrato do primeiro
     * controller, e os próximos nunca o atualizam.
     *
     * O sintoma é sutil. Um controller que carrega uma library no meio do método
     * (como o `pagination` do `Auditoria::index()`) ganha a PROPRIEDADE nova do
     * controller, e o `$this->pagination` do controller funciona; mas a view
     * renderizada logo depois lê o retrato velho do Loader, que aponta para o
     * objeto do controller ANTERIOR — com o estado de lá, `total_rows` de um
     * teste que já virou. Quem exercita a tela pela segunda vez no mesmo processo
     * vê o pagination somindo sem que nada no código do controller explique.
     *
     * As propriedades próprias do Loader são todas `_ci_*` (protegidas); o que
     * sobra — `pagination`, `session`, `permission`, os models, `db` — é o
     * retrato a ser esquecido. Sem rodar isto antes de cada controller, a suíte
     * só renderiza layout uma vez por processo: a primeira renderização congela
     * o retrato, e todas as outras leem o objeto da primeira.
     *
     * O guard `strpos($name, '_ci_')` não é defesa teórica: `Closure::call()`
     * reajusta o escopo para a classe do objeto, então o `get_object_vars()`
     * aqui enxerga as propriedades protegidas do Loader. Sem o filtro, o unset
     * levaria junto `_ci_models` e `_ci_classes`, e o Loader que deveria
     * esquecer o controller velho passaria a esquecer também os models que ele
     * carrega.
     */
    public static function resetLoaderViewAliases(object $loader): void
    {
        (function (): void {
            foreach (array_keys(get_object_vars($this)) as $name) {
                if (strpos($name, '_ci_') === 0) {
                    continue;
                }

                unset($this->$name);
            }
        })->call($loader);
    }
}
