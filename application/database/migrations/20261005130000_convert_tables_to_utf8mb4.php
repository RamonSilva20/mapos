<?php

/**
 * Converts every application table to utf8mb4 / utf8mb4_general_ci.
 *
 * No previous migration does this; the reason is in application/config/database.php:
 * until 20261005120000, 'char_set' was `utf8` and 'dbcollat' was `utf8_general_ci`.
 * mysql_forge::_create_table_attr() appends `DEFAULT CHARACTER SET = char_set COLLATE =
 * dbcollat` to every table dbforge creates, so the migration chain produced
 * utf8mb3_general_ci — in MySQL 8, `utf8` is an alias for utf8mb3, which cannot hold
 * the 4 bytes of an emoji. 20220307173741 creates `resets_de_senha` with raw SQL in
 * latin1, so that one arrives here as latin1_swedish_ci.
 *
 * banco.sql, by contrast, declares utf8mb4 in 25 of the 27 tables. Someone installing
 * via install/do_install.php got utf8mb4, someone installing via the migration chain
 * got utf8mb3, and the same code behaved differently depending on the path — exactly
 * what check-schema-parity.php exists to denounce, and what it did not see because it
 * compared only the column type.
 *
 * The tables come from information_schema instead of a hand-written list: a list ages,
 * and a new table nobody added would be left out without anyone noticing — the failure
 * mode commit 4862410 ("removed latin1 and put utf8mb4") had. Names coming out of there
 * only enter SQL after passing through identifier(), which wraps them in backticks and
 * doubles any backtick inside — nothing enters the SQL without that path.
 *
 * A `CONVERT TO CHARACTER SET` call rewrites the whole table: the time is O(size in
 * bytes), and the ALTER holds the table's MDL while it runs, so upgrading this
 * migration is a maintenance-window operation on installs with a large history.
 *
 * The type a `TEXT` column gets from CONVERT is NOT guaranteed: local MySQL 8.4.11
 * widens it to `MEDIUMTEXT` and the mysql:8.4 of CI does not — the same `ALTER`
 * converts on both. The parity gate compares banco.sql against the chain, and a result
 * that depends on the server is a gate that turns red on one machine and green on
 * another — which is exactly what happened with the 12 `TEXT` columns. The canonical
 * is the `MEDIUMTEXT` that banco.sql declares, so up() enforces it in its own pass:
 * after the CONVERT, every character column left as `TEXT` is widened to `MEDIUMTEXT`
 * on the same table bases. On a server that already widened, the pass finds no column
 * and only costs a read. down() does not narrow back, so up() and down() are not a
 * round trip in that regard.
 */
class Migration_convert_tables_to_utf8mb4 extends CI_Migration
{
    /**
     * The charset and collation the application now declares.
     *
     * The same values as config/database.php. They live here and are not read from
     * there because the migration must convert to where the application is GOING, not
     * where it IS: an install with a wrong DB_CHARSET in .env must still receive the
     * table in the charset that banco.sql describes.
     *
     * And here is the note for whoever upgrades: the converted table is not enough.
     * The DB_CHARSET in .env also has to become utf8mb4, otherwise the connection stays
     * utf8mb3 and the emoji is still truncated to `?` on the way INTO a query, even
     * with the table in utf8mb4 — the data the emoji would hold dies before reaching
     * the column. This migration converts what banco.sql declares; the .env is the
     * other end and is yours.
     */
    private const CHARSET = 'utf8mb4';

    private const COLLATION = 'utf8mb4_general_ci';

