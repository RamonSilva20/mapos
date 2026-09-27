<?php

namespace Tests\Support;

use PDO;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Afirma que a conexão do harness e a do CodeIgniter não podem divergir.
 *
 * A suíte abre duas conexões para o mesmo banco: o PDO do TestDatabase, que cria e
 * apaga o banco, e a do autoloader 'database' do index.php, que o CI3 abre durante o
 * boot. A segunda é configurada por application/config/database.php, que não conhece
 * MAPOS_TEST_DB_* — ele lê $_ENV['DB_*'] e cai no placeholder 'enter_db_username'
 * quando a chave não existe.
 *
 * Quando fromEnvironment() resolvia as credenciais só nas suas propriedades privadas,
 * as duas conexões discordavam sempre que não havia application/.env, porque nada
 * publicava $_ENV['DB_USERNAME']. No desenvolvimento o .env preenche a chave e o
 * placeholder nunca aparecia, então a suíte passava; no CI não existe .env, e o boot
 * morria com "Access denied for user 'enter_db_username'".
 *
 * Um teste que roda dentro do processo já bootado não veria a raiz disso — quem
 * preenche a chave é o bootstrap, antes de qualquer teste existir. Por isso o teste
 * compara o config efetivo com o que foi publicado, e não apenas afirma que existe
 * arquivo de config: é a comparação que pega a regressão, e ela só tem o que
 * comparar onde o .env falta, que é o CI.
 *
 * A segunda metade da classe cobre a decisão de reaproveitar o schema, que é o
 * motivo de o setup-db.php parar de derrubar o banco. Ver isSchemaCurrent().
 */
final class TestDatabaseTest extends TestCase
{
    /**
     * As chaves que fromEnvironment() tem que publicar, e que o config/database.php
     * tem que ler. Inclui o usuário e a senha, que é justamente o par que faltava:
     * host, porta e banco já eram publicados desde o começo.
     *
     * @var array<string, string>
     */
    private const PUBLISHED_KEYS = [
        'DB_HOSTNAME' => 'hostname',
        'DB_PORT' => 'port',
        'DB_DATABASE' => 'database',
        'DB_USERNAME' => 'username',
        'DB_PASSWORD' => 'password',
    ];

    /**
     * O banco descartável dos casos de reaproveitamento.
     *
     * Um nome próprio, e não o banco da suíte: estes casos criam e apagam schema,
     * e fazer isso no banco que o resto da execução usa trocaria o assunto da
     * suíte por um DROP acidental. O sufixo `_test` é o que mantém
     * assertDatabaseNameIsSafe() satisfeita — e o que impede este arquivo de ser
     * o motivo de o banco de produção sumir.
     */
    private string $banco = 'mapos_currency_probe_test';

    /**
     * Um nome que nenhum banco pode ter, para o caso do banco inexistente.
     *
     * Terminar em `_test` também aqui, porque a guarda é verificada antes de
     * qualquer conexão: um caso de "banco que não existe" que violasse o sufixo
     * abortaria o processo inteiro em vez de testar o que pretende.
     */
    private string $nomeInexistente = 'mapos_nunca_criado_test';

    /**
     * Os arquivos temporários deste caso, para o `#[After]` apagar.
     *
     * @var list<string>
     */
    private array $temporarios = [];

    /**
     * Derruba o banco descartável, a impressão digital dele e os temporários.
     *
     * No `#[After]` e não no fim de cada caso: um caso que falhe no meio deixa o
     * banco para trás, e o próximo `composer test` reencontraria um schema
     * descartável com a impressão digital certa, o que faria a limpeza parecer
     * funcionar por acidente. O `finally` cobre a falha dentro da própria limpeza.
     */
    #[After]
    public function limparBancoDescartavel(): void
    {
        $test = TestDatabase::fromEnvironment();

        try {
            $test->pdo()->exec("DROP DATABASE IF EXISTS `{$this->banco}`");
        } finally {
            $test->forgetSchemaFingerprint($this->banco);

            foreach ($this->temporarios as $arquivo) {
                if (is_file($arquivo)) {
                    unlink($arquivo);
                }
            }

            $this->temporarios = [];
        }
    }

