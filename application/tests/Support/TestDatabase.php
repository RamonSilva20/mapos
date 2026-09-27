<?php

namespace Tests\Support;

use Dotenv\Dotenv;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Credenciais, conexão e ciclo de vida do banco de testes.
 *
 * Esta classe é sobre o banco e nada mais. O require() do index.php, o conserto
 * do estado reentrante do CI3 e a corrida das migrações estão em
 * Tests\Support\TestApplication, que depende desta.
 *
 * Três consumidores precisam exatamente da mesma resolução: o bootstrap do
 * PHPUnit, o script que monta o banco (setup-db.php) e o que compara
 * banco.sql com as migrações (check-schema-parity.php). Cada um fazia isso
 * por conta própria, e as cópias já divergiram —
 * uma resolvia as credenciais antes do safeLoad(), outra depois, o que produzia
 * "Access denied" num e o oposto no outro.
 *
 * A ordem das operações importa e não é arbitrária:
 *
 *   1. As chaves MAPOS_TEST_DB_* vêm de getenv(), que só enxerga o ambiente de
 *      verdade. Um .env não participa, então não consegue redirecionar a suíte.
 *   2. As chaves DB_* são escritas em $_ENV ANTES do safeLoad(). O Dotenv é
 *      criado com createImmutable(), que não sobrescreve o que já existe em
 *      $_ENV; é essa pré-escrita que impede um .env de desenvolvimento de
 *      apontar a suíte para o banco de desenvolvimento.
 *   3. Só depois do safeLoad() as credenciais são lidas, porque o fallback vem
 *      de $_ENV e é o Dotenv quem popula.
 */
final class TestDatabase
{
    private function __construct(
        private readonly string $hostname,
        private readonly string $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly string $rootPath
    ) {
    }

    /**
     * Só pode ser carregado pela linha de comando.
     *
     * A suíte mora dentro da raiz do documento e a pasta de testes é servida pelo
     * nginx, que não lê .htaccess. Os scripts daqui reconstruem um banco, então
     * precisam recusar qualquer execução vinda da web.
     */
    public static function assertCommandLine(): void
    {
        if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg') {
            exit('No direct script access allowed');
        }
    }

    /**
     * A suíte importa banco.sql com DROP/CREATE, e o install/do_install.php
     * reconstrói o banco de produção. Esta é a única barreira contra um teste
     * apagar o banco de desenvolvimento, e por isso o nome precisa terminar em
     * '_test'.
     */
    public static function assertDatabaseNameIsSafe(string $name): void
    {
        if (preg_match('/_test$/', $name)) {
            return;
        }

        fwrite(STDERR, "Refusando rodar: o banco de testes precisa terminar em '_test', recebido '{$name}'.\n");
        fwrite(STDERR, "Defina MAPOS_TEST_DB_DATABASE com um nome válido, por exemplo mapos_test.\n");
        exit(1);
    }

    public static function rootPath(): string
    {
        // A classe mora em application/tests/Support/, então a raiz do projeto
        // está três níveis acima.
        return dirname(__DIR__, 3);
    }

    public static function envPath(): string
    {
        return static::rootPath() . '/application';
    }

    public static function fromEnvironment(): self
    {
        $hostname = self::env('MAPOS_TEST_DB_HOSTNAME', '127.0.0.1');
        $port = self::env('MAPOS_TEST_DB_PORT', '8989');
        $database = self::env('MAPOS_TEST_DB_DATABASE', 'mapos_test');

        self::assertDatabaseNameIsSafe($database);

        $envPath = static::envPath();

        // Ver static::fromEnvironment() para por que a ordem importa.
        $_ENV['APP_ENVIRONMENT'] = 'testing';
        $_ENV['DB_HOSTNAME'] = $hostname;
        $_ENV['DB_PORT'] = $port;
        $_ENV['DB_DATABASE'] = $database;

        // safeLoad(), e não load(): o CI roda sem application/.env, e load()
        // lança exceção quando o arquivo não existe. Quem tem .env continua
        // sendo lido.
        Dotenv::createImmutable($envPath)->safeLoad();

        // config.php lê estas duas chaves sem valor padrão, então a ausência
        // delas gera aviso no log e deixa a encryption_key vazia. Preenchidas
        // só quando faltam, para um .env de desenvolvimento continuar mandando.
        $_ENV['APP_ENCRYPTION_KEY'] ??= 'mapos-testing-key';
        $_ENV['GLOBAL_XSS_FILTERING'] ??= 'false';

        return new self(
            $hostname,
            $port,
            $database,
            self::env('MAPOS_TEST_DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root'),
            self::env('MAPOS_TEST_DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? ''),
            static::rootPath()
        );
    }

    public function database(): string
    {
        return $this->database;
    }

    public function dsn(?string $database = null): string
    {
        $dsn = "mysql:host={$this->hostname};port={$this->port};charset=utf8mb4";

        return $database === null ? $dsn : $dsn . ";dbname={$database}";
    }

    public function pdo(?string $database = null): PDO
    {
        try {
            return new PDO($this->dsn($database), $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Falha ao conectar em {$this->hostname}:{$this->port}: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Apaga e recria um banco, e devolve a conexão já apontada para ele.
     *
     * O DROP é o que torna a montagem do banco de testes realmente idempotente.
     * O banco.sql usa CREATE TABLE IF NOT EXISTS em todas as 28 tabelas, então
     * sem o DROP a segunda execução herda tudo que a primeira deixou para trás.
     */
    public function recreate(string $database): PDO
    {
        static::assertDatabaseNameIsSafe($database);

        $admin = $this->pdo();

        $admin->exec("DROP DATABASE IF EXISTS `{$database}`");
        $admin->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        return $this->pdo($database);
    }

    public function drop(string $database): void
    {
        static::assertDatabaseNameIsSafe($database);

        $this->pdo()->exec("DROP DATABASE IF EXISTS `{$database}`");
    }



    private static function env(string $name, string $default): string
    {
        $value = getenv($name);

        // getenv() devolvendo "0" é um valor legítimo; só o false de "não existe"
        // cai no padrão.
        return $value === false || $value === '' ? $default : $value;
    }
}