    /**
     * Converts the whole schema and fails loudly when an ALTER does not accept the
     * request.
     *
     * The return of each `query()` is checked on purpose: in production `db_debug` is
     * false, so a failed ALTER does not become a visible error — it becomes `false`
     * returned silently — and CI_Migration advances the version as it is, leaving the
     * database half-converted and the "database updated successfully!" message on the
     * screen. Throwing a RuntimeException here aborts the migration BEFORE the version
     * number is recorded, and that is what makes a failed conversion a conversion that
     * did not happen, not one that "happened" halfway.
     *
     * After the CONVERT comes the pass that widens to MEDIUMTEXT the columns the server
     * left in TEXT — see the file header, which explains why the type cannot depend on
     * the server. The same care about checking the return applies to that pass.
     *
     * `db_debug` is switched off around the queries for the usual reason: with it on,
     * CI3 calls display_error() and EXITS before the code can respond. It is switched
     * off to check the return and throw the exception the migration must, and back on
     * in the `finally`, so an aborted up() does not leave the connection changed for
     * whatever runs next.
     */
    public function up()
    {
        $previousDebug = $this->db->db_debug;
        $this->db->db_debug = false;

        try {
            foreach ($this->tables() as $table) {
                if ($this->db->query(
                    'ALTER TABLE ' . $this->identifier($table) . ' CONVERT TO CHARACTER SET '
                    . self::CHARSET . ' COLLATE ' . self::COLLATION
                ) === false) {
                    throw new RuntimeException(sprintf(
                        'Could not convert `%s` to utf8mb4. Original: %s.',
                        $table,
                        $this->db->error()['message']
                    ));
                }
            }

            $this->widenTextToMediumText();
        } finally {
            $this->db->db_debug = $previousDebug;
        }
    }

    /**
     * Goes back to utf8mb3, and refuses when that would destroy data.
     *
     * Going back is not reversible in the usual sense: utf8mb3 does not have the 4
     * bytes of the emoji, so a value that fits in utf8mb4 may not fit in utf8mb3. The
     * answer cannot depend on strict mode — config/database.php takes STRICT out of
     * the session sql_mode when `stricton` is false, and the behavior of `ALTER ...
     * CONVERT` in strict mode varies between server versions — so the data decides:
     * before converting each table, the method asks whether there is a value utf8mb3
     * cannot represent, and refuses naming the table and the column.
     *
     * The question counts BYTES, not characters: `LENGTH(column) <> LENGTH(CONVERT(column
     * USING utf8mb3))`. Still on utf8mb4, an emoji has 4 bytes; re-written as utf8mb3 it
     * becomes `?`, which has 1 byte, and the count differs. What utf8mb3 ALREADY
     * represents keeps its length, so it does not accuse; an already-stored `?` stays
     * `?` and does not accuse. Comparing length in bytes is deliberate: comparing the
     * re-written value with the original would need to match collations on both sides,
     * and the collation of the conversion expression is the server default, which
     * diverges from the column's `utf8mb4_general_ci` — on MySQL 8 the `<>` then dies
     * in "Illegal mix of collations" instead of answering.
     *
     * The question only looks at columns ALREADY in utf8mb3 or utf8mb4 (char, varchar
     * and the text types, plus enum and set). A column with another charset (latin1,
     * for example) is kept out of the accusation queue on purpose: it is convertible
     * to utf8mb3 without loss, but its byte count changes anyway (a latin1 `é` has 1
     * byte and becomes 2 in UTF-8), so the test would accuse something the ALTER would
     * not destroy. Same discovery mode as the tables. Binary columns are left out
     * because a BLOB column in utf8mb4 is already data that lives on the byte, and
     * CONVERT handles them through another door.
     *
     * The queue is swept in two passes. In the first, ALL tables are asked before any
     * ALTER, and the first accuser aborts right after: a down() that refuses cannot
     * have converted half the schema ahead of it. Only when the whole queue has passed
     * do the ALTERs run, and each return is checked — the same defect as up()
     * manifesting. The sweep is one query per table with the column predicates in OR,
     * and the column that accused is found by a second query on that table.
     *
     * Names coming from information_schema only enter the SQL through identifier(),
     * and each `query()` return is checked before any `num_rows()` — with `db_debug`
     * false it silently returns FALSE, and `FALSE->num_rows()` is not the error this
     * migration promises.
     *
     * `db_debug` is switched off around the queries for the same reason as in up(),
     * on again in the `finally` for the same reason too: an aborted down() must not
     * leave the connection changed for whatever runs next.
     *
     * The exception translates what the driver reports, because the original message
     * talks about an ALTER and the person reading is looking at a migration.
     */
    public function down()
    {
        $previousDebug = $this->db->db_debug;
        $this->db->db_debug = false;

        try {
            foreach ($this->characterColumns() as $table => $columns) {
                $offendingColumn = $this->firstFourByteColumn($table, $columns);

                if ($offendingColumn !== null) {
                    throw new RuntimeException(sprintf(
                        'Cannot revert `%s` to utf8mb3: `%s` holds a 4-byte character '
                        . 'that utf8mb3 cannot represent, and converting would erase that '
                        . 'data. Nothing was converted — the whole queue was examined before '
                        . 'the first ALTER. Revert by hand, or do not revert.',
                        $table,
                        $offendingColumn
                    ));
                }
            }

            foreach ($this->tables() as $table) {
                if ($this->db->query(
                    'ALTER TABLE ' . $this->identifier($table) . ' CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci'
                ) === false) {
                    throw new RuntimeException(sprintf(
                        'Could not revert `%s` to utf8mb3. Original: %s.',
                        $table,
                        $this->db->error()['message']
                    ));
                }
            }
        } finally {
            $this->db->db_debug = $previousDebug;
        }
    }

