<?php

/**
 * Monta o banco de testes. Idempotente: pode rodar antes de cada `composer test`.
 *
 * O schema vem da cadeia de migrações, e não do banco.sql, por dois motivos.
 *
 * O primeiro é cobertura: é assim que o banco de produção de quem já usa o
 * sistema foi construído, e é a única forma de a suíte enxergar uma migration
 * quebrada. O banco.sql tem 28 CREATE TABLE IF NOT EXISTS e nenhum DROP, então
 * importá-lo nunca exercita uma única linha de migration.
 *
 * O segundo é consistência: install/do_install.php importa o banco.sql e o
 * Tools::migrate() roda as migrações, e nada comparava os dois resultados. O
 * check-schema-parity.php faz essa comparação e roda no CI; o banco.sql continua
 * sendo a fonte do instalador.
 *
 * A referência (permissão, configuração e administrador) vem das seeds
 * canônicas, as mesmas do Tools::seed().
 */

use Tests\Support\TestApplication;
use Tests\Support\TestDatabase;
use Tests\Support\TestFixtures;

require_once __DIR__ . '/../../vendor/autoload.php';

TestDatabase::assertCommandLine();

$test = TestDatabase::fromEnvironment();
$database = $test->database();

$echo = static function (string $message): void {
    fwrite(STDOUT, $message . PHP_EOL);
};

$echo("Montando o banco '{$database}' a partir das migrações.");

// O banco precisa existir antes do index.php subir: o autoloader de 'database'
// conecta durante o boot. O DROP é o que torna isto realmente idempotente — sem
// ele, a segunda execução herda tudo que a primeira deixou no schema.
$test->recreate($database);

TestApplication::boot();

$migrationError = TestApplication::migrate();

if ($migrationError !== '') {
    fwrite(STDERR, "As migrações falharam: {$migrationError}\n");
    exit(1);
}

$echo('Esquema criado pelas migrações.' . PHP_EOL);

$installed = TestFixtures::install();

$echo(sprintf('Reference data: %s.', implode(', ', $installed)));

// Confere o resultado, para uma montagem quebrada falhar aqui e não no meio de
// um teste com uma mensagem sem pistas.
$usuarios = get_instance()->db->count_all_results('usuarios');

if ($usuarios !== 3) {
    fwrite(STDERR, "Esperava 3 usuários e encontrei {$usuarios}.\n");
    exit(1);
}

$echo('3 usuários disponíveis: admin@admin.com, inativo@admin.com, expirado@admin.com (senha 123456).');
