<?php

/**
 * O boot compartilhado pelos scripts de linha de comando da suíte.
 *
 * Os scripts da pasta bin — setup-db.php, check-schema-parity.php,
 * drop-worker-databases.php e probe-utf8mb4-down.php —
 * precisam da mesma coisa antes de qualquer trabalho: recusar se não vierem da
 * linha de comando, e resolver as credenciais do banco de testes. As cópias
 * carregavam também o mesmo comentário explicando por que a guarda lança em vez
 * de sair, e era esse comentário que mais rendia: escrito três vezes, ele passou
 * a descrever o comportamento de todos em vez do de um.
 *
 * A promessa que a função cumpre é estreita e é esta: uma falha NESTA função sai
 * como mensagem em STDERR e código 1. Ela não é o try/catch de cada script. Os
 * scripts que criam banco de worker são os que são responsáveis pela borda
 * deles: derrubam o que criaram num `finally` que nenhum exit() pode pular, e
 * usam exceções em vez de exit() dentro da região guardada. O que sobra é o
 * caso documentado — um `exit()` cru cravado no boot profundo do CI3 ainda pula
 * o finally — e esse banco se resolve por recreate() na próxima execução e por
 * test:clean no fim.
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
