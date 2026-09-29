<?php

namespace Tests\Support\Database;

use PDO;
use RuntimeException;

/**
 * A única leitura de metadados de schema da suíte.
 *
 * Três regras vêm do fato de as leituras terem sido várias, e cada uma delas
 * continua valendo agora que há uma:
 *
 *   - o gate de paridade pula a tabela `migrations` e o clone não, e não pode ser ao
 *     contrário: o clone precisa copiá-la, porque é dela que vem a versão que o
 *     SchemaFingerprint::isCurrent() confere no worker. Daí as duas entradas,
 *     tableNames() e applicationSchema(), em vez de um filtro escondido.
 *   - nenhum nome de tabela ou coluna é interpolado no SQL. É a forma de injeção que
 *     o projeto proíbe, e o identifier() existe para barrá-la.
 *   - lista de tabelas e lista de tipos vêm da mesma fonte, e não de SHOW e
 *     information_schema, que podem divergir sobre o que existe sem ninguém
 *     perceber.
 *
 * As chaves saem de information_schema em maiúscula porque é assim que o MySQL as
 * entrega: escrever `SELECT table_name` devolve a chave `TABLE_NAME`, e o nome
 * digitado na consulta não é o que volta. Não é uma peculiaridade que valha ser
 * lembrada — é o tipo de coisa que produz um aviso em vez de um erro, e
 * `failOnWarning` transforma isso em teste vermelho.
 */
final class SchemaReader
{
    /**
     * Tabelas que não são schema da aplicação.
     *
     * A tabela de controle do Migrator do CI3, e só ela. Ela não existe num banco
     * recém-criado: passa a existir depois da primeira migration, então contá-la
     * como parte do schema faria um banco parecer divergente por causa do próprio
     * histórico de instalação, e não por causa de uma diferença entre banco.sql e
     * as migrations.
     *
     * Excluída de applicationSchema() e não de tableNames() — o clone copia
     * `migrations` de propósito, e é a versão dela que autoriza o reaproveitamento
     * do worker na execução seguinte.
     */
    public const CONTROL_TABLES = ['migrations'];

    /**
     * As tabelas de base do banco, em ordem estável, tabelas de controle inclusive.
     *
     * `table_type = 'BASE TABLE'` porque uma VIEW não se copia com
     * `CREATE TABLE ... LIKE` e não é o que a cadeia de migrations constrói.
     *
     * @return list<string>
     */
    public static function tableNames(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = ? AND table_type = 'BASE TABLE'
             ORDER BY table_name"
        );
        $statement->execute([$database]);

        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * As colunas de cada tabela, como mapa tabela => coluna => tipo.
     *
     * Tabelas de controle inclusive: quem filtra é quem sabe por quê, e o clone
     * precisa delas.
     *
     * @return array<string, array<string, string>> tabela => coluna => tipo
     */
    public static function columnTypes(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            'SELECT table_name, column_name, column_type FROM information_schema.columns
             WHERE table_schema = ? ORDER BY table_name, ordinal_position'
        );
        $statement->execute([$database]);

        $columns = [];

        foreach ($statement->fetchAll() as $row) {
            $columns[(string) $row['TABLE_NAME']][(string) $row['COLUMN_NAME']] = (string) $row['COLUMN_TYPE'];
        }