    /**
     * O config que o CI3 realmente usa, resolvido agora.
     *
     * O arquivo é incluído dentro de uma closure em vez do corpo do teste: ele exige
     * BASEPATH e ENVIRONMENT definidos, e o `defined('BASEPATH') or exit()` da
     * primeira linha derrubaria o processo do PHPUnit inteiro se o boot um dia não os
     * tivesse deixado no lugar. Dentro da closure o escopo também é isolado, então o
     * $db não vaza para o teste.
     *
     * @return array<string, mixed>
     */
    private function ci3DatabaseConfig(): array
    {
        $this->assertTrue(
            defined('BASEPATH') && defined('ENVIRONMENT'),
            'O app precisa estar bootado antes de ler o config/database.php'
        );

        $path = TestDatabase::envPath() . '/config/database.php';

        $read = static function (string $path): array {
            include $path;

            return $db['default'];
        };

        $config = $read($path);

        $this->assertIsArray($config, 'O config/database.php precisa montar $db[\'default\'] como array');

        return $config;
    }

    public function testTheCi3ConfigConnectsWithTheSameDatabaseAsTheHarness(): void
    {
        $config = $this->ci3DatabaseConfig();

        foreach (self::PUBLISHED_KEYS as $envKey => $configKey) {
            $this->assertArrayHasKey(
                $envKey,
                $_ENV,
                "TestDatabase::fromEnvironment() precisa publicar {$envKey} em \$_ENV para o config/database.php"
            );

            $this->assertSame(
                $_ENV[$envKey],
                $config[$configKey] ?? null,
                "O config/database.php e o harness precisam concordar sobre {$envKey}"
            );

            // A redundância que importa: se um dia o valor publicado voltar a ser
            // omitido, a comparação acima acusaria uma diferença, e esta diz o quê.
            $this->assertStringNotContainsString(
                'enter_',
                (string) ($config[$configKey] ?? ''),
                "O config/database.php caiu no placeholder de {$configKey}: fromEnvironment() não publicou a chave"
            );
        }
    }

    /**
     * Um banco recém-montado é reconhecido como em dia.
     *
     * O `migrations` aqui é um de mentira, com a versão mais recente e mais nada:
     * o que este caso exercita é a decisão, não a cadeia de migrations, e essa
     * é verificada de verdade pelo setup-db.php, que é quem roda as 32. Montar
     * um schema completo levaria os 9s que este caso existe para evitar.
     */
    public function testAFreshlyBuiltSchemaIsCurrent(): void
    {
        $test = $this->bancoDeDescarte();
        $pdo = $test->pdo($this->banco);

        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec(sprintf(
            "INSERT INTO `migrations` (`version`) VALUES ('%s')",
            TestDatabase::latestMigrationVersion()
        ));

        $this->assertFalse(
            $test->isSchemaCurrent($this->banco),
            'Sem o arquivo de impressão digital, a procedência do banco é desconhecida e ele não pode ser reaproveitado.'
        );

        $test->recordSchemaFingerprint($this->banco);

        $this->assertTrue(
            $test->isSchemaCurrent($this->banco),
            'Um banco na versão mais recente, com a impressão digital da última montagem, deveria ser reaproveitado.'
        );
    }

    /**
     * Uma versão antiga não é reaproveitada, e é aqui que o branch trogado pega.
     *
     * O Migrator do CI3 só sobe de versão, então um banco mais velho que os
     * arquivos precisaria de um passo para trás que `migrate()` não dá. A
     * resposta tem de ser remontar.
     */
    public function testASchemaBehindTheLatestMigrationIsNotCurrent(): void
    {
        $test = $this->bancoDeDescarte();
        $pdo = $test->pdo($this->banco);

        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec("INSERT INTO `migrations` (`version`) VALUES ('20210101000000')");

        $test->recordSchemaFingerprint($this->banco);

        $this->assertFalse(
            $test->isSchemaCurrent($this->banco),
            'Um banco numa versão anterior à migration mais recente não pode ser reaproveitado: o Migrator só sobe.'
        );
    }

