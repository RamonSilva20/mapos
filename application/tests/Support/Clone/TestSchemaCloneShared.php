<?php

namespace Tests\Support\Clone;

use PDO;
use Tests\Support\Database\DatabaseGuard;
use Tests\Support\Database\SchemaReader;

/**
 * Os nomes dos bancos sintéticos e a comparação entre dois deles.
 *
 * Não tem DDL e não tem hook de ciclo de vida, e é por isso que os quatro arquivos
 * de clone podem usar esta trait: ela só responde "qual é o nome" e "o que tem
 * dentro", e as duas perguntas servem mesmo quando o schema sintético não existe
 * — que é o caso dos testes de guarda, que decidem por nome e por impressão digital
 * sem nunca olhar uma tabela.
 *
 * O que monta e desmonta o banco está em TestSchemaCloneSyntheticOrigin, em trait
 * separada, porque esse lado custa ~220ms por classe e as guardas não pagam por ele.
 */
trait TestSchemaCloneShared
{
    /**
     * A base do schema de origem, e a do de destino, dos casos sintéticos.
     *
     * São BASES, e não os nomes: o token entra no meio, por `databaseName()`. Com o
     * token hardcoded no nome, os quatro processos do ParaTest derrubariam e
     * remontariam o MESMO `mapos_clone_origem_test` ao mesmo tempo — um veria a
     * origem pela metade, o `ensureWorkerDatabase()` copiaria um schema
     * incompleto e a paridade acusaria uma diferença que não existe. Foi o que
     * aconteceu, e a contenção de DDL custou 7,1s com 4 processos.
     *
     * As duas bases são jogáveis e mantêm o `_test` do fim pelo mesmo motivo de
     * sempre: assertDatabaseNameIsSafe() roda antes de qualquer conexão, e um nome
     * fora do padrão aborta o processo inteiro em vez de testar o que pretende.
     * `databaseName()` reconstrói o mesmo nome que a produção constrói, sufixo
     * incluso, porque quem decide é workerDatabaseName() e não este arquivo.
     */
    private const ORIGIN_BASE = 'mapos_clone_origem';

    private const DESTINATION_BASE = 'mapos_clone_destino';

    /**
     * O nome do banco de um worker deste arquivo, com o token do processo.
     *
     * `solo` é o token da execução de processo único, e o nome que sai é
     * `mapos_clone_origem_solo_test` — não o `mapos_clone_origem_test` de antes.
     * Ele não é byte a byte o mesmo, e isso é o que permite ler esta linha sem
     * procurar o resto do arquivo para descobrir de onde veio o nome.
     */
    private static function databaseName(string $base): string
    {
        return DatabaseGuard::workerName($base);
    }

    private static function origin(): string
    {
        return self::databaseName(self::ORIGIN_BASE);
    }

    private static function destination(): string
    {
        return self::databaseName(self::DESTINATION_BASE);
    }

    /**
     * O que um banco tem, de um jeito comparável com o de outro.
     *
     * Fica aqui, e não no TestSchemaClone, porque nada fora deste arquivo precisa
     * descrever um banco. Em produção era superfície pública sem um único
     * consumidor, e o efeito era o pior dos dois: indexCounts() e rowCount()
     * pareciam utilitários de produção quando existem para sustentar um assert.
     *
     * As linhas entram na comparação de propósito. Um worker com a mesma forma e
     * menos linhas é um clone que pulou alguma cópia, e o lugar para suspeitar
     * disso é aqui — e não no próximo teste que falha por falta de uma fixture.
     *
     * Não entram: charset, collation e o tipo do índice. `CREATE TABLE ... LIKE`
     * copia os três, e compará-los só produziria uma diferença que ninguém
     * consegue provocar.
     *
     * @return array{tabelas: array<string, array{colunas: array<string, string>, indices: int, linhas: int}>, chaves_estrangeiras: array<string, string>}
     */
    private static function describe(PDO $pdo, string $database): array
    {
        $columns = SchemaReader::columnTypes($pdo, $database);
        $indexes = self::indexCounts($pdo, $database);

        $tables = [];

        foreach (SchemaReader::tableNames($pdo, $database) as $table) {
            $tables[$table] = [
                'colunas' => $columns[$table] ?? [],
                'indices' => $indexes[$table] ?? 0,
                'linhas' => (int) $pdo
                    ->query(sprintf('SELECT COUNT(*) AS total FROM %s', SchemaReader::qualified($database, $table)))
                    ->fetch()['total'],
            ];
        }

        return [
            'tabelas' => $tables,
            'chaves_estrangeiras' => TestSchemaClone::describeForeignKeys($pdo, $database),
        ];
    }

    /**
     * Quantos índices cada tabela tem, contados por par (tabela, índice).
     *
     * Delegado a `SchemaReader::indexCounts()`, que é onde a consulta vive. Ela
     * ficava escrita aqui e é a mesma que o `SchemaReader` existe para evitar:
     * duas leituras de `information_schema.statistics` podem divergir no `DISTINCT`
     * e ninguém perceberia, porque a divergência apareceria como "o clone perdeu um
     * índice", que é uma conclusão plausível demais para chamar a atenção.
     *
     * @return array<string, int>
     */
    private static function indexCounts(PDO $pdo, string $database): array
    {
        return SchemaReader::indexCounts($pdo, $database);
    }
}
