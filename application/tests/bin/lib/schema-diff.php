<?php

namespace Tests\Support\Database;

use RuntimeException;

/**
 * A diferença entre o schema de banco.sql e o da cadeia de migrations.
 *
 * Esta função já foi um defeito. Ela comparava a união dos nomes de tabela e, dentro
 * de cada uma, a união das colunas, e antes disso eram dois laços quase idênticos —
 * o segundo sem o laço interno de colunas. Uma coluna removida de um dos lados
 * aparecia como "coluna só no banco.sql" num caminho e ficava invisível no outro,
 * dependendo de qual schema fosse percorrido por último.
 *
 * O defeito era invisível porque a função vivia como closure no corpo de um script
 * procedural: pura, determinística, recebendo dois arrays, e impossível de testar
 * sem um banco no meio. Ela é a única parte do gate de paridade que decide se há
 * divergência, e foi a única parte que nunca teve teste.
 *
 * Virou uma função com assinatura explícita e um teste com casos sintéticos, que é
 * a forma de impedir que o próximo laço assimétrico entre. Morar num `require` e não
 * no autoloader é consequência de ser função: o autoloader de classes não acha
 * função, e o preço de registrá-la em `composer.json` para uma função só é maior
 * que o de um `require_once` explícito nos dois lugares que a chamam — o script de
 * paridade e o teste, que é quem a exercita.
 *
 * A pasta `bin/lib` é onde ela vive porque é onde o seu único consumidor de
 * produção vive; `SchemaDiffTest` a carrega daqui.
 */
/**
 * Os atributos de coluna que o gate compara, e a ordem em que aparecem na linha.
 *
 * A lista é uma constante e não um laço com os nomes espalhados pelo corpo
 * porque a mensagem é o produto desta função, e a ordem das mensagens é a
 * ordem em que alguém lê a saída do CI procurando qual migration corrigir.
 */
const SCHEMA_COLUMN_ATTRIBUTES = ['type', 'default', 'charset', 'collation', 'nullable', 'extra'];

/**
 * A largura do rótulo do atributo na linha do relatório.
 *
 * O `str_pad` usava SCHEMA_COLUMN_ATTRIBUTES[3], o que fazia a ordem da
 * constante ser load-bearing: reordenar os atributos mudaria o alinhamento do
 * relatório sem mudar o script. A largura é o comprimento do rótulo mais longo —
 * `collation`, o terceiro da lista — e é valor fixo de propósito, nomeado aqui
 * para o [3] não voltar.
 */
const SCHEMA_REPORT_WIDTH = 9;