    /**
     * The first column of the table with a character utf8mb3 cannot represent.
     *
     * The test is byte length, per column:
     * `LENGTH(column) <> LENGTH(CONVERT(column USING utf8mb3))`. Going back to utf8mb3
     * swaps every character it does not have for `?`, and swapping 4 bytes for 1
     * changes the length. The first query asks for the first row accusing on any
     * column (predicates in OR) and stops; the second identifies which column accused,
     * for the failed table only.
     *
     * @param  list<string>  $columns
     * @return string|null the column name, or null when the table is clean
     */
    private function firstFourByteColumn(string $table, array $columns): ?string
    {
        $quotedTable = $this->identifier($table);

        $predicates = [];

        foreach ($columns as $column) {
            $quotedColumn = $this->identifier($column);
            $predicates[] = 'LENGTH(' . $quotedColumn . ') <> LENGTH(CONVERT(' . $quotedColumn . ' USING utf8mb3))';
        }

        $result = $this->db->query(
            'SELECT 1 FROM ' . $quotedTable . ' WHERE ' . implode(' OR ', $predicates) . ' LIMIT 1'
        );

        if ($result === false) {
            throw new RuntimeException('Could not scan `' . $table . '`. Original: ' . $this->db->error()['message']);
        }

        if ($result->num_rows() === 0) {
            return null;
        }

        foreach ($columns as $column) {
            $quotedColumn = $this->identifier($column);

            $result = $this->db->query(
                'SELECT 1 FROM ' . $quotedTable . ' WHERE LENGTH(' . $quotedColumn . ') <> '
                . 'LENGTH(CONVERT(' . $quotedColumn . ' USING utf8mb3)) LIMIT 1'
            );

            if ($result === false) {
                throw new RuntimeException('Could not scan `' . $table . '`. Original: ' . $this->db->error()['message']);
            }

            if ($result->num_rows() > 0) {
                return $column;
            }
        }

        return null;
    }

    /**
     * The character columns of the database, grouped by table.
     *
     * A single query, and the two filters that matter come out of it: the type (char,
     * varchar, the text types, enum and set) and the charset (only utf8mb3 and utf8mb4,
     * because the byte test lies about the rest — see the down() docblock). The order
     * by table and ordinal position keeps the grouping stable for the refusal message.
     *
     * @return array<string, list<string>> table name => character column names
     */
    private function characterColumns(): array
    {
        $result = $this->db->query(
            'SELECT table_name, column_name FROM information_schema.columns
             WHERE table_schema = ' . $this->db->escape($this->db->database) . "
             AND data_type IN ('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum', 'set')
             AND character_set_name IN ('utf8mb3', 'utf8mb4')
             ORDER BY table_name, ordinal_position"
        );

        if ($result === false) {
            throw new RuntimeException('Could not read the character columns. Original: ' . $this->db->error()['message']);
        }

        $grouped = [];

        foreach ($result->result_array() as $row) {
            $table = strval($row['TABLE_NAME']);
            $grouped[$table][] = strval($row['COLUMN_NAME']);
        }

        return $grouped;
    }

    /**
     * The base tables of the database the application is connected to.
     *
     * @return list<string>
     */
    private function tables(): array
    {
        $result = $this->db->query(
            'SELECT table_name FROM information_schema.tables
             WHERE table_schema = ' . $this->db->escape($this->db->database) . "
             AND table_type = 'BASE TABLE' ORDER BY table_name"
        );

        if ($result === false) {
            throw new RuntimeException('Could not read the tables. Original: ' . $this->db->error()['message']);
        }

        return array_map('strval', $result->result_array() === []
            ? []
            : array_column($result->result_array(), 'TABLE_NAME'));
    }

