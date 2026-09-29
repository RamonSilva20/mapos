<?php

/**
 * Compara o schema que sai do banco.sql com o que sai da cadeia de migrações.
 *
 * Uso: php application/tests/bin/check-schema-parity.php
 *
 * O projeto tem dois caminhos de instalação independentes: o install/do_install.php
 * importa o banco.sql, e o Tools::migrate() roda a cadeia de migrações. Uma
 * instalação nova e uma instalação atualizada precisam terminar com o mesmo
 * schema, senão o mesmo código se comporta de jeito diferente dependendo de como
 * o sistema foi instalado — e nada mais denuncia isso.
 *
 * Este script é a barreira que faz a divergência aparecer no CI, em vez de
 * aparecer anos depois num relatório financeiro.
 */

use Tests\Support\App\TestApplication;
use Tests\Support\Database\DatabaseGuard;
use function Tests\Support\Database\schemaDiffBetween;
use Tests\Support\Database\SchemaReader;

use Tests\Support\Database\TestDatabase;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/lib/_boot.php';
require_once __DIR__ . '/lib/schema-diff.php';

$test = bootTestDatabase();

$rootPath = TestDatabase::rootPath();

$readSchema = static fn (PDO $pdo, string $database): array => SchemaReader::applicationSchema($pdo, $database);

// Os nomes derivados também precisam terminar em '_test', porque o mesmo guard
// protege o DROP.
//
// O token do worker entra no nome pelo mesmo motivo do clone: sem ele, dois
// processos de paridade rodando ao mesmo tempo recriam os mesmos dois bancos e
// cada um lê o schema do meio da reconstrução do outro. O token vem antes do
// sufixo, que é o que assertDatabaseNameIsSafe() exige, e o nome é montado por
// workerDatabaseName() — a mesma função que o clone usa, para as duas metades
// do gate não saberem cada uma a sua regra.
// `solo` é o token da execução de processo único, o mesmo que o clone usa: sem
// TEST_TOKEN não há isolamento a fazer, e o nome precisa ser estável para o
// gate poder ser repetido.
$token = TestDatabase::parallelToken() ?? 'solo';

$dumpName = DatabaseGuard::workerDatabaseName('mapos_parity_dump', $token);
$migrationName = DatabaseGuard::workerDatabaseName('mapos_parity_migration', $token);

// --- Lado 1: o que o install/do_install.php produz, a partir do banco.sql ---

$dumpPdo = $test->recreate($dumpName);
$dumpPdo->exec((string) file_get_contents($rootPath . '/banco.sql'));
$dumpSchema = $readSchema($dumpPdo, $dumpName);

// --- Lado 2: o que a cadeia de migrações produz ---

// O banco precisa existir antes do index.php subir: o autoloader de 'database'
// conecta durante o boot.
$test->recreate($migrationName);

TestApplication::boot($migrationName);

$migrationError = TestApplication::migrate();
$migrationSchema = $readSchema($test->pdo($migrationName), $migrationName);

$test->drop($dumpName);
$test->drop($migrationName);

if ($migrationError !== '') {
    fwrite(STDERR, "As migrações falharam: {$migrationError}\n");
    exit(1);
}

$diff = schemaDiffBetween($dumpSchema, $migrationSchema);

if ($diff === []) {
    fwrite(STDOUT, sprintf(
        "Schema em paridade: %d tabelas conferidas entre banco.sql e a cadeia de migrações.\n",
        count($dumpSchema)
    ));
    exit(0);
}

sort($diff);

fwrite(STDERR, sprintf("Schema divergente (%d diferenças):\n\n", count($diff)));

foreach ($diff as $line) {
    fwrite(STDERR, "  {$line}\n");
}

fwrite(STDERR, "\n");

exit(1);
