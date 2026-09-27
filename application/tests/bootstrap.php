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
 * ciclos de vida diferentes: Tests\Support\TestDatabase resolve as credenciais e
 * a guarda do '_test', e Tests\Support\TestApplication sobe o CI3 e conserta o
 * estado reentrante. O setup-db.php e o check-schema-parity.php precisam
 * exatamente da mesma coisa e já copiaram isso duas vezes antes.
 *
 * O .env é opcional. Em desenvolvimento ele existe e é respeitado; no CI não
 * existe, e tudo vem do ambiente.
 */

use Tests\Support\TestApplication;
use Tests\Support\TestDatabase;

require_once dirname(__DIR__) . '/vendor/autoload.php';

TestDatabase::fromEnvironment();
TestApplication::boot();

register_shutdown_function(static function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
});
