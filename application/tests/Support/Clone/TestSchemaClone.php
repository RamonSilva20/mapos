<?php

namespace Tests\Support\Clone;

use PDO;
use RuntimeException;

use Tests\Support\Database\DatabaseGuard;
use Tests\Support\Database\SchemaReader;
use Tests\Support\Database\TestDatabase;

/**
 * Copia o banco modelo para o banco de um processo do ParaTest.
 *
 * A execução paralela não pode dividir o banco entre os processos, e o motivo é
 * concreto: LoginControllerTest e BaselineDataResetTest rodam resetBaselineData(),
 * que faz `DELETE FROM usuarios` e reinsere as fixtures dentro da transação do
 * caso. Isso é um X-lock do InnoDB nas mesmas linhas, segurado pelo teste inteiro,
 * e o segundo processo espera innodb_lock_wait_timeout. Montar um banco por
 * processo pela cadeia de migrations custaria ~9,8s por worker; o worker recebe uma
 * cópia do `mapos_test` que o setup-db.php já monta. Ver AGENTS.md, que tem o
 * porquê do ParaTest não ser o default e a tabela de custo.
 *
 * ## As três etapas, e por que nesta ordem
 *
 * `CREATE TABLE ... LIKE` nas 28 tabelas, `INSERT ... SELECT` de cada uma, e
 * `ALTER TABLE ... ADD CONSTRAINT` para as 26 chaves estrangeiras.
 *
 * ## O que `CREATE TABLE ... LIKE` não copia
 *
 * Ele copia colunas, tipos, defaults e índices. Ele NÃO copia chaves estrangeiras.
 * Um clone só com `LIKE` teria 28 tabelas iguais e zero constraints, e continuaria
 * passando a suíte inteira: apenas aceitaria escritas que a produção rejeita, que é
 * a classe de bug que esta suíte existe para pegar.
 *
 * ## Por que as chaves por último
 *
 * `ADD CONSTRAINT` valida as linhas que já existem, e recriá-las depois dos dados
 * faz o MySQL conferir cada linha copiada. Um clone com dado inconsistente morre
 * aqui, nomeando a linha culpada, em vez de chegar a um teste e falhar por falta
 * de uma fixture. E os dados antes das chaves porque só assim a ordem das 28
 * inserções é irrelevante — com as constraints presentes, a cópia teria de
 * respeitar a árvore de dependências, e `anexos` antes de `os` não copia.
 *
 * ## As alternativas, medidas antes de virarem comentário
 *
 * Trazer o DDL do modelo com `SHOW CREATE TABLE` e criar a chave junto do CREATE
 * economizaria os 26 ALTERs, mas as tabelas passam a ter de ser criadas na ordem em
 * que uma aponta para a outra, e em ordem alfabética isso morre na segunda:
 * `anexos` referencia `os`, que ainda não existe.
 *
 * E criar as chaves com as tabelas ainda vazias, trocando a ordem dos dados e das
 * chaves, não ajuda: as 26 chaves em tabelas vazias custaram 2584 ms, contra
 * 2549 ms com os dados já dentro — o custo é o overhead de DDL do InnoDB por
 * ALTER TABLE (cerca de 100 ms por statement), não a contagem de linhas.
 *
 * A cópia é feita para todas as tabelas, sem consultar
 * `information_schema.tables.table_rows` para decidir o que está vazio: em InnoDB
 * essa coluna é uma estimativa e chega a vir 0 numa tabela que tem linhas, e
 * pular a cópia produziria um worker que falha apontando para o lugar errado.
 * Vinte e oito `INSERT ... SELECT` sobre tabelas de fixture custam 33 ms, menos
 * que a contagem que teria que decidir isso.
 */
final class TestSchemaClone
{
    public function __construct(private readonly TestDatabase $test)
    {
    }

