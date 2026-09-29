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
 * "Mantém o schema, limpa os dados" é a divisão do trabalho aqui. O schema é caro
 * (a cadeia de migrations leva ~9s) e raramente muda, então ele é reaproveitado
 * quando a impressão digital diz que está em dia. Os dados são o que a execução
 * anterior suja, e quem os limpa antes de cada caso é a TransactsDatabase, não este
 * script — ver o método resetBaselineData().
 *
 * `--fresh` ignora a verificação e remonta tudo, para quando o atalho não serve.
 */

use Tests\Support\App\TestApplication;
use Tests\Support\Database\SchemaFingerprint;
use Tests\Support\Database\TestFixtures;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/lib/_boot.php';

$test = bootTestDatabase();

$database = $test->database();

$echo = static function (string $message): void {
    fwrite(STDOUT, $message . PHP_EOL);
};

// --fresh monta do zero. O caminho padrão não pergunta: perguntar por padrão é
// interativo, e um script de CI precisa responder sozinho.
$fresh = in_array('--fresh', $argv, true);
$reusable = ! $fresh && $test->schemaFingerprint()->isCurrent($database);

if ($fresh) {
    // A impressão digital é removida junto com o banco. Sem isto, uma montagem
    // --fresh que falha no meio deixaria o arquivo de uma versão que não é a do
    // banco, e a execução seguinte acreditaria nele.
    $test->schemaFingerprint()->forget($database);
}

if ($reusable) {
    $echo("Schema de '{$database}' em dia (migrations até " . SchemaFingerprint::latestMigrationVersion() . '); reaproveitando.');
} else {
    $echo("Montando o banco '{$database}' a partir das migrações.");

    // O banco precisa existir antes do index.php subir: o autoloader de 'database'
    // conecta durante o boot. O DROP é o que torna isto realmente idempotente — sem
    // ele, a segunda execução herda tudo que a primeira deixou no schema.
    $test->recreate($database);
}

TestApplication::boot();

$migrationError = TestApplication::migrate();

if ($migrationError !== '') {
    fwrite(STDERR, "As migrações falharam: {$migrationError}\n");
    exit(1);
}

if ($reusable) {
    // As seeds NÃO rodam no caminho reaproveitado, e é a única coisa aqui que não
    // é óbvia: a seed Usuarios grava um idUsuarios explícito, então repetir a
    // inserção numa tabela que já tem a linha aborta com 1062. O que substitui a
    // seed é a conferência de baixo, que roda nos dois caminhos.
    $echo('Reference data: reaproveitada.');
} else {
    $echo('Esquema criado pelas migrações.' . PHP_EOL);

    $installed = TestFixtures::install();

    $echo(sprintf('Reference data: %s.', implode(', ', $installed)));

    // A impressão digital só vale depois que a montagem deu certo, e é ela que
    // autoriza a próxima execução a pular o caminho caro.
    $test->schemaFingerprint()->record($database);
}

// Confere o resultado, para uma montagem quebrada falhar aqui e não no meio de
// um teste com uma mensagem sem pistas. No caminho reaproveitado esta conferência
// é a verificação de que as fixtures estão lá, já que nenhuma seed rodou.
$users = get_instance()->db->count_all_results('usuarios');

if ($users !== TestFixtures::USER_COUNT) {
    fwrite(STDERR, sprintf("Esperava %d usuários e encontrei %d.\n", TestFixtures::USER_COUNT, $users));
    fwrite(STDERR, $reusable
        ? "O banco foi reaproveitado sem as fixtures. Rode 'composer test:fresh' para remontá-lo.\n"
        : "As fixtures não instalaram os três usuários.\n");
    exit(1);
}

$echo(sprintf(
    '%d usuários disponíveis: %s (senha 123456).',
    TestFixtures::USER_COUNT,
    implode(', ', TestFixtures::userEmails())
));