        return $columns;
    }

    /**
     * O tipo de uma coluna, ou null se a coluna não existe.
     *
     * Existe para o teste que afirma um valor absoluto sobre o schema. Ele lia
     * information_schema com a sua própria consulta, e isso é a forma de divergência
     * que a classe se propõe a eliminar: uma segunda leitura pode perguntar a fonte
     * errada, esquecer o filtro `table_schema`, ou responder diferente da outra
     * numa correção futura, e o sintoma seria um teste que passa com um schema que
     * o resto da suíte leu de outro jeito.
     *
     * O tipo sai cru, como em columnTypes(), e a comparação decide o que fazer com a
     * caixa. O gate de paridade usa strcasecmp() e o SchemaTest usa strtolower(), e
     * normalizar aqui deixaria um dos dois clientes acreditando que a normalização
     * não existe.
     */
    public static function columnType(PDO $pdo, string $database, string $table, string $column): ?string
    {
        $statement = $pdo->prepare(
            'SELECT column_type FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?'
        );
        $statement->execute([$database, $table, $column]);

        $type = $statement->fetchColumn();

        return $type === false ? null : (string) $type;
    }

    /**
     * O schema da aplicação como mapa tabela => coluna => tipo, ordenado e sem as
     * tabelas de controle.
     *
     * A forma que o gate de paridade compara, e a única que o projeto documenta
     * como "o schema": só definição de coluna. Charset, collation e ordem de
     * colunas mudam entre o dump e o dbforge sem significar divergência, e compará-los
     * só produziria ruído.
     *
     * @return array<string, array<string, string>>
     */
    public static function applicationSchema(PDO $pdo, string $database): array
    {
        $columns = self::columnTypes($pdo, $database);

        $schema = [];

        foreach (array_diff(self::tableNames($pdo, $database), self::CONTROL_TABLES) as $table) {
            $schema[$table] = $columns[$table] ?? [];
        }

        return $schema;
    }

    /**
     * O banco existe, com nome exato.
     *
     * Existia como um `SELECT COUNT(*)` de `SCHEMATA` escrito à mão em dois lugares:
     * o `SchemaFingerprint::isCurrent()` e o `drop-worker-databases.php`. As duas
     * leituras podiam divergir — uma contava, a outra filtrava por `LIKE` — e a
     * divergência de uma delas aparece como "o banco não existe" num contexto que
     * trata isso como "o schema precisa ser remontado", que é uma decisão tomada por
     * um número que ninguém conferiu.
     */
    public static function databaseExists(PDO $pdo, string $database): bool
    {
        $statement = $pdo->prepare(
            'SELECT COUNT(*) AS total FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?'
        );
        $statement->execute([$database]);

        return (int) $statement->fetch()['total'] > 0;
    }

    /**
     * Os bancos cujo nome casa com um padrão, em ordem estável.
     *
     * O `LIKE` chega aqui como argumento, e o `ESCAPE` do MySQL é o que permite
     * que a base com underscore seja comparada como wildcard. Sem o `ESCAPE`, um
     * nome de base com `_` casaria com qualquer caractere, e a busca por
     * `mapos_1_test` traria `maposX1_test` junto — que o chamador pode apagar.
     *
     * O `default` do escape é o que torna isso seguro: sem o segundo argumento de
     * `LIKE`, o MySQL usa a barra invertida, e o padrão que o chamador escreve já
     * precisa ter escapado os coringas. Por isso o método escapa `\`, `_` e `%` do
     * padrão recebido, e quem chama passa a base como está.
     *
     * @return list<string>
     */
    public static function databasesLike(PDO $pdo, string $pattern): array
    {
        $escaped = str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $pattern);

        $statement = $pdo->prepare(
            'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA
              WHERE SCHEMA_NAME LIKE ? ORDER BY SCHEMA_NAME'
        );
        $statement->execute([$escaped]);

        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * As chaves estrangeiras, como mapa nome => definição.
     *
     * Uma constraint por entrada, e não uma linha por coluna: a de duas colunas
     * aparece duas vezes em `KEY_COLUMN_USAGE`, e as linhas contíguas do `ORDER BY`
     * são o que permite agrupá-las sem índice intermediário. A ordem de entrada é a
     * do `ORDER BY` (tabela, nome, posição) e ela é a que `replayForeignKeys()`
     * consome, e por isso o `ksort` fica em `describeForeignKeys()`, que só quer
     * comparação estável.
     *
     * Esta consulta já era a de `TestSchemaClone::foreignKeyConstraints()`, e é a
     * mesma que `TestSchemaCloneWorkerParityTest` escrevia por conta própria para
     * ler `REFERENCED_TABLE_SCHEMA`. Duas leituras da mesma pergunta podem
     * divergir no `JOIN` ou no filtro, e a divergência aparece como um clone que
     * escreve uma coisa e uma conferência que valida outra.
     *
     * @return array<string, array{table: string, columns: list<string>, referenced_table: string, referenced_columns: list<string>, update_rule: string, delete_rule: string}>
     */
    public static function foreignKeys(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            'SELECT kcu.CONSTRAINT_NAME, kcu.TABLE_NAME, kcu.COLUMN_NAME,
                    kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME,
                    rc.UPDATE_RULE, rc.DELETE_RULE
             FROM information_schema.KEY_COLUMN_USAGE kcu
             JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
               ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
              AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
              AND rc.TABLE_NAME = kcu.TABLE_NAME
             WHERE kcu.TABLE_SCHEMA = ? AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
             ORDER BY kcu.TABLE_NAME, kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION'
        );
        $statement->execute([$database]);

        $constraints = [];

        foreach ($statement->fetchAll() as $row) {
            $name = (string) $row['CONSTRAINT_NAME'];

            $constraints[$name] ??= [
                'table' => (string) $row['TABLE_NAME'],
                'columns' => [],
                'referenced_table' => (string) $row['REFERENCED_TABLE_NAME'],
                'referenced_columns' => [],
                'update_rule' => (string) $row['UPDATE_RULE'],
                'delete_rule' => (string) $row['DELETE_RULE'],
            ];

            $constraints[$name]['columns'][] = (string) $row['COLUMN_NAME'];
            $constraints[$name]['referenced_columns'][] = (string) $row['REFERENCED_COLUMN_NAME'];
        }

        return $constraints;
    }

    /**
     * Os schemas de tabela que as chaves estrangeiras referenciam, distintos.
     *
     * Existe para a pergunta que `describeForeignKeys()` não responde: se as
     * constraints de um banco apontam para DENTRO dele. A descrição compara worker
     * contra worker e por isso não traz o schema de propósito, já que o nome do
     * banco de cada um é justamente o que difere. Ler `REFERENCED_TABLE_SCHEMA` é o
     * que enxerga uma constraint que aponta para o modelo: ela é bem formada,
     * `describe()` passa, e o filho órfão também passa — que é o defeito que
     * qualifier as duas pontas do `ALTER` previne.
     *
     * @return list<string>
     */
    public static function foreignKeyTargetSchemas(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            'SELECT DISTINCT REFERENCED_TABLE_SCHEMA
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE CONSTRAINT_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL'
        );
        $statement->execute([$database]);

        return array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Quantos índices cada tabela tem, contados por par (tabela, índice).
     *
     * O `DISTINCT` não é estilo: o `information_schema.statistics` repete uma linha
     * por coluna do índice, e a contagem de linhas é a de colunas, não a de índices.
     *
     * @return array<string, int>
     */
    public static function indexCounts(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            'SELECT table_name, COUNT(DISTINCT index_name) AS total FROM information_schema.statistics
              WHERE table_schema = ? GROUP BY table_name'
        );
        $statement->execute([$database]);

        $counts = [];

        foreach ($statement->fetchAll() as $row) {
            $counts[(string) $row['TABLE_NAME']] = (int) $row['total'];
        }

        return $counts;
    }

    public static function qualified(string $database, string $table): string
    {
        return self::identifier($database) . '.' . self::identifier($table);
    }

    /**
     * Um identificador entre crases, recusando o que não seja um nome.
     *
     * Os nomes vêm do information_schema e do nome do banco, que já passou por
     * assertDatabaseNameIsSafe(). A verificação existe porque concatenar nome de
     * tabela em SQL é a forma de injeção que o projeto proíbe, e um
     * information_schema comprometeria a garantia de todo: um dia uma tabela se
     * chamar `` `x`; DROP ... `` e quem monta o SQL passa a executar o que o nome
     * mandava. A recusa é muito mais barata que confiar.
     *
     * A resposta é a de `DatabaseGuard::isSafeIdentifier()`, e é a mesma por um
     * motivo só: as duas perguntas são a mesma pergunta — "isto pode entrar num SQL
     * como identificador?" — e eram respondidas por duas regex em dois arquivos. Um
     * dia alguém aceita um caractere novo numa e a outra continua recusando, e o
     * lugar onde a regra é mais frouxa é onde o buraco se abre.
     */
    public static function identifier(string $identifier): string
    {
        if (! DatabaseGuard::isSafeIdentifier($identifier)) {
            throw new RuntimeException(
                "Identificador fora do esperado: '{$identifier}'. "
                . 'Esperado apenas letras, dígitos e sublinhado, porque o nome entra no SQL.'
            );
        }

        return '`' . $identifier . '`';
    }
}