    /**
     * Widens to MEDIUMTEXT every column the CONVERT left as TEXT.
     *
     * See the file header: whether to widen is the server's decision, and a result
     * that depends on the server breaks the parity gate. banco.sql declares MEDIUMTEXT,
     * so that is where the chain must land on any server.
     *
     * The column is modified preserving what it already was — nullability and default —
     * read from information_schema itself: widening without preserving would flip the
     * NOT NULL of one column or the default of another, and the chain schema would
     * diverge from banco.sql again. A column whose `extra` is not empty (a generated
     * column, for example) does not enter the pass: widening by hand a construct the
     * server did not widen alone risks more than the pass solves. None exists today,
     * and the refusal fails loudly rather than leaving the column out silently.
     *
     * One ALTER per table, with the table's MODIFYs together: each ALTER rewrites the
     * whole table, and the cost is that of the tables the server did not widen — on an
     * install whose server widened, the pass finds no column and only costs a read.
     */
    private function widenTextToMediumText()
    {
        foreach ($this->textColumns() as $table => $columns) {
            $modifies = [];

            foreach ($columns as $column) {
                if ($column['extra'] !== '') {
                    throw new RuntimeException(sprintf(
                        'Column `%s`.`%s` has the extra `%s` and is TEXT after the CONVERT. '
                        . 'Widening it by hand risks what the server did not widen alone. '
                        . 'Nothing was widened.',
                        $table,
                        $column['name'],
                        $column['extra']
                    ));
                }

                $modifies[] = 'MODIFY ' . $this->identifier($column['name'])
                    . ' MEDIUMTEXT COLLATE ' . self::COLLATION
                    . ($column['nullable'] ? '' : ' NOT NULL')
                    . $this->modifyDefault($column['default'], $column['nullable']);
            }

            if ($this->db->query(
                'ALTER TABLE ' . $this->identifier($table) . ' ' . implode(', ', $modifies)
            ) === false) {
                throw new RuntimeException(sprintf(
                    'Could not widen the columns of `%s` to MEDIUMTEXT. Original: %s.',
                    $table,
                    $this->db->error()['message']
                ));
            }
        }
    }

    /**
     * The `TEXT` columns of the database the widening pass must modify.
     *
     * @return array<string, list<array{name: string, nullable: bool, default: ?string, extra: string}>> table => columns
     */
    private function textColumns(): array
    {
        $result = $this->db->query(
            'SELECT table_name, column_name, is_nullable, column_default, extra FROM information_schema.columns
             WHERE table_schema = ' . $this->db->escape($this->db->database) . "
             AND data_type = 'text'
             ORDER BY table_name, ordinal_position"
        );

        if ($result === false) {
            throw new RuntimeException('Could not read the TEXT columns. Original: ' . $this->db->error()['message']);
        }

        $grouped = [];

        foreach ($result->result_array() as $row) {
            $grouped[strval($row['TABLE_NAME'])][] = [
                'name' => strval($row['COLUMN_NAME']),
                'nullable' => (string) $row['IS_NULLABLE'] === 'YES',
                'default' => $row['COLUMN_DEFAULT'] === null ? null : strval($row['COLUMN_DEFAULT']),
                'extra' => strval($row['EXTRA']),
            ];
        }

        return $grouped;
    }

    /**
     * The DEFAULT clause of a MODIFY, verbatim from what the column already declares.
     *
     * information_schema returns the default as a literal, and its NULL is SQL NULL. A
     * column without a default gets no clause: a `DEFAULT NULL` on a NOT NULL column —
     * or a default the column never had — would change the schema the chain promised.
     */
    private function modifyDefault(?string $default, bool $nullable): string
    {
        if ($default === null || ($nullable && $default === 'NULL')) {
            return '';
        }

        return ' DEFAULT ' . $this->db->escape($default);
    }

    /**
     * An identifier ready to enter the SQL.
     *
     * Wraps in backticks and doubles any backtick within the name. The name comes from
     * information_schema, not from the user, but this function keeps the header's
     * promise: nothing enters the SQL as an identifier without passing through here.
     */
    private function identifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }
}
