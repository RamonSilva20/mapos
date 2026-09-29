<?php

/**
 * O boot compartilhado pelos scripts de linha de comando da suíte.
 *
 * Os três scripts — setup-db.php, check-schema-parity.php e drop-worker-databases.php
 * — precisam da mesma coisa antes de qualquer trabalho: recusar se não vierem da
 * linha de comando, e resolver as credenciais do banco de testes. As três cópias
 * carregavam também o mesmo comentário de três linhas explicando por que a guarda
 * lança em vez de sair, e era esse comentário que mais rendia: escrito três vezes,
 * ele passou a descrever o comportamento dos três em vez do de um.
 *
 * A promessa que a função cumpre é estreita e é esta: uma falha NESTA função sai
 * como mensagem em STDERR e código 1. Ela não é o try/catch de cada script. Uma
 * `RuntimeException` de `recreate()` continua sendo fatal de PHP, e cada script que
 * chama `recreate()` é responsável pela borda dele. A alternativa — um try/catch
 * global em volta do corpo inteiro de cada script — é o que faria a função acima
 * parecer maior do que é, e ela é pequena de propósito.
 */

use Tests\Support\Database\DatabaseGuard;
use Tests\Support\Database\TestDatabase;

/**
 * A instância do banco de testes, ou a saída com código 1.
 *
 * A guarda recusa lançando, e não saindo, para poder ser testada
 * (DatabaseGuardTest). Aqui, na borda do script, a exceção volta a ser o que o
 * operador esperava de um `exit()`.
 */
function bootTestDatabase(): TestDatabase
{
    try {
        DatabaseGuard::assertCommandLine();

        return TestDatabase::fromEnvironment();
    } catch (RuntimeException $exception) {
        fwrite(STDERR, $exception->getMessage() . PHP_EOL);
        exit(1);
    }
}
