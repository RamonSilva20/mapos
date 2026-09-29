<?php

namespace Tests\Support\Database;

use Dotenv\Dotenv;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Credenciais, conexão e ciclo de vida do banco de testes.
 *
 * Esta classe é sobre o banco e nada mais. O require() do index.php, o conserto
 * do estado reentrante do CI3 e a corrida das migrações estão em
 * Tests\Support\App\TestApplication, que depende desta.
 *
 * Três consumidores precisam exatamente da mesma resolução: o bootstrap do
 * PHPUnit, o script que monta o banco (setup-db.php) e o que compara
 * banco.sql com as migrações (check-schema-parity.php). Cada um fazia isso por
 * conta própria, e as cópias já divergiram — uma resolvia as credenciais antes
 * do safeLoad(), outra depois, o que produzia "Access denied" num e o oposto no
 * outro.
 *
 * A ordem das operações importa e não é arbitrária: as chaves MAPOS_TEST_DB_*
 * vêm de getenv(), que só enxerga o ambiente de verdade e por isso não pode ser
 * redirecionado por um .env; o safeLoad() vem antes de resolver as credenciais,
 * porque o fallback delas é lido de $_ENV e é o Dotenv quem popula; e as cinco
 * chaves DB_* são publicadas em $_ENV, o que impede um .env de desenvolvimento de
 * apontar a suíte para o banco de desenvolvimento. Publicar as cinco, e não só
 * host/porta/banco, é o que impede a suíte de abrir duas conexões com
 * credenciais diferentes — o config/database.php não conhece MAPOS_TEST_DB_*, e
 * sem a publicação o PDO desta classe conectava com a senha certa e o
 * autoloader 'database' do index.php com o placeholder 'enter_db_username'.
 * AGENTS.md traz o parágrafo completo disso.
 *
 * ## Os dois nomes de banco
 *
 * MAPOS_TEST_DB_DATABASE é o nome do MODELO, e é o que o setup-db.php monta
 * pelas migrations. O nome que este processo USA é o modelo acrescido do
 * TEST_TOKEN do ParaTest, e é ele que vai para $_ENV['DB_DATABASE'].
 *
 * A suíte é in-process por escolha, e cada processo do ParaTest tem a sua própria
 * instância do CI3 já inicializada — mas o banco é o único estado que o processo
 * não carrega consigo. Sem o segundo nome, os processos dividem `usuarios`, e
 * resetBaselineData() segura um X-lock naquelas três linhas pelo teste inteiro:
 * o segundo processo espera e estoura em innodb_lock_wait_timeout. Por isso os
 * nomes são dois, e por isso o token entra antes do sufixo `_test`, que
 * assertDatabaseNameIsSafe() exige.
 *
 * Sem TEST_TOKEN — PHPUnit normal, setup-db.php, check-schema-parity.php — os
 * dois nomes são o mesmo e nada mais acontece. Ver TestSchemaClone.
 */
final class TestDatabase
{
    private function __construct(
        private readonly string $hostname,
        private readonly string $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly string $rootPath,
        private readonly string $template
    ) {
    }

    public static function rootPath(): string
    {
        // Sobe até a raiz em vez de contar níveis. A contagem quebrava a cada
        // vez que esta pasta ganhava um subdiretório, e a quebra só aparecia
        // na execução: o require() do index.php em TestApplication::boot() é o
        // primeiro lugar que usa este valor, e ele acontece depois do banco já
        // estar montado.
        for ($directory = __DIR__, $parent = dirname(__DIR__); $directory !== $parent; $directory = $parent, $parent = dirname($parent)) {
            if (is_file($directory . '/index.php') && is_dir($directory . '/application')) {
                return $directory;
            }
        }

        throw new RuntimeException('Não achei a raiz do projeto subindo de ' . __DIR__ . '.');
    }

    /**
     * O token do processo paralelo, ou null na execução de processo único.
     *
     * O ParaTest publica TEST_TOKEN como um inteiro incremental, um valor
     * diferente para cada processo rodando ao mesmo tempo, e o mesmo valor é
     * reciclado quando um processo termina e outro ocupa o lugar dele. Essa
     * reciclagem é o que torna barato identificar o banco do processo sem saber
     * de antemão quantos processos vão existir.
     *
     * Ausente fora do ParaTest — inclusive no PHPUnit normal, no setup-db.php e
     * no check-schema-parity.php, que são processos próprios e não recebem
     * TEST_TOKEN. É por isso que o caminho de processo único não paga nada por
     * esta classe: o token é null e o nome efetivo é o do modelo.
     */
    public static function parallelToken(): ?string
    {
        $token = getenv('TEST_TOKEN');

        return $token === false || $token === '' ? null : $token;
    }

