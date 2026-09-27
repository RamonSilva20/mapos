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
 *   2. O safeLoad() vem antes de resolver as credenciais, porque o fallback delas
 *      é lido de $_ENV e é o Dotenv quem popula.
 *   3. Todas as cinco chaves DB_* são publicadas em $_ENV, e é a publicação — não a
 *      ordem em relação ao Dotenv — que impede um .env de desenvolvimento de apontar
 *      a suíte para o banco de desenvolvimento: o valor final é a sobreposição do
 *      ambiente ou o próprio valor do .env, nunca os dois misturados.
 *
 * Publicar as cinco, e não só host/porta/banco, é o que impede a suíte de abrir duas
 * conexões com credenciais diferentes. O config/database.php não conhece
 * MAPOS_TEST_DB_*: ele lê $_ENV['DB_USERNAME'] e cai no placeholder
 * 'enter_db_username' quando a chave não existe. Sem a publicação, o PDO do
 * TestDatabase conectava com a senha certa e o autoloader 'database' do index.php
 * conectava com o placeholder — o que só aparecia no CI, onde não há .env para
 * preencher a chave por fora.
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

        // safeLoad(), e não load(): o CI roda sem application/.env, e load()
        // lança exceção quando o arquivo não existe. Quem tem .env continua
        // sendo lido.
        Dotenv::createImmutable($envPath)->safeLoad();

        // Depois do safeLoad() porque o fallback lê $_ENV, que é quem o Dotenv
        // popula. A chave MAPOS_TEST_DB_* manda quando existe, e é isso que
        // permite ao CI definir root/root sem depender de um .env.
        $username = self::env('MAPOS_TEST_DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
        $password = self::env('MAPOS_TEST_DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

        // Publicadas em $_ENV para o config/database.php — que o autoloader do
        // index.php lê durante o boot — conectar com as mesmas credenciais que o
        // PDO daqui. Sem isto o config cai no placeholder 'enter_db_username' e o
        // boot falha com "Access denied" onde não existe .env (ver o docblock da
        // classe).
        $_ENV['APP_ENVIRONMENT'] = 'testing';
        $_ENV['DB_HOSTNAME'] = $hostname;
        $_ENV['DB_PORT'] = $port;
        $_ENV['DB_DATABASE'] = $database;
        $_ENV['DB_USERNAME'] = $username;
        $_ENV['DB_PASSWORD'] = $password;

        // config.php lê estas duas chaves sem valor padrão, então a ausência
        // delas gera aviso no log e deixa a encryption_key vazia. Preenchidas
        // só quando faltam, para um .env de desenvolvimento continuar mandando.
        $_ENV['APP_ENCRYPTION_KEY'] ??= 'mapos-testing-key';
        $_ENV['GLOBAL_XSS_FILTERING'] ??= 'false';

        return new self(
            $hostname,
            $port,
            $database,
            $username,
            $password,
            static::rootPath()
        );
    }

    public function database(): string
    {
        return $this->database;
    }

    /**
     * O schema que este banco de teste já tem serve, ou precisa ser remontado.
     *
     * Montar o schema é a parte cara da suíte: a cadeia de migrations leva ~9s,
     * e rodar Tools::migrate() contra um schema já em dia leva 2ms. A diferença
     * inteira está no DROP, porque um banco derrubado nunca está em dia.
     *
     * Três condições, e as três precisam valer:
     *
     *   1. O banco existe.
     *   2. A versão em `migrations` é a da migration mais recente do diretório.
     *      Ela é mais nova quando falta migration, e mais velha quando o branch
     *      foi trocado por um que não conhece a que rodou — nos dois casos o
     *      certo é remontar, porque o Migrator do CI3 só sobe de versão.
     *   3. A impressão digital dos arquivos que definem o banco bate com a que
     *      foi gravada na última montagem.
     *
     * A terceira não é redundante com a segunda. Editar o *conteúdo* de uma
     * migration que já rodou, ou de uma seed, não muda o número do arquivo, então
     * a versão continua em dia e um banco velho passaria pelo teste. A impressão
     * digital é o que pega isso, e o motivo de ela morar num arquivo ao lado do
     * banco — e não numa tabela dentro dele — é que o check-schema-parity.php
     * compara toda tabela BASE TABLE exceto `migrations`, então uma tabela a mais
     * aqui faria o gate de paridade falhar.
     *
     * O arquivo some quando o /tmp é limpo, e aí a resposta é remontar uma vez e
     * regravá-lo. Reaproveitar um banco de procedência desconhecida seria o tipo de
     * atalho que custa horas de depuração; remontar custa 9s uma vez.
     */
    public function isSchemaCurrent(string $database): bool
    {
        static::assertDatabaseNameIsSafe($database);

        $latest = static::latestMigrationVersion();

        if ($latest === null) {
            return false;
        }

        $admin = $this->pdo();

        $exists = $admin->prepare(
            'SELECT COUNT(*) AS total FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?'
        );
        $exists->execute([$database]);

        if ((int) $exists->fetch()['total'] === 0) {
            return false;
        }

        // A tabela de controle do Migrator não existe até a primeira migration, e
        // um banco meio montado é justamente o que precisa ser remontado.
        try {
            $version = $admin
                ->query("SELECT version FROM `{$database}`.`migrations` ORDER BY version DESC LIMIT 1")
                ->fetch();
        } catch (PDOException) {
            return false;
        }

        if ($version === false) {
            return false;
        }

        if (static::normalizeVersion((string) $version['version']) !== $latest) {
            return false;
        }

        $sidecar = static::fingerprintPath($database);

        return is_file($sidecar) && trim((string) file_get_contents($sidecar)) === static::schemaFingerprint();
    }

    /**
     * Grava a impressão digital do banco que acabou de ser montado.
     *
     * Chamada depois de uma montagem bem-sucedida, e é o que permite à próxima
     * execução reaproveitar. O conteúdo é só o hash: o arquivo existe para responder
     * "isto ainda é o que foi construído?" e não guarda nada do banco.
     */
    public function recordSchemaFingerprint(string $database): void
    {
        static::assertDatabaseNameIsSafe($database);

        $path = static::fingerprintPath($database);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        file_put_contents($path, static::schemaFingerprint() . PHP_EOL);
    }

    /**
     * Apaga a impressão digital, para a próxima execução remontar.
     *
     * É o caminho do `composer test:fresh`, e existe para que o arquivo não vire
     * um estado que precisa ser lembrado junto com o resto do harness.
     */
    public function forgetSchemaFingerprint(string $database): void
    {
        static::assertDatabaseNameIsSafe($database);

        $path = static::fingerprintPath($database);

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * A versão da migration mais recente, como o Migrator do CI3 a escreve.
     *
     * O número é o prefixo numérico do nome do arquivo, e é o mesmo que vai para
     * a coluna `version` da tabela de controle. A largura é normalizada em
     * normalizeVersion() porque a comparação é de string e um nome fora do padrão
     * mudaria o resultado sem mudar o significado.
     */
    public static function latestMigrationVersion(): ?string
    {
        $latest = null;

        foreach (static::migrationFiles() as $file) {
            $version = static::normalizeVersion(static::versionFromFilename(basename($file)));

            if ($latest === null || $version > $latest) {
                $latest = $version;
            }
        }

        return $latest;
    }

    private static function fingerprintPath(string $database): string
    {
        // O nome já passou por assertDatabaseNameIsSafe(), que exige terminar em
        // '_test' e portanto não aceita barra nenhuma: o nome não escapa do diretório.
        return sys_get_temp_dir() . '/mapos-test-schema/' . $database . '.hash';
    }

    /**
     * O hash dos arquivos que decidem o que o banco contém.
     *
     * Só entram as migrations, as seeds e o TestFixtures. A migration mais recente
     * já entra pela versão; repetir o conteúdo dela aqui é o que faz uma edição
     * silenciosa dentro de uma migration que já rodou invalidar a impressão digital
     * em vez de passar batido.
     */
    private static function schemaFingerprint(): string
    {
        return static::fingerprintFor(static::schemaFiles());
    }

    /**
     * O núcleo puro da impressão digital, público para poder ser testado.
     *
     * Sem esta separação a única forma de provar que o hash muda quando o CONTEÚDO
     * de uma migration muda seria editar uma migration de verdade, dentro de um
     * teste. Editar `application/database/migrations/` durante a execução é pior
     * do que não testar: um teste que morre no meio deixa o arquivo alterado, e a
     * próxima execução_remonta o banco por causa de um resíduo do teste, que é
     * um sintoma que não aponta para nada. Passando os arquivos de fora, o mesmo
     * arquivo é reescrito entre dois hashes, que é a situação real.
     *
     * As chaves são rótulos, não caminhos: 'seeds/Usuarios.php' em vez do caminho
     * absoluto. É o que impede o hash de depender de onde o projeto está clonado, e
     * o que faz o rótulo — e não só o conteúdo — contar, já que renomear uma
     * migration muda o schema sem mudar uma linha.
     *
     * @param  array<string, string> $labelledFiles rótulo => caminho absoluto
     */
    public static function fingerprintFor(array $labelledFiles): string
    {
        if ($labelledFiles === []) {
            throw new RuntimeException(
                'Uma impressão digital sem nenhum arquivo não distingue um schema de outro.'
            );
        }

        ksort($labelledFiles);

        $manifest = '';

        foreach ($labelledFiles as $label => $file) {
            if (! is_file($file) || ! is_readable($file)) {
                throw new RuntimeException(
                    "Não consegui ler {$file} (rótulo '{$label}') para compor a impressão digital do schema. "
                    . 'Um arquivo de migration ou seed ausente tornaria o hash incompleto, e um hash '
                    . 'incompleto é um banco velho aprovado como atual.'
                );
            }

            $manifest .= $label . ':' . hash_file('sha256', $file) . "\n";
        }

        return hash('sha256', $manifest);
    }

    /**
     * Os arquivos que definem o banco, com o rótulo de cada um.
     *
     * O único lugar que varre os diretórios, para que a impressão digital e a
     * contagem da versão mais recente nunca discordem sobre o que existe.
     *
     * @return array<string, string> rótulo => caminho absoluto
     */
    private static function schemaFiles(): array
    {
        $root = static::envPath();

        $files = [
            'tests/Support/TestFixtures.php' => $root . '/tests/Support/TestFixtures.php',
        ];

        foreach (['migrations', 'seeds'] as $dir) {
            foreach (glob($root . '/database/' . $dir . '/*.php') ?: [] as $file) {
                $files[$dir . '/' . basename($file)] = $file;
            }
        }

        return $files;
    }

    /**
     * Só as migrations, para a contagem da versão mais recente.
     *
     * Filtra o mesmo mapa que alimenta a impressão digital, em vez de varrer o
     * diretório de novo: dois varredores independentes do mesmo diretório podem
     * discordar sobre o que existe, e essa discordância apareceria como "o banco
     * está em dia mas a impressão digital não bate" sem causa visível.
     *
     * @return list<string>
     */
    private static function migrationFiles(): array
    {
        $files = [];

        foreach (static::schemaFiles() as $label => $path) {
            if (str_starts_with($label, 'migrations/')) {
                $files[] = $path;
            }
        }

        sort($files);

        return $files;
    }

    private static function versionFromFilename(string $filename): string
    {
        return (string) preg_replace('/[^0-9].*$/', '', $filename);
    }

    /**
     * A versão como string de largura fixa, para a comparação não depender do tipo
     * que o driver devolveu nem do formato do nome do arquivo.
     */
    private static function normalizeVersion(string $version): string
    {
        return str_pad($version, 14, '0', STR_PAD_LEFT);
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
     * O DROP é o que torna a montagem do banco de testes realmente idempotente: o
     * banco.sql usa CREATE TABLE IF NOT EXISTS em todas as 28 tabelas, então sem
     * o DROP a segunda execução herdaria tudo que a primeira deixou para trás.
     *
     * Quem chama isto é o setup-db.php, e só quando o schema não está em dia —
     * ver isSchemaCurrent(). O caminho que economiza os ~9s da cadeia de migrations
     * não passa por aqui, e é por isso que este método continua sendo o caminho
     * sem recycle: dados de uma execução anterior nunca sobrevivem a ele.
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
