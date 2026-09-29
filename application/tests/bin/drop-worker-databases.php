<?php

/**
 * Apaga os bancos que os workers do ParaTest deixaram para trás.
 *
 * Um worker clona o modelo no banco dele — `mapos_1_test`, `mapos_2_test` — e o
 * mantém entre execuções de propósito: a impressão digital faz a segunda
 * execução de um token custar 2ms em vez dos 3,7s do clone. O preço é um banco
 * por token, e o token é reciclado: o `1` de hoje é o `1` de amanhã, então o
 * reaproveitamento é o caso comum.
 *
 * O que sobra é o token que o ParaTest não reciclou mais, porque uma execução
 * usou mais processos que a seguinte. Esses bancos ocupam espaço e são
 * confundidos com trabalho em andamento, e este script é o caminho para tirá-los.
 *
 * Ele é separado do setup por isso. `test:fresh` remonta o MODELO, e o modelo
 * nunca é o banco de um worker — por isso este script não é uma opção do setup
 * e sim um comando próprio, para quando alguém quiser.
 *
 * O modelo é excluído da consulta e nunca é apagado: reconstruí-lo custa os ~9,8s
 * da cadeia de migrations, e apagá-lo por engano seria trocar um incômodo por um
 * downtime de meio minuto.
 */

use Tests\Support\Database\DatabaseGuard;
use Tests\Support\Database\SchemaReader;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/lib/_boot.php';

$test = bootTestDatabase();
$model = $test->templateDatabase();
$base = DatabaseGuard::modelBase($model);

// O filtro é barato e a conferência é a que vale: ele restringe a lista a nomes
// que começam como um worker e termina como um worker, e
// `DatabaseGuard::isWorkerDatabaseName()` logo abaixo refaz o nome pela MESMA
// função que o clone usa. O filtro é a rede, e a conferida é a autoridade — é ela
// que garante que só sai daqui um nome que `workerDatabaseName()` produziu.
$found = SchemaReader::databasesLike($test->pdo(), $base . '_%_test');

$deleted = [];
$skipped = [];

foreach ($found as $name) {
    // O modelo não está no conjunto por construção — `mapos\_%\_test` exige um
    // token no meio, e `mapos_test` não tem — e a conferida abaixo o recusaria do
    // mesmo jeito. Fica escrito de propósito: o passo é o que este script não pode
    // dar errado, e um nome que se prove não ser worker deve ser relatado, não
    // silenciosamente pulado.
    if ($name === $model) {
        continue;
    }

    if (! DatabaseGuard::isWorkerDatabaseName($name, $model)) {
        $skipped[] = $name;

        continue;
    }

    $test->drop($name);
    $test->schemaFingerprint()->forget($name);

    $deleted[] = $name;
}

foreach ($skipped as $name) {
    fwrite(STDOUT, "Ignorado, não é um nome que workerDatabaseName() produziria: {$name}\n");
}

if ($deleted === []) {
    fwrite(STDOUT, 'Nenhum banco de worker para apagar. Modelo preservado: ' . $model . PHP_EOL);

    exit(0);
}

fwrite(STDOUT, sprintf(
    'Apagados %d banco(s) de worker: %s%s',
    count($deleted),
    implode(', ', $deleted),
    PHP_EOL
));