    /**
     * Garante que o banco do worker exista e seja cópia do modelo. Devolve se clonou.
     *
     * Devolve `false` em dois casos: os nomes são o mesmo (execução de processo
     * único, que já usa o modelo direto), ou o worker já existe e está em dia (uma
     * segunda execução, que não deve pagar a cópia de novo). Qualquer outro estado
     * é reconstruído, e a lista é curta de propósito: acrescentar um terceiro caso
     * aqui é acrescentar um estado em que a cópia deixa de acontecer, e esse
     * estado precisa ser nomeado junto com o motivo de a cópia ser desnecessária.
     *
     * Se o modelo não estiver em dia, isto falha em vez de montar. Montar aqui
     * seria autoparalelismo: N processos percebem o modelo velho ao mesmo tempo e
     * cada um roda a cadeia de ~9,8s de migrations sobre o mesmo banco, o que é
     * ao mesmo tempo lentíssimo e uma corrida de DDL. A correção é uma linha na
     * ponta (`composer test:db`), e a mensagem diz exatamente qual.
     */
    public function ensureWorkerDatabase(string $worker, string $template): bool
    {
        if ($worker === $template) {
            return false;
        }

        DatabaseGuard::assertDatabaseNameIsSafe($worker);
        DatabaseGuard::assertDatabaseNameIsSafe($template);

        if ($this->test->schemaFingerprint()->isCurrent($worker)) {
            return false;
        }

        if (! $this->test->schemaFingerprint()->isCurrent($template)) {
            throw new RuntimeException(
                "O banco modelo '{$template}' não está em dia, então o clone para '{$worker}' seria "
                . 'feito a partir de um schema velho — e um worker velho não é um worker, é um teste '
                . "que passa ou falha por um motivo que não tem nada a ver com o código. Rode "
                . "'composer test:db' antes da execução paralela. Esta classe não monta o schema de "
                . 'propósito: N processos montando ao mesmo tempo sobre o mesmo banco seria uma corrida '
                . 'de DDL, não uma montagem.'
            );
        }

        // A impressão digital vai com o banco. Sem esta linha, um worker deixado
        // por uma execução anterior com a impressão de uma versão diferente do
        // modelo seria recreate()'ado — que é o certo — mas o caminho inverso, um
        // worker recreate()'ado cuja impressão ficou, faria isCurrent()
        // aprovar um banco meio montado.
        $this->test->schemaFingerprint()->forget($worker);
        $this->test->recreate($worker);

        $pdo = $this->test->pdo();
        $tables = SchemaReader::tableNames($pdo, $template);

        foreach ($tables as $table) {
            $pdo->exec(sprintf(
                'CREATE TABLE %s LIKE %s',
                SchemaReader::qualified($worker, $table),
                SchemaReader::qualified($template, $table)
            ));
        }

        foreach ($tables as $table) {
            $pdo->exec(sprintf(
                'INSERT INTO %s SELECT * FROM %s',
                SchemaReader::qualified($worker, $table),
                SchemaReader::qualified($template, $table)
            ));
        }

        self::replayForeignKeys($pdo, $worker, $template);

        // Só depois de tudo dar certo. A impressão é o que autoriza a próxima
        // execução a pular a cópia, e gravada antes do fim ela aprobaria um
        // worker pela metade.
        $this->test->schemaFingerprint()->record($worker);

        return true;
    }

    /**
     * Recria as chaves estrangeiras do modelo no worker.
     *
     * Os nomes são preservados de propósito: a comparação do describe() é por
     * nome, e um clone cujas constraints tivessem sido renomeadas passaria numa
     * conferência por contagem e falharia nesta. Em MySQL o nome da constraint é
     * por schema, então repetir o nome do modelo no worker não colide com nada.
     *
     * Os índices exigidos pela constraint vêm junto no passo 1, porque
     * `CREATE TABLE ... LIKE` copia índices — e é por isso que o InnoDB aceita
     * recriar a chave sem pedir um índice novo.
     */
    private static function replayForeignKeys(PDO $pdo, string $worker, string $template): void
    {
        foreach (self::foreignKeyConstraints($pdo, $template) as $name => $constraint) {
            $pdo->exec(self::addForeignKeyStatement($worker, (string) $name, $constraint));
        }
    }

    /**
     * As chaves estrangeiras do banco, agrupadas por constraint.
     *
     * A consulta e o agrupamento vivem em `SchemaReader::foreignKeys()`, e é por
     * isso que os dois consumidores concordam: `replayForeignKeys()` escreve as
     * constraints no worker e `describeForeignKeys()` confere as que chegaram, e as
     * duas já foram cópia uma da outra — 26 linhas idênticas exceto pelo nome da
     * variável. Essa cópia era um defeito esperando: editar o `JOIN` em uma delas e
     * não na outra fazia a conferência validar uma consulta diferente da que escreveu
     * os dados, que é exatamente a classe de bug que o `describe()` existe para
     * pegar. Uma consulta só, e a impossibilidade de divergirem volta a existir.
     *
     * Este método virou a delegação, porque o `SchemaReader` é o lugar declarado
     * para a leitura e havia mais uma cópia da mesma consulta no
     * `TestSchemaCloneWorkerParityTest`, lendo `REFERENCED_TABLE_SCHEMA` com o
     * mesmo `JOIN`. Duas leituras da mesma pergunta podem divergir, e a divergência
     * de uma delas aparece como um clone que gravou uma coisa e uma conferência que
     * validou outra.
     *
     * A ordem de entrada é a do `ORDER BY` da consulta (tabela, nome, posição) e ela
     * é intencional: `replayForeignKeys()` aplica os `ALTER` nessa ordem. O `ksort`
     * fica em `describeForeignKeys()`, que só quer comparação estável, e não aqui.
     *
     * @return array<string, array{table: string, columns: list<string>, referenced_table: string, referenced_columns: list<string>, update_rule: string, delete_rule: string}>
     */
    private static function foreignKeyConstraints(PDO $pdo, string $database): array
    {
        return SchemaReader::foreignKeys($pdo, $database);
    }

