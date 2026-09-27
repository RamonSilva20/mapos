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

use Tests\Support\TestApplication;
use Tests\Support\TestDatabase;

require_once __DIR__ . '/../../vendor/autoload.php';

TestDatabase::assertCommandLine();

$rootPath = TestDatabase::rootPath();
$test = TestDatabase::fromEnvironment();

/**
 * Lê o schema de um banco como mapa table => column => type.
 *
 * Só a definição da coluna entra na comparação. Charset, collation e a ordem das
 * colunas mudam entre o dump e o dbforge sem significar divergência de schema, e
 * compará-las só produziria ruído.
 */
$readSchema = static function (PDO $pdo): array {
    $schema = [];

    foreach ($pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->fetchAll() as $table) {
        $name = current($table);

        // A tabela de controle do Migrator não faz parte do schema da aplicação.
        if ($name === 'migrations') {
            continue;
        }

        foreach ($pdo->query("SHOW COLUMNS FROM `{$name}`")->fetchAll() as $column) {
            $schema[$name][$column['Field']] = $column['Type'];
        }
    }

    ksort($schema);

    return $schema;
};

// Os nomes derivados também precisam terminar em '_test', porque o mesmo guard
// protege o DROP.
$dumpName = 'mapos_parity_dump_test';
$migrationName = 'mapos_parity_migration_test';

// --- Lado 1: o que o install/do_install.php produz, a partir do banco.sql ---

$dumpPdo = $test->recreate($dumpName);
$dumpPdo->exec((string) file_get_contents($rootPath . '/banco.sql'));
$dumpSchema = $readSchema($dumpPdo);

// --- Lado 2: o que a cadeia de migrações produz ---

// O banco precisa existir antes do index.php subir: o autoloader de 'database'
// conecta durante o boot.
$test->recreate($migrationName);

TestApplication::boot($migrationName);

$migrationError = TestApplication::migrate();
$migrationSchema = $readSchema($test->pdo($migrationName));

$test->drop($dumpName);
$test->drop($migrationName);

if ($migrationError !== '') {
    fwrite(STDERR, "As migrações falharam: {$migrationError}\n");
    exit(1);
}

/**
 * Diferença entre os dois schemas, como lista de linhas legíveis.
 *
 * Percorre a união dos nomes de tabela e, dentro de cada uma, a união das
 * colunas, de modo que "só no banco.sql" e "só nas migrações" saem do mesmo
 * laço. Antes eram dois laços quase idênticos, e o segundo não tinha o laço
 * interno de colunas: uma coluna removida de um dos lados aparecia como
 * "coluna só no banco.sql" num caminho e ficava invisível no outro, dependendo
 * de qual schema fosse percorrido por último.
 */
$diffSchema = static function (array $dump, array $migrations): array {
    $diff = [];

    foreach (array_unique([...array_keys($dump), ...array_keys($migrations)]) as $table) {
        if (! isset($migrations[$table])) {
            $diff[] = "tabela só no banco.sql: {$table}";

            continue;
        }

        if (! isset($dump[$table])) {
            $diff[] = "tabela só nas migrações: {$table}";

            continue;
        }

        $dumpColumns = $dump[$table];
        $migrationColumns = $migrations[$table];

        foreach (array_unique([...array_keys($dumpColumns), ...array_keys($migrationColumns)]) as $column) {
            if (! isset($migrationColumns[$column])) {
                $diff[] = "coluna só no banco.sql: {$table}.{$column} {$dumpColumns[$column]}";
            } elseif (! isset($dumpColumns[$column])) {
                $diff[] = "coluna só nas migrações: {$table}.{$column} {$migrationColumns[$column]}";
            } elseif (strcasecmp($dumpColumns[$column], $migrationColumns[$column]) !== 0) {
                $diff[] = sprintf(
                    'tipo divergente: %-24s banco.sql=%-18s migracoes=%s',
                    "{$table}.{$column}",
                    $dumpColumns[$column],
                    $migrationColumns[$column]
                );
            }
        }
    }

    return $diff;
};

$diff = $diffSchema($dumpSchema, $migrationSchema);

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
