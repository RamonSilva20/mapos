<?php

namespace Tests\Support\Clone;

use PDO;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tests\Support\Database\SchemaFingerprint;
use Tests\Support\Database\TestDatabase;

/**
 * O schema sintético de duas tabelas e duas constraints, e o ciclo de vida dele.
 *
 * Fica em trait separada de TestSchemaCloneShared porque custa ~220ms por classe
 * que o usa, e porque nem todo caso de clone precisa dele. Medido nesta máquina:
 * ~117ms para as três tabelas e os dois INSERT (o InnoDB paga o flush do dicionário
 * de dados a cada `CREATE TABLE` com constraint) e ~52ms no `DROP DATABASE` final.
 *
 * Três das quatro classes de clone o usam; a de guardas não, e por isso não paga.
 * Uma classe que só instancia o TestSchemaClone para receber uma exceção não
 * deveria estar criando banco para não ler nada.
 *
 * A origem é montada no `#[BeforeClass]` de cada classe, e não uma vez para todas.
 * Com uma montagem só, a primeira classe a rodar pagaria o DDL e as três seguintes
 * dependeriam da ordem de execução do PHPUnit, que não é contrato; e o
 * `#[AfterClass]` de uma delas derrubaria o banco das outras. O preço são ~220ms
 * por classe, contra uma dependência entre arquivos que falha em silêncio.
 */
trait TestSchemaCloneSyntheticOrigin
{
    use TestSchemaCloneShared;

    /**
     * O schema sintético, montado uma vez para os casos.
     *
     * `uniq_pai` existe por um motivo específico: o InnoDB exige que as colunas
     * da chave estrangeira sejam o prefixo mais à esquerda de um índice do PAI, e
     * a chave de duas colunas aponta para (id, seg) — o PRIMARY KEY (id) sozinho
     * não cobre as duas. Sem o índice, o `ADD CONSTRAINT` do clone é recusado e o
     * caso falha por um motivo que não é o que ele quer testar.
     */
    private static function createOrigin(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE `pai` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `seg` INT NOT NULL DEFAULT 0,
            `nome` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_pai` (`id`, `seg`)
        ) ENGINE=InnoDB');

        $pdo->exec('CREATE TABLE `filho` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `pai_id` INT NOT NULL,
            `pai_seg` INT NOT NULL DEFAULT 0,
            `nota` VARCHAR(10) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_filho_pai` (`pai_id`),
            CONSTRAINT `fk_simples` FOREIGN KEY (`pai_id`) REFERENCES `pai` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_composta` FOREIGN KEY (`pai_id`, `pai_seg`) REFERENCES `pai` (`id`, `seg`) ON UPDATE CASCADE
        ) ENGINE=InnoDB');

        $pdo->exec("INSERT INTO `pai` (`seg`, `nome`) VALUES (1, 'primeiro'), (2, 'segundo')");
        $pdo->exec("INSERT INTO `filho` (`pai_id`, `pai_seg`, `nota`) VALUES (1, 1, 'a'), (2, 2, 'b')");

        // A tabela de controle do Migrator, com a versão mais recente e mais nada.
        //
        // Não é decoração: ensureWorkerDatabase() se recusa a clonar um modelo que
        // a impressão digital diz estar velha, e essa verificação olha `migrations`
        // antes de olhar qualquer outra coisa. Sem esta tabela o clone seria
        // recusado por um motivo que não tem nada a ver com as constraints, e o
        // caso estaria medindo a guarda em vez do mecanismo.
        $pdo->exec('CREATE TABLE `migrations` (`version` VARCHAR(20) NOT NULL)');
        $pdo->exec(sprintf(
            "INSERT INTO `migrations` (`version`) VALUES ('%s')",
            SchemaFingerprint::latestMigrationVersion()
        ));
    }

    /**
     * A origem, montada uma vez e compartilhada pelos casos da classe.
     *
     * No `#[BeforeClass]` e não por caso, e a distinção é entre as duas metades do
     * banco: a origem é SÓ LIDA por tudo que isto faz — o clone faz
     * `CREATE TABLE ... LIKE` a partir dela, os `INSERT ... SELECT` copiam dela, e
     * o `describe()` só a lê. Nada aqui escreve nela, então montá-la por caso seria
     * o mesmo custo de DDL para chegar ao mesmo estado.
     *
     * O destino é o contrário, e é por isso que ele é apagado no `#[After]` de
     * cada caso: é nele que a cópia acontece, e a impressão digital que o
     * `ensureWorkerDatabase()` grava sobrevive à chamada. Sem apagar, o primeiro
     * caso que clonasse deixaria o destino em dia e o seguinte receberia um false
     * do `ensureWorkerDatabase()` — um teste que falha por causa da ordem em que o
     * PHPUnit escolheu rodar, que é a forma mais cara de teste quebrado.
     */
    #[BeforeClass]
    public static function setUpOrigin(): void
    {
        $test = TestDatabase::fromEnvironment();

        $test->recreate(self::origin());
        $test->drop(self::destination());
        $test->schemaFingerprint()->forget(self::origin());
        $test->schemaFingerprint()->forget(self::destination());

        self::createOrigin($test->pdo(self::origin()));

        // A impressão é gravada porque a origem foi montada agora, por estas
        // linhas. ensureWorkerDatabase() se recusa a clonar um modelo que
        // a impressão digital diz estar velha, e sem isto o caso mediria essa
        // guarda em vez do mecanismo de cópia.
        $test->schemaFingerprint()->record(self::origin());
    }

    /**
     * Apaga o destino antes e depois de cada caso que usa o banco.
     *
     * No `#[After]` e não num helper chamado pelo fim de cada caso: um caso que
     * falhe no meio deixa o destino para trás, e o próximo `composer test`
     * reencontraria um destino com a impressão digital certa — o que faria a
     * limpeza parecer funcionar por acaso.
     *
     * A impressão digital vai junto do banco porque é o que autoriza um banco
     * como atual: um banco apagado com a impressão no lugar faz a próxima
     * execução acreditar num schema que não existe. O `finally` cobre a falha
     * dentro da própria limpeza.
     */
    #[Before]
    public function clearDestination(): void
    {
        $this->dropDestination();
    }

    #[After]
    public function dropDestination(): void
    {
        $test = TestDatabase::fromEnvironment();

        try {
            $test->drop(self::destination());
        } finally {
            $test->schemaFingerprint()->forget(self::destination());
        }
    }

    #[AfterClass]
    public static function tearDownSyntheticSchema(): void
    {
        $test = TestDatabase::fromEnvironment();

        try {
            $test->drop(self::origin());
            $test->drop(self::destination());
        } finally {
            $test->schemaFingerprint()->forget(self::origin());
            $test->schemaFingerprint()->forget(self::destination());
        }
    }
}
