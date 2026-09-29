<?php

namespace Tests\Support\Database;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * A decisão de reaproveitar o schema, e a impressão digital que a sustenta.
 *
 * Esta é a metade da suíte que existe para o `setup-db.php` não derrubar o banco a
 * cada execução: a cadeia de migrations leva ~9s, e um banco em dia leva 2ms. Ver
 * SchemaFingerprint.
 *
 * Os casos aqui criam e apagam schema de verdade, num banco descartável com nome
 * próprio — nunca no banco que o resto da execução usa, porque um DROP acidental
 * trocaria o assunto da suíte por um incidente.
 */
final class SchemaFingerprintTest extends TestCase
{
    /**
     * O banco descartável dos casos de reaproveitamento.
     *
     * Um nome próprio, e não o banco da suíte: estes casos criam e apagam schema,
     * e fazer isso no banco que o resto da execução usa trocaria o assunto da
     * suíte por um DROP acidental. O sufixo `_test` é o que mantém
     * DatabaseGuard::assertDatabaseNameIsSafe() satisfeita — e o que impede este
     * arquivo de ser o motivo de o banco de produção sumir.
     *
     * O token entra no meio do nome, e é o mesmo cuidado de
     * DatabaseGuard::workerDatabaseName(): um nome fixo seria derrubado e remontado
     * por todos os processos do ParaTest ao mesmo tempo. `prepareDatabaseNames()`
     * monta pelo workerDatabaseName() da produção, e a propriedade fica vazia no
     * lugar de ter um valor padrão para que um caso que esqueça de chamar a
     * preparação receba um nome inválido em vez de um nome compartilhado.
     */
    private string $database = '';

    /**
     * Um nome que nenhum banco pode ter, para o caso do banco inexistente.
     *
     * Terminar em `_test` também aqui, porque a guarda é verificada antes de
     * qualquer conexão: um caso de "banco que não existe" que violasse o sufixo
     * abortaria o processo inteiro em vez de testar o que pretende.
     */
    private string $missingDatabaseName = '';

    /**
     * Os arquivos temporários deste caso, para o `#[After]` apagar.
     *
     * @var list<string>
     */
    private array $temporaryFiles = [];

    /**
     * Deriva os dois nomes do processo e deixa o banco descartável pronto.
     *
     * O que se pede é o que DatabaseGuard::assertDatabaseNameIsSafe() exige e o que
     * o MySQL aceita: o nome tem de terminar em `_test`, e a guarda roda antes de
     * qualquer conexão — um nome fora do padrão aborta o processo inteiro em vez de
     * testar o que pretende. A base sem o sufixo é o que entra no
     * workerDatabaseName(), que devolve o nome com `_test` no fim.
     */
    #[Before]
    public function prepareDatabaseNames(): void
    {
        $token = TestDatabase::parallelToken() ?? 'solo';

        $this->database = DatabaseGuard::workerDatabaseName('mapos_schema_probe', $token);
        $this->missingDatabaseName = DatabaseGuard::workerDatabaseName('mapos_nunca_criado', $token);
    }