    /**
     * Uma impressão digital que não bate significa arquivo editado, e o banco é
     * velho sem que a versão tenha mudado.
     *
     * É o caso que a versão sozinha não pega: editar o conteúdo de uma migration
     * que já rodou, ou de uma seed, não muda o número do arquivo, então
     * `migrations.version` continua em dia e o banco velho passaria. Aqui a
     * impressão digital é a de outro conjunto de arquivos, o que é
     * indistinguível de uma edição — que é exatamente o ponto.
     */
    public function testAMismatchedFingerprintIsNotCurrent(): void
    {
        $test = $this->bancoDeDescarte();
        $pdo = $test->pdo($this->banco);

        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec(sprintf(
            "INSERT INTO `migrations` (`version`) VALUES ('%s')",
            TestDatabase::latestMigrationVersion()
        ));

        // O que o setup-db.php escreveria depois de uma montagem a partir de
        // arquivos diferentes destes.
        file_put_contents(
            $this->caminhoDaImpressaoDigital(),
            hash('sha256', 'um conjunto de migrations e seeds que não é o deste') . PHP_EOL
        );

        $this->assertFalse(
            $test->isSchemaCurrent($this->banco),
            'A impressão digital não bate, então os arquivos mudaram depois da montagem e o banco precisa ser refeito.'
        );
    }

    /**
     * Um banco que não existe é o caso mais comum, e o mais fácil de errar.
     *
     * A conexão de conferência abre sem `dbname` justamente por isso: uma
     * tentativa de ler `migrations` de um banco inexistente estouraria exceção
     * em vez de devolver um "não está em dia", e o setup-db.php teria que
     * distinguir as duas coisas.
     */
    public function testAMissingDatabaseIsNotCurrent(): void
    {
        $test = TestDatabase::fromEnvironment();

        $this->assertFalse(
            $test->isSchemaCurrent($this->nomeInexistente),
            'Um banco inexistente não pode ser reaproveitado, e a conferência disso não pode lançar.'
        );
    }

    /**
     * A impressão digital fica fora do banco, e o motivo é um gate.
     *
     * Guardá-la numa tabela faria o check-schema-parity.php falhar: ele compara
     * toda BASE TABLE do information_schema contra a cadeia de migrations,
     * excluindo só `migrations`. Uma tabela a mais aqui seria um falso positivo
     * de drift entre o banco.sql e as migrations — o oposto do que o arquivo
     * existe para sinalizar.
     */
    public function testTheFingerprintLivesOutsideTheDatabase(): void
    {
        $test = $this->bancoDeDescarte();
        $test->pdo($this->banco)->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');

        $test->recordSchemaFingerprint($this->banco);

        $tabelas = $test->pdo($this->banco)
            ->query('SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()')
            ->fetchAll(PDO::FETCH_COLUMN);

        $this->assertNotContains(
            'mapos_schema_fingerprint',
            $tabelas,
            'A impressão digital não pode virar uma tabela, ou o check-schema-parity.php vai acusar drift.'
        );

        $this->assertFileExists(
            $this->caminhoDaImpressaoDigital(),
            'A impressão digital precisa existir em algum lugar, e esse lugar é o filesystem ao lado do banco.'
        );
    }

    /**
     * O banco descartável, criado vazio.
     *
     * `recreate()` e não `CREATE DATABASE IF NOT EXISTS`: os casos precisam de um
     * schema garantidamente vazio, e o que sobrou de uma execução anterior — ou de
     * um caso anterior que falhou antes do `#[After]` — faria a conferência de
     * `migrations` começar em cima de algo. A impressao digital é apagada junto,
     * pelo mesmo motivo: ela é o estado que precisa não sobreviver.
     */
    private function bancoDeDescarte(): TestDatabase
    {
        $test = TestDatabase::fromEnvironment();

        $test->recreate($this->banco);
        $test->forgetSchemaFingerprint($this->banco);

        return $test;
    }

    /**
     * A impressão digital muda quando o conteúdo de um arquivo muda.
     *
     * É a propriedade que justifica a impressão digital existir, e a que a versão
     * sozinha não cobre: editar uma migration que JÁ RODOU, ou uma seed, não
     * muda o número do arquivo, então `migrations.version` continua em dia e o
     * banco velho seria aprovado. Um hash que levasse só o nome do arquivo
     * passaria por cima disso, e foi o que este caso encontrou: ele falhou com
     * o manifesto sem `hash_file()` e passou sem o manifesto.
     *
     * O mesmo caminho é reescrito entre os dois hashes, porque é essa a situação
     * real — é o mesmo arquivo, editado. Dois arquivos de nomes diferentes
     * dariam hash diferente mesmo com o conteúdo igual, e o caso não diria nada.
     */
    public function testTheFingerprintChangesWhenTheContentOfAFileChanges(): void
    {
        $arquivo = $this->arquivoTemporario();

        file_put_contents($arquivo, "-- conteudo original\n");
        $antes = TestDatabase::fingerprintFor(['seeds/Usuarios.php' => $arquivo]);

        file_put_contents($arquivo, "-- conteudo editado\n");
        $depois = TestDatabase::fingerprintFor(['seeds/Usuarios.php' => $arquivo]);

        $this->assertNotSame(
            $antes,
            $depois,
            'Editar o conteúdo de uma seed ou migration não mudou a impressão digital, então um banco construído antes da edição seria reaproveitado.'
        );
    }