    /**
     * O `ALTER TABLE` que recria uma chave estrangeira, montado de fora do banco.
     *
     * A montagem é uma função separada e pública para poder ser testada sem um
     * banco: o erro que importa aqui não é a exceção, é a constraint que não é
     * recriada e a suíte que continua verde. Um método testado contra um
     * information_schema real só é testado no banco que ele deveria estar
     * conferindo, e o teste passa quando o defeito está justamente ali.
     *
     * UPDATE_RULE e DELETE_RULE vão explícitos mesmo quando são RESTRICT, que é o
     * padrão: omitir o que é o default esconde a diferença entre uma constraint
     * replicada e uma que virou o default por acidente.
     *
     * O `REFERENCES` qualifica o WORKER, e não o modelo, e essa é a linha mais
     * importante do arquivo. Qualificar pelo modelo funciona — o MySQL aceita, e a
     * comparação continuaria batendo, porque ela confere a forma da constraint e
     * não o schema para onde ela aponta. Só que o resultado é um worker cujas
     * tabelas-filhas estão presas ao pai do MODELO, e aí nada do que esta classe
     * existe para entregar acontece de fato:
     *
     *  - a cópia do pai dentro do worker deixa de valer para a constraint, então um
     *    `INSERT` de filho com pai existente só no worker passa, e a suíte passa a
     *    poder gravar órfão que a produção rejeita;
     *  - todos os workers passam a segurar X-lock nas mesmas linhas do pai no
     *    modelo, que é exatamente a contenção entre processos que o banco por worker
     *    foi criado para eliminar — trocada por uma menor e mais difícil de ver,
     *    porque aparece só de vez em quando.
     *
     * O defeito era invisível para a comparação de paridade e para os testes de
     * SQL, e só apareceu ao ler `REFERENCED_TABLE_SCHEMA` no `information_schema`
     * do worker. Daí o teste que exige que o schema referenciado seja o do próprio
     * worker.
     *
     * @param  array{table: string, columns: list<string>, referenced_table: string, referenced_columns: list<string>, update_rule: string, delete_rule: string} $constraint
     */
    public static function addForeignKeyStatement(
        string $worker,
        string $name,
        array $constraint
    ): string {
        $columns = implode(', ', array_map(
            static fn (string $column): string => SchemaReader::identifier($column),
            $constraint['columns']
        ));

        $referenced = implode(', ', array_map(
            static fn (string $column): string => SchemaReader::identifier($column),
            $constraint['referenced_columns']
        ));

        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON UPDATE %s ON DELETE %s',
            SchemaReader::qualified($worker, $constraint['table']),
            SchemaReader::identifier($name),
            $columns,
            SchemaReader::qualified($worker, $constraint['referenced_table']),
            $referenced,
            self::foreignKeyRule($constraint['update_rule']),
            self::foreignKeyRule($constraint['delete_rule'])
        );
    }

    /**
     * As chaves estrangeiras do banco, como mapa nome => descrição.
     *
     * A descrição inclui a regra de UPDATE e a de DELETE porque `CASCADE` e
     * `SET NULL` mudam o que acontece com as linhas filhas quando a pai é
     * apagada, e uma suíte que roda contra um banco sem essa regra estar certa
     * não está mais testando o schema que a produção tem.
     *
     * @return array<string, string>
     */
    public static function describeForeignKeys(PDO $pdo, string $database): array
    {
        $constraints = self::foreignKeyConstraints($pdo, $database);

        ksort($constraints);

        $descriptions = [];

        foreach ($constraints as $name => $constraint) {
            $descriptions[$name] = sprintf(
                '%s(%s) -> %s(%s) ON UPDATE %s ON DELETE %s',
                $constraint['table'],
                implode(',', $constraint['columns']),
                $constraint['referenced_table'],
                implode(',', $constraint['referenced_columns']),
                $constraint['update_rule'],
                $constraint['delete_rule']
            );
        }

        return $descriptions;
    }

    /**
     * As regras de referência, que são palavras-chave e não identificadores.
     *
     * `UPDATE_RULE` e `DELETE_RULE` chegam do information_schema como `NO ACTION`
     * ou `CASCADE`, com espaço — não é um nome de objeto, e por isso não passa
     * por identifier(). A lista é fechada de propósito: ela transforma um valor
     * inesperado numa exceção que diz o que aconteceu, em vez de uma string que
     * entrava no SQL do clone sem ninguém ter conferido. `NO ACTION` precisa
     * estar na lista mesmo sendo o que o MySQL grava em vez de `RESTRICT`, que
     * é a diferença que faria a constraint recriada não ser a mesma.
     */
    private static function foreignKeyRule(string $rule): string
    {
        $allowed = ['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION'];

        if (! in_array($rule, $allowed, true)) {
            throw new RuntimeException(
                "Regra de referência '{$rule}' fora do conjunto que o MySQL usa "
                . '(' . implode(', ', $allowed) . '). Ela vem de information_schema.REFERENTIAL_CONSTRAINTS, '
                . 'e um valor inesperado aqui significa que a coluna mudou de sentido — o que '
                . 'aconteceria se alguém passasse a ler outra tabela no lugar dela.'
            );
        }

        return $rule;
    }
}
