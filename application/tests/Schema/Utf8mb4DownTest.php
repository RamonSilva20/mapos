<?php

namespace Tests\Schema;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Support\App\ChildEnvironment;
use Tests\Support\Database\DatabaseGuard;
use Tests\Support\Database\TestDatabase;

/**
 * A volta da migration para utf8mb3, medida por um filho.
 *
 * A suíte é in-process, e o TestApplication::boot() só pode rodar uma vez por
 * processo: os dois cenários da volta — um banco recém-criado com um emoji
 * (recusa) e um banco recém-criado só com dado limpo (conversão) — precisam de
 * um processo próprio, então cada um é montado por um processo filho do
 * probe-utf8mb4-down.php e este arquivo afirma a saída e o código de saída,
 * sem tocar no banco da suíte. Um modo por filho, porque o boot não roda duas
 * vezes no mesmo processo.
 *
 * A sonda desvia da janela de charset da conexão do CI3 de propósito: o config
 * conecta em utf8mb3 (DB_CHARSET=utf8), e uma conexão assim transforma o emoji
 * em '?' antes de ele chegar à tabela — o dado entraria truncado e não haveria o
 * que recusar. O cenário grava pelo PDO cru em utf8mb4.
 */
final class Utf8mb4DownTest extends TestCase
{
    private string $childToken;

    private string $expectedDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $token = getenv('TEST_TOKEN');

        $this->childToken = $token === false || $token === '' ? 'solo' : $token;
        $this->expectedDatabase = DatabaseGuard::workerDatabaseName('mapos_utf8mb4_down', $this->childToken);
    }

    /**
     * O banco que o filho cria não pode sobrar.
     *
     * Roda no tearDown, e não no corpo do teste, porque é a proteção para o filho
     * MORTO no meio: um filho que buscou 0 já apagou o banco em si. Um filho que
     * morreu encerra com código diferente de 0, e uma afirmação no corpo do teste
     * já teria parado antes de olhar o banco — o tearDown roda de qualquer jeito.
     */
    protected function tearDown(): void
    {
        $test = TestDatabase::fromEnvironment();

        $found = $test->pdo()->query('SHOW DATABASES')->fetchAll(\PDO::FETCH_COLUMN);

        $this->assertNotContains(
            $this->expectedDatabase,
            $found,
            'A sonda deixou o banco de sondagem ' . $this->expectedDatabase . ' para trás: ou o filho morreu '
            . 'no meio (e o código de saída denuncia isso), ou alguma das recusas dele não apaga o que criou.'
        );

        parent::tearDown();
    }

    #[Test]
    public function testDownRefusesToDestroyAnEmojiAndLeavesTheSchemaUntouched(): void
    {
        [$exitCode, $stdout, $stderr] = $this->runProbe(false);

        $this->assertSame(0, $exitCode, 'A sonda da recusa devolveu código diferente de 0. STDERR: ' . $stderr);
        $this->assertStringContainsString('UTF8MB4_DOWN_OK', $stdout, 'A sonda da recusa não chegou ao fim. STDERR: ' . $stderr);
        $this->assertSame('', trim($stderr), 'A sonda da recusa encontrou um defeito: ' . $stderr);
    }

    #[Test]
    public function testDownConvertsACleanDatabase(): void
    {
        [$exitCode, $stdout, $stderr] = $this->runProbe(true);

        $this->assertSame(0, $exitCode, 'A sonda da conversão devolveu código diferente de 0. STDERR: ' . $stderr);
        $this->assertStringContainsString('UTF8MB4_DOWN_OK', $stdout, 'A sonda da conversão não chegou ao fim. STDERR: ' . $stderr);
        $this->assertSame('', trim($stderr), 'A sonda da conversão encontrou um defeito: ' . $stderr);
    }

    /**
     * Sobe a sonda num modo, lê as duas saídas até o fim e devolve o resultado.
     *
     * O filho é curto e escreve pouco, então a leitura é sequencial e depois do
     * fim do processo: as duas saídas cabem no buffer do pipe, e ler depois do
     * proc_close() não arrisca o deadlock de um filho que escreveu mais que o
     * buffer antes de o pai ler.
     *
     * @return array{int, string, string} código de saída, stdout, stderr
     */
    private function runProbe(bool $cleanOnly): array
    {
        $script = dirname(__DIR__) . '/bin/probe-utf8mb4-down.php';
        $arguments = [PHP_BINARY, '-d', 'variables_order=EGPCS', $script];

        if ($cleanOnly) {
            $arguments[] = '--clean';
        }

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

        $process = proc_open($arguments, $descriptors, $pipes, null, $this->childEnvironment());

        if (! is_resource($process)) {
            $this->fail('proc_open() não devolveu um processo para a sonda utf8mb4.');
        }

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return [$exitCode, $stdout, $stderr];
    }

    /**
     * As variáveis que a sonda precisa, e nada mais.
     *
     * A metade compartilhada — os caminhos do ambiente e as cinco chaves de
     * configuração — mora em ChildEnvironment::base(); aqui ficam as que a sonda
     * precisa e o servidor front-end não: as credenciais `MAPOS_TEST_DB_*` e o
     * `TEST_TOKEN` quando o processo é um worker.
     */
    private function childEnvironment(): array
    {
        $environment = ChildEnvironment::base();

        if ($this->childToken !== 'solo') {
            $environment['TEST_TOKEN'] = $this->childToken;
        }

        $test = TestDatabase::fromEnvironment();

        return $environment + [
            'MAPOS_TEST_DB_HOSTNAME' => $_ENV['DB_HOSTNAME'] ?? '127.0.0.1',
            'MAPOS_TEST_DB_PORT' => $_ENV['DB_PORT'] ?? '8989',
            'MAPOS_TEST_DB_DATABASE' => $test->templateDatabase(),
            'MAPOS_TEST_DB_USERNAME' => $_ENV['DB_USERNAME'] ?? 'root',
            'MAPOS_TEST_DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? '',
        ];
    }
}