    public static function envPath(): string
    {
        return static::rootPath() . '/application';
    }

    public static function fromEnvironment(): self
    {
        $hostname = self::env('MAPOS_TEST_DB_HOSTNAME', '127.0.0.1');
        $port = self::env('MAPOS_TEST_DB_PORT', '8989');
        $template = self::env('MAPOS_TEST_DB_DATABASE', 'mapos_test');

        // O nome do modelo é conferido e o nome efetivo também. São os dois
        // bancos que este processo pode abrir, e DatabaseGuard::assertDatabaseNameIsSafe() é a
        // única barreira entre a suíte e o banco de desenvolvimento.
        DatabaseGuard::assertDatabaseNameIsSafe($template);

        $token = self::parallelToken();

        $database = $token === null ? $template : DatabaseGuard::workerDatabaseName($template, $token);

        DatabaseGuard::assertDatabaseNameIsSafe($database);

        $envPath = static::envPath();

        // safeLoad(), e não load(): o CI roda sem application/.env, e load()
        // lança exceção quando o arquivo não existe. Quem tem .env continua
        // sendo lido.
        Dotenv::createImmutable($envPath)->safeLoad();

        // Depois do safeLoad() porque o fallback é lido daqui, que é quem o Dotenv
        // populou. A chave MAPOS_TEST_DB_* manda quando existe, e é isso que
        // permite ao CI definir root/root sem depender de um .env.
        //
        // O fallback passa por env() e não por um $_ENV[...] direto: o `$_ENV` é
        // um dos lugares onde o Dotenv pode ter escrito, e não o único nem
        // sempre um deles. Ler só um deles já fez o CI quebrar só na execução
        // paralela, com o worker caindo no placeholder 'root' e o .env presente
        // no disco.
        $username = self::env('MAPOS_TEST_DB_USERNAME', self::env('DB_USERNAME', 'root'));
        $password = self::env('MAPOS_TEST_DB_PASSWORD', self::env('DB_PASSWORD', ''));

        // Publicadas em $_ENV para o config/database.php — que o autoloader do
        // index.php lê durante o boot — conectar com as mesmas credenciais que o
        // PDO daqui. Sem isto o config cai no placeholder 'enter_db_username' e o
        // boot falha com "Access denied" onde não existe .env (ver o docblock da
        // classe).
        //
        // DB_DATABASE recebe o nome do worker, e não o do modelo: é o banco onde
        // este processo escreve, e é nele que a transação do TransactsDatabase
        // roda. Um worker que publicasse o nome do modelo faria dois processos
        // disputarem as mesmas linhas de `usuarios` — que é exatamente o que a
        // execução paralela existe para evitar.
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
            static::rootPath(),
            $template
        );
    }

    /**
     * O banco que este processo USA.
     *
     * É o que já está publicado em $_ENV['DB_DATABASE'] e o que a transação do
     * TransactsDatabase abre. Em execução de processo único ele coincide com o
     * modelo, que é o que faz o caminho serial não mudar em nada.
     */
    public function database(): string
    {
        return $this->database;
    }

    /**
     * O banco modelo: de onde o worker tira a cópia, e o que o setup-db.php monta.
     *
     * Os dois nomes são necessários e é o worker que precisa dos dois. O modelo
     * é a origem da cópia; o efetivo é onde este processo escreve. Sob ParaTest
     * eles são bancos diferentes, e é essa diferença que impede dois processos
     * de disputarem as mesmas linhas.
     */
    public function templateDatabase(): string
    {
        return $this->template;
    }

    /**
     * A impressão digital do schema, ligada a esta conexão.
     *
     * Existe para que quem precisa da decisão ("este banco ainda serve?") não
     * precise conhecer a classe que a toma, e para que a dependência fique numa
     * direção só: SchemaFingerprint abre conexão, TestDatabase não sabe que ela
     * existe.
     */
    public function schemaFingerprint(): SchemaFingerprint
    {
        return new SchemaFingerprint($this);
    }

    /**
     * O DSN do servidor, com o banco opcional.
     *
     * Sem o nome do banco o PDO conecta sem selecionar um schema, e é assim que
     * o CREATE DATABASE do worker funciona: o banco ainda não existe, e um
     * `dbname=` no DSN o faria o driver recusar a conexão.
     */
    private function dsn(?string $database = null): string
    {
        $dsn = "mysql:host={$this->hostname};port={$this->port};charset=utf8mb4";

        return $database === null ? $dsn : $dsn . ";dbname={$database}";
    }

    /**
     * Uma conexão nova, para o banco dado ou para o banco do processo.
     *
     * Nova a cada chamada, de propósito: a suíte compartilha a conexão do CI3
     * através de get_instance()->db, e um PDO separado é o que permite ao
     * setup-db.php recriar o schema sem derrubar a transação que o caso abriu.
     * Passar null é o mesmo que passar $this->database(), e é o que os chamadores
     * que não pensam em banco fazem.
     *
     * @throws RuntimeException se a conexão recusar, com o host:port na mensagem
     */
    public function pdo(?string $database = null): PDO
    {
        try {
            return new PDO($this->dsn($database), $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                "Falha ao conectar em {$this->hostname}:{$this->port}: {$exception->getMessage()}",
                0,
                $exception
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
     * ver SchemaFingerprint::isCurrent(). O caminho que economiza os ~9s da cadeia de migrations
     * não passa por aqui, e é por isso que este método continua sendo o caminho
     * sem recycle: dados de uma execução anterior nunca sobrevivem a ele.
     */
    public function recreate(string $database): PDO
    {
        DatabaseGuard::assertDatabaseNameIsSafe($database);

        $admin = $this->pdo();

        $this->withoutForeignKeys($admin, static function () use ($admin, $database): void {
            $admin->exec('DROP DATABASE IF EXISTS ' . SchemaReader::identifier($database));
        });

        $admin->exec('CREATE DATABASE ' . SchemaReader::identifier($database) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        return $this->pdo($database);
    }

    public function drop(string $database): void
    {
        DatabaseGuard::assertDatabaseNameIsSafe($database);

        $admin = $this->pdo();

        $this->withoutForeignKeys($admin, function () use ($admin, $database): void {
            $admin->exec('DROP DATABASE IF EXISTS ' . SchemaReader::identifier($database));
        });
    }

    /**
     * Roda um DROP de schema com a verificação de chaves desligada.
     *
     * `DROP DATABASE` escolhe a ordem em que derruba as tabelas, e em um schema
     * com chave estrangeira a escolha nem sempre é uma ordem válida: o MySQL
     * aborta com "Cannot drop table 'pai' referenced by a foreign key constraint
     * 'fk_composta' on table 'filho'" (erro 3730) quando a tabela pai entra
     * antes da filha. Não é uma hipótese — é o que o schema de duas tabelas do
     * TestSchemaCloneSyntheticOrigin provoca, e a mesma falha alcança qualquer um
     * dos bancos de teste que tenha ganhado uma constraint.
     *
     * Desligar a verificação é a única forma de o DROP acontecer, e ela vale para
     * todo o schema de uma vez. O risco de desligar isso seria um DELETE que
     * violasse integridade referencial, e aqui o que roda dentro do bloco é um
     * `DROP DATABASE` de um banco descartável — não há dado que possa sobrar
     * órfão, porque não sobra nada.
     *
     * A volta a ligar é no `finally` e na MESMA conexão: `FOREIGN_KEY_CHECKS` é
     * de sessão, e o `pdo()` daqui abre uma conexão nova a cada chamada, então
     * religar numa outra não religaria esta.
     */
    private function withoutForeignKeys(PDO $admin, callable $operation): void
    {
        $admin->exec('SET FOREIGN_KEY_CHECKS = 0');

        try {
            $operation();
        } finally {
            $admin->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    /**
     * Uma variável de ambiente, de onde ela estiver.
     *
     * As três fontes são consultadas porque o Dotenv não escreve nas três, e as
     * duas consequências disso já custaram uma execução: `getenv()` devolvia falso
     * mesmo com a variável no `.env`, e `$_ENV` chega vazia num worker do ParaTest
     * enquanto `$_SERVER` chega cheia — o worker abria o banco como `root` sem
     * senha. A ordem não é arbitrária: quando a variável é REAL o PHP a coloca em
     * todas as fontes com o mesmo valor, e o Dotenv é imutável e não a sobrescreve.
     * O `.env` é o único caso em que as fontes divergem — ele só escreve onde a
     * chave ainda não existe — e aí ela está em `$_ENV`/`$_SERVER` e não em
     * getenv(), que é exatamente a ordem lida aqui. AGENTS.md traz o resumo.
     */
    private static function env(string $name, string $default): string
    {
        foreach ([$_ENV[$name] ?? null, $_SERVER[$name] ?? null, getenv($name)] as $value) {
            // getenv() devolvendo "0" é um valor legítimo, e só o false de "não
            // existe" — e o "" de "setado para vazio" — cai no padrão.
            if ($value === false || $value === null || $value === '') {
                continue;
            }

            return (string) $value;
        }

        return $default;
    }
}
