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
     * O DEFAULT declarado de uma coluna, ou null quando a coluna não tem um.
     *
     * A ausência é SQL NULL no `information_schema`, e é null aqui. Uma versão
     * anterior desta função também devolvia null para a string vazia, achando que
     * o MySQL 8 não distinguia as duas coisas; ele distingue, e a confusão custava
     * uma coluna que o MySQL mostra com `DEFAULT ''` e que outra ponta lê como
     * coluna sem default nenhum. O
     * sintoma é silencioso dos dois lados: quem lê null não sabe se a coluna tem
     * default vazio, e o gate de paridade declarava em paridade um default que
     * só existe de um lado.
     *
     * Um DEFAULT presente não é convertido: `NULL` como default chega como string
     * 'NULL', e `'0'` chega como '0'. A conversão fica de fora de propósito,
     * pelo mesmo motivo de columnType().
     */
    public static function columnDefault(PDO $pdo, string $database, string $table, string $column): ?string
    {
        $statement = $pdo->prepare(
            'SELECT column_default FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?'
        );
        $statement->execute([$database, $table, $column]);

        $default = $statement->fetchColumn();

        if ($default === false || $default === null) {
            return null;
        }

        return (string) $default;
    }

    /**
     * A definição completa de cada coluna, como mapa tabela => coluna => atributos.
     *
     * São seis atributos porque cada um deles já divergiu uma vez, ou divergiria em
     * silêncio se o gate não olhasse:
     *
     *   - `type`: o que o gate já comparava.
     *   - `default`: um `DEFAULT ''` que só existe de um lado muda o que a aplicação
     *     grava numa coluna que ninguém preenche. Foi o defeito que a migration
     *     20261005131000 removeu de `usuarios.cep`, e o gate não teria dito nada.
     *   - `charset` e `collation`: um banco em `latin1` e um em `utf8mb4` aceitam os
     *     mesmos tipos e devolvem as mesmas linhas, e a diferença só aparece quando
     *     alguém grava um emoji. Uma coluna numérica não tem charset, e daí o null.
     *   - `nullable`: muda o que o MySQL grava numa coluna que o código deixa vazia.
     *   - `extra`: `auto_increment` e as expressões geradas. Uma coluna `id` que um
     *     lado perdeu o `AUTO_INCREMENT` é um esquema que não insere.
     *
     * A ausência de default é null e não string vazia, e a distinção é do MySQL:
     * `information_schema` devolve SQL NULL para a coluna sem DEFAULT e a string
     * vazia para a coluna com `DEFAULT ''`. Ver columnDefault(), que é o mesmo
     * cuidado aplicado a uma coluna só.
     *
     * @return array<string, array<string, array{type: string, default: ?string, charset: ?string, collation: ?string, nullable: bool, extra: string}>>
     */
    public static function columnSchema(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            'SELECT table_name, column_name, column_type, column_default, is_nullable,
                    character_set_name, collation_name, extra
             FROM information_schema.columns
             WHERE table_schema = ? ORDER BY table_name, ordinal_position'
        );
        $statement->execute([$database]);

        $columns = [];

        foreach ($statement->fetchAll() as $row) {
            $default = $row['COLUMN_DEFAULT'];

            $columns[(string) $row['TABLE_NAME']][(string) $row['COLUMN_NAME']] = [
                'type' => (string) $row['COLUMN_TYPE'],
                'default' => $default === null ? null : (string) $default,
                'charset' => $row['CHARACTER_SET_NAME'] === null ? null : (string) $row['CHARACTER_SET_NAME'],
                'collation' => $row['COLLATION_NAME'] === null ? null : (string) $row['COLLATION_NAME'],
                'nullable' => (string) $row['IS_NULLABLE'] === 'YES',
                'extra' => (string) $row['EXTRA'],
            ];
        }

        return $columns;
    }

    /**
     * O schema da aplicação como mapa tabela => coluna => definição completa,
     * ordenado e sem as tabelas de controle.
     *
     * A forma que o gate de paridade compara. Antes comparava só o tipo, o que
     * deixava passar de tudo o que importa mais: um charset, um default e um
     * `AUTO_INCREMENT` divergente entre o banco.sql e as migrations é uma
     * instalação que se comporta diferente da atualização, e nenhuma delas erra
     * alto o bastante para alguém perceber.
     *
     * A ordem de colunas NÃO é comparada e nem entra aqui: `SHOW CREATE TABLE`
     * devolve as colunas na ordem do ordinal, e tanto o dump quanto a cadeia
     * produzem essa ordem, mas um `ALTER TABLE MODIFY` que mova uma coluna para o
     * fim muda a ordem sem mudar nada do que a aplicação enxerga.
     *
     * @return array<string, array<string, array{type: string, default: ?string, charset: ?string, collation: ?string, nullable: bool, extra: string}>>
     */
    public static function applicationSchema(PDO $pdo, string $database): array
    {
        $columns = self::columnSchema($pdo, $database);

        $schema = [];

        foreach (array_diff(self::tableNames($pdo, $database), self::CONTROL_TABLES) as $table) {
            $schema[$table] = $columns[$table] ?? [];
        }

        return $schema;
    }

    /**
     * A collation de cada tabela, como mapa tabela => collation.
     *
     * O companheiro do nível de coluna que applicationSchema() compara. Sem ele o
     * gate é cego para uma tabela sem coluna de texto — `itens_de_vendas` — porque
     * `information_schema.columns` devolve CHARACTER_SET_NAME null para toda coluna
     * numérica, e sobra pouco para comparar num schema em que o que diverge é o
     * DEFAULT da própria tabela. TABLE_COLLATION é onde esse defeito aparece.
     *
     * Mesma exclusão das tabelas de controle e a mesma disciplina de
     * ordenação de tableNames(): quem lê compara depois, e comparar exige ordem
     * estável.
     *
     * @return array<string, string> tabela => collation
     */
    public static function tableCollations(PDO $pdo, string $database): array
    {
        $statement = $pdo->prepare(
            "SELECT table_name, table_collation FROM information_schema.tables
             WHERE table_schema = ? AND table_type = 'BASE TABLE'
             ORDER BY table_name"
        );
        $statement->execute([$database]);

        $collations = [];

        foreach ($statement->fetchAll() as $row) {
            $collations[(string) $row['TABLE_NAME']] = (string) $row['TABLE_COLLATION'];
        }

        return array_diff_key($collations, array_flip(self::CONTROL_TABLES));
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
     * O `LIKE` chega aqui como argumento com o coringa que o chamador quer usar:
     * `%` atravessa sem ser escapado, porque é ele que torna `mapos_%_test` um
     * padrão de busca. `_` e `\` são escapados, e é esse escape que impede o
     * underscore do nome de um worker (`mapos_1_test`) de casar com qualquer
     * caractere — sem ele a busca traria `maposX1_test` junto, que o chamador pode
     * apagar.
     *
     * O `%` já esteve na lista de escape, e o defeito era silencioso: o padrão do
     * `drop-worker-databases.php` virava `mapos\_\%\_test`, não casava com banco
     * algum, e o script respondia "Nenhum banco de worker para apagar" com quatro
     * na frente. Escapar o coringa de quem chama é transformar a busca em nada.
     *
     * @return list<string>
     */
    public static function databasesLike(PDO $pdo, string $pattern): array
    {
        $escaped = str_replace(['\\', '_'], ['\\\\', '\\_'], $pattern);

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
