<?php

/**
 * Bootstrap in-process da suíte de testes.
 *
 * O CodeIgniter 3 não foi feito para ser reentrante: incluir o index.php
 * executa o ciclo completo da requisição (despacha o controller e exibe a saída).
 * Por isso o app é inicializado uma única vez, aqui, e os testes instanciam os
 * controllers diretamente sobre a instância viva.
 *
 * A resolução do ambiente e o boot são dois passos, em duas classes, porque têm
 * ciclos de vida diferentes: Tests\Support\Database\TestDatabase resolve as credenciais e
 * a guarda do '_test', e Tests\Support\App\TestApplication sobe o CI3 e conserta o
 * estado reentrante. O setup-db.php e o check-schema-parity.php precisam
 * exatamente da mesma coisa e já copiaram isso duas vezes antes.
 *
 * O .env é opcional. Em desenvolvimento ele existe e é respeitado; no CI não
 * existe, e tudo vem do ambiente.
 */

use Tests\Support\App\TestApplication;
use Tests\Support\Clone\TestSchemaClone;
use Tests\Support\Database\TestDatabase;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$test = TestDatabase::fromEnvironment();

// O clone do banco do worker, e só quando há worker.
//
// Este arquivo roda uma vez por processo, e o ParaTest publica um TEST_TOKEN
// diferente em cada um. O banco é o único estado que o processo não carrega, então
// sem esta cópia todos escreveriam no mesmo `usuarios` e a reinstalação da linha de
// base — um DELETE seguido de INSERT dentro da transação do caso — seguraria um
// X-lock que o outro só descobre ao estourar em innodb_lock_wait_timeout.
//
// Aqui, e não depois do boot, porque o autoloader 'database' do index.php conecta
// durante o boot: o banco do worker precisa existir antes, e não há como corrigir
// depois sem deixar a conexão do CI3 apontada para o modelo.
//
// Sem TEST_TOKEN — o PHPUnit normal, o setup-db.php e o check-schema-parity.php —
// o caminho serial não paga nem uma linha por isto: a cópia não acontece e
// TestSchemaClone nem é construído. O `if` é o que garante isso; o
// ensureWorkerDatabase() também recusaria, porque sem token o nome efetivo é o do
// modelo e os dois argumentos seriam iguais.
$testToken = TestDatabase::parallelToken();

if ($testToken !== null) {
    (new TestSchemaClone($test))->ensureWorkerDatabase($test->database(), $test->templateDatabase());
}

TestApplication::boot();

register_shutdown_function(static function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
});
