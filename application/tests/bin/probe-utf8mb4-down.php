<?php

/**
 * Mostra o que a volta para utf8mb3 faz, pela lente de um banco recém-criado.
 *
 * Uso: php application/tests/bin/probe-utf8mb4-down.php [--clean]
 *
 * Este script é o processo filho do Utf8mb4DownTest: a suíte é in-process, e o
 * TestApplication::boot() só pode rodar uma vez por processo, então o teste que
 * precisa de um banco recém-criado para exercitar a migration toca num coprocesso
 * e lê o resultado dele. O pai afirma a saída e o código de saída, não o conteúdo.
 * Um modo por processo — nunca os dois — porque o boot só roda uma vez.
 *
 * Dois modos, um por processo:
 *
 *  - padrão (a recusa): cria um banco com conteúdo em utf8mb4 — um emoji, que são
 *    os 4 bytes que utf8mb3 não tem — e chama a migration de volta. A volta TEM de
 *    abrir exceção, o dado TEM de continuar lá, e a DETECÇÃO é em duas passadas: a
 *    fila inteira é examinada antes do primeiro ALTER, então a tabela limpa permanece
 *    em utf8mb4 — nada foi convertido — e o sql_mode e o db_debug da sessão nem saem
 *    do lugar.
 *  - --clean (a conversão): cria só uma tabela limpa em utf8mb4, sem emoji nenhum.
 *    A volta TEM de completar, e a tabela TEM de chegar em utf8mb3 com o dado.
 *
 * Códigos de saída: 0 é sucesso, 1 é um defeito encontrado, 2 é impossibilidade
 * de preparar o cenário. O script inteiro é o cenário: nada aqui é do CI3 de
 * propósito, para uma falha não ser a resposta do framework disfarçada.
 *
 * A janela por onde o emoji entra e sai é o PDO cru, e não a conexão do CI3: a
 * aplicação se conecta com o char_set do config, e uma conexão utf8mb3 transforma
 * o emoji de 4 bytes em '?' na ENTRADA — o dado chegaria à tabela como '?', e o
 * down() não teria o que recusar. É exatamente a degradação que uma instalação
 * atualizada sem DB_CHARSET=utf8mb4 no .env teria na aplicação de verdade, e é
 * por isso que a montagem do cenário tem de contorná-la.
 */

use Tests\Support\App\TestApplication;
use Tests\Support\Database\DatabaseGuard;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/lib/_boot.php';

const UTF8MB4_DOWN_OK = 'UTF8MB4_DOWN_OK';

/**
 * A versão (o timestamp do nome) da migration cujo down() a sonda exercita.
 *
 * A resolução do arquivo e do nome da classe é a do próprio framework
 * (Migration::find_migrations() + o mesmo cálculo de classe da linha que
 * carrega), para renomear o arquivo não rebentar esta sonda com um nome
 * hardcoded — ela segue a cadeia onde a cadeia estiver.
 */
const MIGRATION_VERSION = 20261005130000;

/**
 * Encerra com mensagem em STDERR, com o código que o pai entende.
 */
function fail(string $message, int $code = 1): never
{
    fwrite(STDERR, "probe-utf8mb4-down: {$message}\n");
    exit($code);
}

$cleanOnly = in_array('--clean', array_slice($argv, 1), true);

$test = bootTestDatabase();

$name = DatabaseGuard::workerName('mapos_utf8mb4_down');

$test->recreate($name);
TestApplication::boot($name);
$db = TestApplication::superObject()->db;
$previousDebug = $db->db_debug;

TestApplication::superObject()->load->library('migration');

$raw = $test->pdo($name);
$raw->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

