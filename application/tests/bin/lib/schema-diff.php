<?php

namespace Tests\Support\Database;

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
if (! function_exists('Tests\Support\Database\schemaDiffBetween')) {
    /**
     * As diferenças entre os dois schemas, como linhas legíveis.
     *
     * Percorre a união dos nomes de tabela e, dentro de cada uma, a união das
     * colunas, de modo que "só no banco.sql" e "só nas migrações" saem do mesmo
     * laço. A ordem de entrada dos arrays não muda o resultado, que é o que permite
     * o teste comparar a saída contra uma lista fixa.
     *
     * @param  array<string, array<string, string>> $dump       mapa do banco.sql
     * @param  array<string, array<string, string>> $migrations mapa das migrations
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
                    $diff[] = "coluna só no banco.sql: {$table}.{$column} {$dumpColumns[$column]}";
                } elseif (! isset($dumpColumns[$column])) {
                    $diff[] = "coluna só nas migrações: {$table}.{$column} {$migrationColumns[$column]}";
                } elseif (strcasecmp($dumpColumns[$column], $migrationColumns[$column]) !== 0) {
                    $diff[] = sprintf(
                        'tipo divergente: %-24s banco.sql=%-18s migracoes=%s',
                        "{$table}.{$column}",
                        $dumpColumns[$column],
                        $migrationColumns[$column]
                    );
                }
            }
        }

        return $diff;
    }
}