if (! function_exists('Tests\Support\Database\schemaDiffBetween')) {
    /**
     * As diferenças entre os dois schemas, como linhas legíveis.
     *
     * Percorre a união dos nomes de tabela e, dentro de cada uma, a união das
     * colunas, de modo que "só no banco.sql" e "só nas migrações" saem do mesmo
     * laço. A ordem de entrada dos arrays não muda o resultado, que é o que permite
     * o teste comparar a saída contra uma lista fixa.
     *
     * Dentro da coluna, cada atributo divergente vira uma linha própria, nomeada.
     * A alternativa — uma linha por coluna com tudo junto — foi descartada: um
     * `DEFAULT` divergente e um `collation` divergente na mesma coluna produziam
     * uma linha que não dizia qual dos dois era, e quem lê teria de abrir os dois
     * lados para saber qual resolver. Nomear o atributo é o que faz a linha
     * apontar para a migration.
     *
     * @param  array<string, array<string, array<string, mixed>>> $dump       mapa do banco.sql
     * @param  array<string, array<string, array<string, mixed>>> $migrations mapa das migrations
     * @return list<string>
     */
    function schemaDiffBetween(array $dump, array $migrations): array
    {
        $diff = [];

        foreach (array_unique([...array_keys($dump), ...array_keys($migrations)]) as $table) {
            if (! isset($migrations[$table])) {
                $diff[] = "tabela só no banco.sql: {$table}";

                continue;
            }

            if (! isset($dump[$table])) {
                $diff[] = "tabela só nas migrações: {$table}";

                continue;
            }

            $dumpColumns = $dump[$table];
            $migrationColumns = $migrations[$table];

            foreach (array_unique([...array_keys($dumpColumns), ...array_keys($migrationColumns)]) as $column) {
                if (! isset($migrationColumns[$column])) {
                    $diff[] = "coluna só no banco.sql: {$table}.{$column} {$dumpColumns[$column]['type']}";

                    continue;
                }

                if (! isset($dumpColumns[$column])) {
                    $diff[] = "coluna só nas migrações: {$table}.{$column} {$migrationColumns[$column]['type']}";

                    continue;
                }

                foreach (SCHEMA_COLUMN_ATTRIBUTES as $attribute) {
                    if (! array_key_exists($attribute, $dumpColumns[$column])
                        || ! array_key_exists($attribute, $migrationColumns[$column])
                    ) {
                        throw new RuntimeException(
                            "atributo '{$attribute}' ausente de {$table}.{$column}. "
                            . 'O schema lido não tem o atributo que o gate compara, e é defeito '
                            . 'de código, não divergência de schema.'
                        );
                    }

                    $left = $dumpColumns[$column][$attribute];
                    $right = $migrationColumns[$column][$attribute];

                    if (! schemaAttributeMatches($attribute, $left, $right)) {
                        $diff[] = sprintf(
                            '%s divergente: %-24s banco.sql=%-18s migracoes=%s',
                            str_pad($attribute, SCHEMA_REPORT_WIDTH),
                            "{$table}.{$column}",
                            schemaAttributeForReport($left),
                            schemaAttributeForReport($right)
                        );
                    }
                }
            }
        }

        return $diff;
    }

    /**
     * Dois valores do mesmo atributo são iguais, dentro da regra do atributo.
     *
     * `type`, `charset`, `collation` e `extra` são case-insensitive no MySQL —
     * `SHOW CREATE TABLE` e `information_schema` grafam o mesmo valor do mesmo
     * jeito, mas o dump passou por um `DEFAULT ''` reescrito à mão em outro
     * momento da história do arquivo. Comparar com caixa seria um "divergente"
     * que ninguém consegue corrigir.
     *
     * O `default` é a exceção que o docblock abaixo documentava e o código não
     * implementava: `'ADMIN'` e `'admin'` são valores de dado diferentes, e um
     * DEFAULT com caixa trocada muda o que a aplicação grava numa coluna que
     * ninguém preenche. Por isso a comparação é `===` na string, fora do caminho
     * case-insensitive.
     */
    function schemaAttributeMatches(string $attribute, mixed $left, mixed $right): bool
    {
        if ($attribute === 'default') {
            return $left === $right;
        }

        return schemaValuesCaseInsensitiveEqual($left, $right);
    }

    /**
     * Dois valores são iguais ignorando a caixa, com os casos não-textuais na mão.
     *
     * bool e null não passam pelo `strcasecmp`: false viraria `(string)false ===
     * ''`, dizendo que um nullable SIM é igual a um NAO, e dois null são iguais
     * entre si e diferentes de qualquer string — uma coluna numérica sem charset
     * não é a string vazia.
     */
    function schemaValuesCaseInsensitiveEqual(mixed $left, mixed $right): bool
    {
        if (is_bool($left) || is_bool($right)) {
            return $left === $right;
        }

        if ($left === null || $right === null) {
            return $left === $right;
        }

        return strcasecmp((string) $left, (string) $right) === 0;
    }

    /**
     * O valor de um atributo como ele aparece na linha do relatório.
     *
     * Os três casos que precisam de tratamento são null, bool e string vazia, e
     * cada um deles sem tratamento produz uma linha que mente:
     *
     *   - null viraria `''` no `%s` do sprintf, e a linha passaria a dizer que uma
     *     coluna sem DEFAULT diverge de outra com `DEFAULT ''`, que é uma diferença
     *     que existe e que este gate precisa enxergar.
     *   - false viraria `''`, e o relatório leria "coluna nullable divergente" sem
     *     dizer de qual lado.
     *   - string vazia viraria nada visível, e a diferença saía da tela: foi
     *     exatamente o `DEFAULT ''` de `usuarios.cep` que a migration removeu.
     *
     * O relatório é lido por alguém com o CI aberto e sem o schema ao lado, então
     * nenhum valor pode sumir da linha.
     */
    function schemaAttributeForReport(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'SIM' : 'NAO';
        }

        $string = (string) $value;

        return $string === '' ? "''" : $string;
    }

    /**
     * As diferenças de collation entre as duas pontas, no nível da tabela.
     *
     * A irmã de schemaDiffBetween, para o atributo que não cabe em coluna nenhuma:
     * a collation declarada na tabela. Uma tabela sem coluna de texto — como
     * `itens_de_vendas` — não tem CHARACTER_SET_NAME em coluna alguma, então a
     * comparação de colunas não enxerga um `DEFAULT CHARACTER SET` divergente
     * entre banco.sql e as migrations. É o buraco de defeito 2; o `TABLE_COLLATION`
     * é onde ele aparece.
     *
     * A presença de tabela NÃO é relatada aqui: ela é a meia dúzia de linhas "só
     * no banco.sql" / "só nas migrações" da irmã, e dizer a mesma coisa duas
     * vezes por tabela não ajuda quem lê o relatório. Só a collation divergente
     * entre tabelas que existem nas duas pontas vira linha.
     *
     * @param  array<string, string> $dump       mapa do banco.sql
     * @param  array<string, string> $migrations mapa das migrations
     * @return list<string>
     */
    function schemaTableDiffBetween(array $dump, array $migrations): array
    {
        $diff = [];

        foreach ($dump as $table => $left) {
            if (! array_key_exists($table, $migrations)) {
                continue;
            }

            $right = $migrations[$table];

            if (strcasecmp((string) $left, (string) $right) !== 0) {
                $diff[] = sprintf(
                    'collation divergente: %-24s banco.sql=%-18s migracoes=%s',
                    $table,
                    schemaAttributeForReport($left),
                    schemaAttributeForReport($right)
                );
            }
        }

        return $diff;
    }
}