foreach (['probe_down', 'probe_clean'] as $tabela) {
    try {
        $raw->exec('CREATE TABLE `' . $tabela . '` (t TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
    } catch (PDOException $exception) {
        $test->drop($name);
        fail('Não criei a tabela de sondagem: ' . $exception->getMessage() . '.', 2);
    }
}

$emoji = '😀';

try {
    $raw->exec('INSERT INTO probe_clean (t) VALUES (\'texto\')');

    if (! $cleanOnly) {
        $statement = $raw->prepare('INSERT INTO probe_down (t) VALUES (?)');
        $statement->execute([$emoji]);
    }
} catch (PDOException $exception) {
    $test->drop($name);
    fail('Não gravei o cenário: ' . $exception->getMessage() . '.', 2);
}

// A migration é resolvida pela própria biblioteca de migrations, e não por um
// caminho gravado aqui: find_migrations() devolve versão => arquivo, e o nome da
// classe é derivado do nome do arquivo exatamente como Migration.php faz na hora
// de executar. Um rename do arquivo troca uma quebra muda por um acompanhamento.
$migrations = TestApplication::superObject()->migration->find_migrations();

$file = $migrations[MIGRATION_VERSION] ?? null;

if ($file === null) {
    $test->drop($name);
    fail('A migration alvo sumiu da cadeia (' . MIGRATION_VERSION . ').', 2);
}

$textName = preg_replace('/^[0-9]+_/', '', basename($file, '.php'));
$className = 'Migration_' . ucfirst(strtolower((string) $textName));

include_once $file;

$migration = new $className();
$caught = null;

try {
    $migration->down();
} catch (RuntimeException $exception) {
    $caught = $exception;
}

if ($cleanOnly) {
    if ($caught instanceof RuntimeException) {
        $test->drop($name);
        fail('O down() recusou sem emoji no caminho. A tabela limpa não deveria acusar: ' . $caught->getMessage());
    }

    if ($db->db_debug !== $previousDebug) {
        $test->drop($name);
        fail('O db_debug não voltou a ser o que era depois da volta.');
    }

    $cleanCollation = $db->query(
        "SELECT table_collation AS collation FROM information_schema.tables "
        . "WHERE table_schema = " . $db->escape($name) . " AND table_name = 'probe_clean'"
    )->row()->collation;

    $cleanValue = $raw->query('SELECT t FROM probe_clean')->fetch(PDO::FETCH_COLUMN);

    $test->drop($name);

    if ($cleanCollation !== 'utf8mb3_general_ci') {
        fail('A tabela limpa não foi convertida, collation ficou em ' . var_export($cleanCollation, true) . '.');
    }

    if ($cleanValue !== 'texto') {
        fail('A tabela limpa perdeu dado na conversão: ' . var_export($cleanValue, true));
    }

    fwrite(STDOUT, UTF8MB4_DOWN_OK . "\n");
    exit(0);
}

if (! $caught instanceof RuntimeException) {
    $test->drop($name);
    fail('O down() não recusou. Sem exceção NENHUMA ele converteu com dado de 4 bytes no caminho — ou a '
        . 'pergunta de arredondamento não está vendo o emoji, ou o ALTER não está sendo precedido por ela.');
}

if ($db->db_debug !== $previousDebug) {
    $test->drop($name);
    fail('O db_debug não voltou a ser o que era depois da recusa.');
}

$message = $caught->getMessage();

if (! str_contains($message, 'probe_down') || ! str_contains($message, 'utf8mb3')) {
    $test->drop($name);
    fail('A exceção não nomeia a tabela nem o charset da volta: ' . $message);
}

$value = $raw->query('SELECT t FROM probe_down')->fetch(PDO::FETCH_COLUMN);
$cleanCollation = $db->query(
    "SELECT table_collation AS collation FROM information_schema.tables "
    . "WHERE table_schema = " . $db->escape($name) . " AND table_name = 'probe_clean'"
)->row()->collation;
$cleanValue = $raw->query('SELECT t FROM probe_clean')->fetch(PDO::FETCH_COLUMN);

$test->drop($name);

if ($value !== $emoji) {
    fail('O dado sumiu ou foi truncado antes da recusa. Esperado ' . $emoji . ', lido ' . var_export($value, true) . '.');
}

if ($cleanCollation !== 'utf8mb4_general_ci') {
    fail('A recusa converteu algo antes da hora: a tabela limpa saiu do utf8mb4 (collation ' . var_export($cleanCollation, true) . '). A fila é examinada inteira antes do primeiro ALTER, então nada devia ter sido convertido.');
}

if ($cleanValue !== 'texto') {
    fail('A tabela limpa perdeu dado: ' . var_export($cleanValue, true));
}

fwrite(STDOUT, UTF8MB4_DOWN_OK . "\n");
exit(0);
