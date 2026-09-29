<?php

namespace Tests\Support\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Se o schema que este banco de testes já tem ainda é o que os arquivos pedem.
 *
 * Montar o schema é a parte cara da suíte: a cadeia de migrations leva ~9s, e
 * rodar Tools::migrate() contra um schema já em dia leva 2ms. A diferença
 * inteira está no DROP, porque um banco derrubado nunca está em dia. Esta classe
 * é a decisão que evita o DROP — e ela é sobre arquivos e sobre a tabela de
 * controle do Migrator, não sobre credenciais, então não mora em TestDatabase.
 *
 * São três condições, e as três precisam valer: o banco existe; a versão em
 * `migrations` é a da migration mais recente; e a impressão digital do banco bate
 * com a da última montagem.
 *
 * A segunda não é redundante com a terceira: editar o *conteúdo* de uma migration
 * que já rodou não muda o número do arquivo, então a versão continua em dia e um
 * banco velho passaria pelo teste. A impressão digital mora num arquivo ao lado
 * do banco, e não numa tabela dentro dele, porque o check-schema-parity.php
 * compara toda tabela BASE TABLE exceto `migrations`. AGENTS.md traz o porquê de
 * cada uma das três.
 *
 * A impressão digital tem DUAS coisas: o hash dos arquivos, e quantas tabelas o
 * banco tem. As três condições acima são todas sobre metadado — o arquivo existe, a
 * versão bate, o hash bate — e nenhuma é sobre o schema em si, então um banco que
 * perdeu uma tabela à mão continua sendo aprovado: o `DROP TABLE os` deixa as três
 * verdadeiras e a suíte inteira roda contra 27 tabelas. É a classe de falha que
 * esta classe existe para impedir, alcançada por uma porta diferente. A contagem
 * não é o conserto completo — ela não vê uma coluna que sumiu — e é o conserto
 * certo aqui: um hash de `SHOW CREATE` por tabela é caro demais para a garantia que
 * compra, enquanto "o banco tem o mesmo número de tabelas de quando foi construído"
 * pega de longe o erro mais provável, que é o destrutivo e o manual.
 *
 * A contagem sai de `SchemaReader::tableNames()`, e não de uma consulta nova: a
 * lista de tabelas do banco tem um leitor só nesta suíte, e um segundo leitor é a
 * divergência que o SchemaReader existe para impedir.
 */
final class SchemaFingerprint
{
    public function __construct(private readonly TestDatabase $test)
    {
    }

    /**
     * O schema que este banco de teste já tem serve, ou precisa ser remontado.
     *
     * O arquivo some quando o /tmp é limpo, e aí a resposta é remontar uma vez e
     * regravá-lo. Reaproveitar um banco de procedência desconhecida seria o tipo de
     * atalho que custa horas de depuração; remontar custa 9s uma vez.
     */
    public function isCurrent(string $database): bool
    {
        DatabaseGuard::assertDatabaseNameIsSafe($database);

        $latest = static::latestMigrationVersion();

        if ($latest === null) {
            return false;
        }

        $admin = $this->test->pdo();

        if (! SchemaReader::databaseExists($admin, $database)) {
            return false;
        }

        // A tabela de controle do Migrator não existe até a primeira migration, e
        // um banco meio montado é justamente o que precisa ser remontado.
        try {
            $version = $admin
                ->query('SELECT version FROM ' . SchemaReader::qualified($database, 'migrations') . ' ORDER BY version DESC LIMIT 1')
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

        return is_file($sidecar) && trim((string) file_get_contents($sidecar)) === $this->stamp($admin, $database);
    }

    /**
     * Grava a impressão digital do banco que acabou de ser montado.
     *
     * Chamada depois de uma montagem bem-sucedida, e é o que permite à próxima
     * execução reaproveitar. O conteúdo é o carimbo — hash e contagem — e nada mais
     * do banco: o arquivo existe para responder "isto ainda é o que foi
     * construído?", e não guarda nada que o próprio banco não responda.
     */
    public function record(string $database): void
    {
        DatabaseGuard::assertDatabaseNameIsSafe($database);

        $path = static::fingerprintPath($database);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        file_put_contents($path, $this->stamp($this->test->pdo(), $database) . PHP_EOL);
    }

    /**
     * O carimbo do banco: o hash dos arquivos, e quantas tabelas ele tem.
     *
     * Uma linha só, e os dois lados dela comparados com `===` sobre a string
     * inteira. Um sidecar no formato antigo — só o hash — não casa, e o banco é
     * remontado: é o comportamento certo para um arquivo cujo formato mudou, e o
     * preço é uma montagem de ~9s uma vez.
     *
     * A conexão chega por parâmetro porque `pdo()` abre uma nova a cada chamada, e
     * `isCurrent()` já tem uma em mãos.
     */
    private function stamp(PDO $pdo, string $database): string
    {
        $tables = count(SchemaReader::tableNames($pdo, $database));

        return static::schemaFingerprint() . ' ' . $tables;
    }

    /**
     * Apaga a impressão digital, para a próxima execução remontar.
     *
     * É o caminho do `composer test:fresh`, e existe para que o arquivo não vire
     * um estado que precisa ser lembrado junto com o resto do harness.
     */
    public function forget(string $database): void
    {
        DatabaseGuard::assertDatabaseNameIsSafe($database);

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

    /**
     * O núcleo puro da impressão digital, público para poder ser testado.
     *
     * Sem esta separação a única forma de provar que o hash muda quando o CONTEÚDO
     * de uma migration muda seria editar uma migration de verdade, dentro de um
     * teste. Editar `application/database/migrations/` durante a execução é pior
     * do que não testar: um teste que morre no meio deixa o arquivo alterado, e a
     * próxima execução remonta o banco por causa de um resíduo do teste, que é
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
     * Onde mora a impressão digital deste banco.
     *
     * Pública para o teste escrever no MESMO caminho que a produção lê. A versão
     * anterior reconstruía a string aqui, byte a byte, e isso é uma divergência
     * silenciosa esperando: dois leitores independentes que discordem aparecem como
     * "o banco está em dia mas a impressão digital não bate", sem causa visível —
     * e `isCurrent()` faz curto-circuito em `is_file()`, então um caminho errado no
     * teste produz um teste que passa pelo motivo errado, para sempre.
     */
    public static function fingerprintPath(string $database): string
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
     * Os arquivos que definem o banco, com o rótulo de cada um.
     *
     * O único lugar que varre os diretórios, para que a impressão digital e a
     * contagem da versão mais recente nunca discordem sobre o que existe.
     *
     * O TestFixtures é relativo a `__DIR__`, e não montado com a raiz: ele mora ao
     * lado desta classe, e a versão anterior escrevia o rótulo e o caminho como o
     * mesmo literal duas vezes — o que codificava a reorganização do próprio
     * branch (o arquivo saiu de `Support/` para `Support/Database/`), e fazia
     * `fingerprintFor()` lançar em vez de responder. `__DIR__` não pode dessincronizar
     * do rótulo: os dois mudam juntos ou nenhum dos dois muda.
     *
     * @return array<string, string> rótulo => caminho absoluto
     */
    private static function schemaFiles(): array
    {
        $root = TestDatabase::envPath();

        $files = [
            'tests/Support/Database/TestFixtures.php' => __DIR__ . '/TestFixtures.php',
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
}
