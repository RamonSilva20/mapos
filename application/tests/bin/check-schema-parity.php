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
 *
 * Ele prova uma coisa — a FIDELIDADE DA IMPORTAÇÃO: o schema que um banco.sql
 * recém-importado dá é o schema que a cadeia de migrations dá. Antes ele também
 * provava a REPRODUTIBILIDADE do arquivo (banco.sql byte a byte igual à saída do
 * gerador), mas essa metade foi removida: o render do dump vinha do SHOW CREATE
 * de um servidor específico, e a mesma versão do MySQL em execuções diferentes
 * (servidor nativo contra a imagem oficial) denunciava a mesma coluna de duas
 * formas só de render. Como o instalador vai passar a criar o banco pelas
 * migrations em vez de importar o banco.sql, a metade semântica ficou como
 * guarda da transição: enquanto o arquivo ainda é importado, ele não pode
 * divergir da cadeia.
 */

use Tests\Support\App\TestApplication;
use Tests\Support\Database\DatabaseGuard;
use function Tests\Support\Database\schemaDiffBetween;
use Tests\Support\Database\SchemaReader;

use function Tests\Support\Database\schemaTableDiffBetween;
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
// workerDatabaseName() — a mesma função que o clone usa.
$dumpName = DatabaseGuard::workerName('mapos_parity_dump');
$migrationName = DatabaseGuard::workerName('mapos_parity_migration');

$diff = null;

// A borda: os dois bancos de worker são derrubados num `finally` que nenhum
// exit() pode pular, e a região guardada não tem exit() nem fail() — só
// exceções. O catch converte exceção em uma linha em STDERR + código 1. Um
// `exit()` cru aqui dentro (de um show_error() profundo no boot, por exemplo)
// continua podendo pular o finally, e é o caso documentado: o banco que sobrar
// se resolve por recreate() na próxima execução e por test:clean no fim.
try {
    try {
        // --- Lado 1: o que o install/do_install.php produz, a partir do banco.sql ---

        $current = file_get_contents($rootPath . '/banco.sql');
        if ($current === false) {
            throw new RuntimeException('banco.sql não pôde ser lido.');
        }

        $dumpPdo = $test->recreate($dumpName);
        $dumpPdo->exec($current);
        $dumpSchema = $readSchema($dumpPdo, $dumpName);
        $dumpCollations = SchemaReader::tableCollations($dumpPdo, $dumpName);

        // --- Lado 2: o que a cadeia de migrações produz ---

        // O banco precisa existir antes do index.php subir: o autoloader de 'database'
        // conecta durante o boot.
        $test->recreate($migrationName);

        TestApplication::boot($migrationName);

        $migrationError = TestApplication::migrate();

        if ($migrationError !== '') {
            throw new RuntimeException("As migrações falharam: {$migrationError}");
        }

        $chainPdo = $test->pdo($migrationName);
        $migrationSchema = $readSchema($chainPdo, $migrationName);
        $migrationCollations = SchemaReader::tableCollations($chainPdo, $migrationName);

        $diff = schemaDiffBetween($dumpSchema, $migrationSchema);
        $tableDiff = schemaTableDiffBetween($dumpCollations, $migrationCollations);

        $diff = array_merge($diff, $tableDiff);
    } finally {
        $test->drop($dumpName);
        $test->drop($migrationName);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'O gate falhou: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}

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
fwrite(STDERR, "O banco.sql divergiu da cadeia de migrations. Se a mudança foi feita por migration, o dump precisa acompanhar.\n");

exit(1);