    /**
     * Derruba o banco descartável, a impressão digital dele e os temporários.
     *
     * No `#[After]` e não no fim de cada caso: um caso que falhe no meio deixa o
     * banco para trás, e o próximo `composer test` reencontraria um schema
     * descartável com a impressão digital certa, o que faria a limpeza parecer
     * funcionar por acidente. O `finally` cobre a falha dentro da própria limpeza.
     *
     * O `drop()` de `TestDatabase`, e não um `DROP DATABASE` escrito aqui. Este
     * arquivo mantinha a própria instrução, com o nome concatenado entre crases, e
     * assim a única barreira contra um `DROP` no banco errado — `DatabaseGuard` — não
     * era consultada neste caminho. O nome vinha de `workerDatabaseName()`, que já é
     * verificado, mas a verificação acontecia por acaso e não por construção: a
     * segunda escrita do `DROP` é a que deixa de depender do que o chamador fez.
     */
    #[After]
    public function cleanDisposableDatabase(): void
    {
        $test = TestDatabase::fromEnvironment();

        try {
            $test->drop($this->database);
        } finally {
            $test->schemaFingerprint()->forget($this->database);

            foreach ($this->temporaryFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $this->temporaryFiles = [];
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
        $test = $this->disposableDatabase();
        $pdo = $test->pdo($this->database);

        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec(sprintf(
            "INSERT INTO `migrations` (`version`) VALUES ('%s')",
            SchemaFingerprint::latestMigrationVersion()
        ));

        $this->assertFalse(
            $test->schemaFingerprint()->isCurrent($this->database),
            'Sem o arquivo de impressão digital, a procedência do banco é desconhecida e ele não pode ser reaproveitado.'
        );

        $test->schemaFingerprint()->record($this->database);

        $this->assertTrue(
            $test->schemaFingerprint()->isCurrent($this->database),
            'Um banco na versão mais recente, com a impressão digital da última montagem, deveria ser reaproveitado.'
        );
    }

    /**
     * Uma versão antiga não é reaproveitada, e é aqui que o branch trocado pega.
     *
     * O Migrator do CI3 só sobe de versão, então um banco mais velho que os
     * arquivos precisaria de um passo para trás que `migrate()` não dá. A
     * resposta tem de ser remontar.
     */
    public function testASchemaBehindTheLatestMigrationIsNotCurrent(): void
    {
        $test = $this->disposableDatabase();
        $pdo = $test->pdo($this->database);

        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec("INSERT INTO `migrations` (`version`) VALUES ('20210101000000')");

        $test->schemaFingerprint()->record($this->database);

        $this->assertFalse(
            $test->schemaFingerprint()->isCurrent($this->database),
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
     *
     * O `record()` antes de sobrescrever é a linha que torna este caso real.
     * Sem ele, `is_file()` faz curto-circuito, `isCurrent()` responde falso pelo
     * motivo errado, e o caso passa de forma idêntica nos dois motivos: se
     * `fingerprintPath()` mudasse de lugar, ele continuaria verde para sempre sem
     * nunca ter exercitado a comparação de hash. Por isso o carimbo gravado
     * primeiro é lido de volta e conferido, e só o HASH é trocado — um carimbo
     * malformado também reprovaria, e reprovaria pelo mesmo motivo errado.
     */
    public function testAMismatchedFingerprintIsNotCurrent(): void
    {
        $test = $this->disposableDatabase();
        $pdo = $test->pdo($this->database);

        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec(sprintf(
            "INSERT INTO `migrations` (`version`) VALUES ('%s')",
            SchemaFingerprint::latestMigrationVersion()
        ));

        $test->schemaFingerprint()->record($this->database);

        $this->assertTrue(
            $test->schemaFingerprint()->isCurrent($this->database),
            'Sem isto, o que se prova abaixo é que um sidecar ausente reprova — e não que um hash divergente reprova.'
        );

        // O carimbo que o setup-db.php teria escrito depois de uma montagem a partir
        // de arquivos diferentes destes: mesmo formato, mesmo número de tabelas, e
        // só o hash trocado.
        [, $tables] = explode(' ', trim((string) file_get_contents(SchemaFingerprint::fingerprintPath($this->database))), 2);

        file_put_contents(
            SchemaFingerprint::fingerprintPath($this->database),
            hash('sha256', 'um conjunto de migrations e seeds que não é o deste') . ' ' . $tables . PHP_EOL
        );

        $this->assertFalse(
            $test->schemaFingerprint()->isCurrent($this->database),
            'A impressão digital não bate, então os arquivos mudaram depois da montagem e o banco precisa ser refeito.'
        );
    }

    /**
     * Um banco que perdeu uma tabela não é o banco que foi montado.
     *
     * Esta é a única das condições de reaproveitamento que fala do schema, e ela
     * existe porque as outras três são todas sobre metadado: o arquivo existe, a
     * versão bate, o hash bate. Um `DROP TABLE os` à mão deixa as três verdadeiras
     * e a suíte inteira roda contra 27 tabelas — a mesma classe de falha que a
     * impressão digital existe para impedir, alcançada por outra porta.
     *
     * A troca é feita na contagem e não no arquivo: gravar um carimbo errado
     * reprovaria pelo motivo do caso acima, e o que se quer provar é que a
     * CONTAGAM do banco, e não o conteúdo do sidecar, é lida.
     */
    public function testASchemaThatLostATableIsNotCurrent(): void
    {
        $test = $this->disposableDatabase();
        $pdo = $test->pdo($this->database);

        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec(sprintf(
            "INSERT INTO `migrations` (`version`) VALUES ('%s')",
            SchemaFingerprint::latestMigrationVersion()
        ));
        $pdo->exec('CREATE TABLE `probe` (`id` INT NOT NULL)');

        $test->schemaFingerprint()->record($this->database);

        $this->assertTrue(
            $test->schemaFingerprint()->isCurrent($this->database),
            'O banco tem o que foi registrado, então ele é reaproveitável antes do DROP.'
        );

        $pdo->exec('DROP TABLE `probe`');

        $this->assertFalse(
            $test->schemaFingerprint()->isCurrent($this->database),
            'O banco perdeu uma tabela depois de ser montado, e as três condições de metadado continuam verdadeiras: a contagem é o que pega.'
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
            $test->schemaFingerprint()->isCurrent($this->missingDatabaseName),
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
     *
     * A lista de tabelas é lida por `SchemaReader::tableNames()`, e não pela consulta
     * que este caso escrevia. Este é o teste vizinho do `SchemaTest.php:69`, que
     * foi convertido e até comenta o porquê; a conversão não tinha chegado aqui. A
     * consequência de ficar para trás é que a afirmação "¿a impressão virou uma
     * tabela?" era respondida por uma leitura de `information_schema.tables` que
     * ninguém mais da suíte usa, e o filtro `table_schema` dela podia divergir do
     * `SchemaReader` sem que nada percebesse — um teste que passa conferindo o
     * conjunto errado é pior do que não conferir, porque ocupa o lugar de uma
     * conferência que não existe.
     */
    public function testTheFingerprintLivesOutsideTheDatabase(): void
    {
        $test = $this->disposableDatabase();
        $test->pdo($this->database)->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');

        $test->schemaFingerprint()->record($this->database);

        $tables = SchemaReader::tableNames($test->pdo($this->database), $this->database);

        $this->assertNotContains(
            'mapos_schema_fingerprint',
            $tables,
            'A impressão digital não pode virar uma tabela, ou o check-schema-parity.php vai acusar drift.'
        );

        $this->assertFileExists(
            SchemaFingerprint::fingerprintPath($this->database),
            'A impressão digital precisa existir em algum lugar, e esse lugar é o filesystem ao lado do banco.'
        );
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
        $file = $this->temporaryFile();

        file_put_contents($file, "-- conteudo original\n");
        $before = SchemaFingerprint::fingerprintFor(['seeds/Usuarios.php' => $file]);

        file_put_contents($file, "-- conteudo editado\n");
        $after = SchemaFingerprint::fingerprintFor(['seeds/Usuarios.php' => $file]);

        $this->assertNotSame(
            $before,
            $after,
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
        $file = $this->temporaryFile();
        file_put_contents($file, "-- conteudo\n");

        $this->assertSame(
            SchemaFingerprint::fingerprintFor(['seeds/Usuarios.php' => $file]),
            SchemaFingerprint::fingerprintFor(['seeds/Usuarios.php' => $file]),
            'A impressão digital não pode mudar entre duas leituras do mesmo conjunto de arquivos.'
        );

        // E a ordem de entrada não pode importar, senão o hash dependeria da
        // ordem em que o glob devolveu os arquivos.
        $other = $this->temporaryFile();
        file_put_contents($other, "-- outra seed\n");

        $this->assertSame(
            SchemaFingerprint::fingerprintFor(['a.php' => $file, 'b.php' => $other]),
            SchemaFingerprint::fingerprintFor(['b.php' => $other, 'a.php' => $file]),
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
        $file = $this->temporaryFile();
        file_put_contents($file, "-- conteudo\n");

        $this->assertNotSame(
            SchemaFingerprint::fingerprintFor(['migrations/20260101000000_x.php' => $file]),
            SchemaFingerprint::fingerprintFor(['migrations/20260101000001_x.php' => $file]),
            'Dois nomes com o mesmo conteúdo precisam de impressões digitais diferentes.'
        );
    }

    /**
     * Um arquivo que sumiu faz a impressão digital falhar, e não passar.
     *
     * O modo de falha perigoso aqui não é a exceção: é o silêncio. Uma seed
     * apagada faria o hash cobrir menos arquivos, continuaria sendo um hash
     * válido, e o banco construído com a seed presente seria aprovado como
     * atual — que é justamente a situação que a impressão digital existe para
     * pegar. Por isso a lista vazia também é recusada.
     */
    public function testTheFingerprintRefusesToRunOnAnIncompleteFileList(): void
    {
        $file = $this->temporaryFile();
        file_put_contents($file, "-- conteudo\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Não consegui ler/');

        SchemaFingerprint::fingerprintFor(['seeds/Usuarios.php' => $file . '.inexistente']);
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

        SchemaFingerprint::fingerprintFor([]);
    }

    /**
     * O banco descartável, criado vazio.
     *
     * `recreate()` e não `CREATE DATABASE IF NOT EXISTS`: os casos precisam de um
     * schema garantidamente vazio, e o que sobrou de uma execução anterior — ou de
     * um caso anterior que falhou antes do `#[After]` — faria a conferência de
     * `migrations` começar em cima de algo. A impressão digital é apagada junto,
     * pelo mesmo motivo: ela é o estado que precisa não sobreviver.
     */
    private function disposableDatabase(): TestDatabase
    {
        $test = TestDatabase::fromEnvironment();

        $test->recreate($this->database);
        $test->schemaFingerprint()->forget($this->database);

        return $test;
    }

    /**
     * Um arquivo descartável fora do repositório.
     *
     * Fora de propósito: escrever em `application/database/` durante um teste
     * transformaria a impressão digital do banco real em resíduo de teste, e o
     * banco de testes da suíte passaria a remontar por causa de um arquivo que
     * sobrou.
     */
    private function temporaryFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mapos-fingerprint-');

        $this->assertIsString($path, 'tempnam() deveria ter criado um arquivo temporário.');

        $this->temporaryFiles[] = $path;

        return $path;
    }
}