    /**
     * A impressão digital é estável para o mesmo conjunto de arquivos.
     *
     * O outro lado da mesma verdade: se ela mudasse sozinha, nenhum banco seria
     * jamais reaproveitado e a otimização vira um `--fresh` disfarçado, que é a
     * falha silenciosa oposta.
     */
    public function testTheFingerprintIsStableForTheSameFiles(): void
    {
        $arquivo = $this->arquivoTemporario();
        file_put_contents($arquivo, "-- conteudo\n");

        $this->assertSame(
            TestDatabase::fingerprintFor(['seeds/Usuarios.php' => $arquivo]),
            TestDatabase::fingerprintFor(['seeds/Usuarios.php' => $arquivo]),
            'A impressão digital não pode mudar entre duas leituras do mesmo conjunto de arquivos.'
        );

        // E a ordem de entrada não pode importar, senão o hash dependeria da
        // ordem em que o glob devolveu os arquivos.
        $outro = $this->arquivoTemporario();
        file_put_contents($outro, "-- outra seed\n");

        $this->assertSame(
            TestDatabase::fingerprintFor(['a.php' => $arquivo, 'b.php' => $outro]),
            TestDatabase::fingerprintFor(['b.php' => $outro, 'a.php' => $arquivo]),
            'A ordem em que os arquivos são passados não pode mudar a impressão digital.'
        );
    }

    /**
     * O rótulo entra no hash, porque renomear uma migration muda o schema.
     *
     * Renomear não altera uma linha de código, mas altera a ordem em que a
     * cadeia de migrations roda — que é a definição de schema diferente.
     */
    public function testTheFingerprintCountsTheLabelAndNotOnlyTheContent(): void
    {
        $arquivo = $this->arquivoTemporario();
        file_put_contents($arquivo, "-- conteudo\n");

        $this->assertNotSame(
            TestDatabase::fingerprintFor(['migrations/20260101000000_x.php' => $arquivo]),
            TestDatabase::fingerprintFor(['migrations/20260101000001_x.php' => $arquivo]),
            'Dois nomes com o mesmo conteúdo precisam de impressões digitais diferentes.'
        );
    }

    /**
     * Um arquivo que sumiu faz a impressão digital falhar, e não passar.
     *
     * O modo de falha perigoso aqui não é a exceção: é o silêncio. Uma seed
     * apagada faria o hash cobrir menos arquivos, continuaria sendo um hash
     * válido, e o banco construído com a seed presente seria aprovado como
     * atual — que é justamente asituação que a impressão digital existe para
     * pegar. Por isso a lista vazia também é recusada.
     */
    public function testTheFingerprintRefusesToRunOnAnIncompleteFileList(): void
    {
        $arquivo = $this->arquivoTemporario();
        file_put_contents($arquivo, "-- conteudo\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Não consegui ler/');

        TestDatabase::fingerprintFor(['seeds/Usuarios.php' => $arquivo . '.inexistente']);
    }

    /**
     * Uma lista sem nenhum arquivo não é uma impressão digital.
     *
     * Sem este caso, um diretório de migrations vazio — o estado de um checkout
     * pela metade, por exemplo — produziria um hash de lista vazia, estável, e o
     * banco seria aprovado.
     */
    public function testTheFingerprintRefusesToRunOnNoFilesAtAll(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/sem nenhum arquivo/');

        TestDatabase::fingerprintFor([]);
    }

    private function caminhoDaImpressaoDigital(): string
    {
        return sys_get_temp_dir() . '/mapos-test-schema/' . $this->banco . '.hash';
    }

    /**
     * Um arquivo descartável fora do repositório.
     *
     * Fora de propósito: escrever em `application/database/` durante um teste
     * transformaria a impressão digital do banco real em resíduo de teste, e o
     * banco de testes da suíte passaria a remontar por causa de um arquivo que
     * sobrou.
     */
    private function arquivoTemporario(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mapos-fingerprint-');

        $this->assertIsString($path, 'tempnam() deveria ter criado um arquivo temporário.');

        $this->temporarios[] = $path;

        return $path;
    }
}
