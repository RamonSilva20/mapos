<?php

/**
 * Preços e saldo com duas casas decimais (#2938).
 *
 * A migration base declarava estas colunas com 'constraint' => 10, 2 (dois
 * elementos do array, não "10,2"), e elas eram criadas como DECIMAL(10), sem
 * centavos, em quem montou o banco pelas migrations. O banco.sql já tem
 * DECIMAL(10,2): ali o MODIFY não muda nada.
 *
 * Só alarga a coluna; o down() não volta para DECIMAL(10), que arredondaria
 * os valores.
 */
class Migration_fix_decimal_scale_of_prices extends CI_Migration
{
    /** tabela => [coluna => aceita null] */
    private const COLUNAS = [
        'contas' => ['saldo' => true],
        'produtos' => ['precoCompra' => true, 'precoVenda' => false],
        'servicos' => ['preco' => false],
    ];

    public function up()
    {
        foreach (self::COLUNAS as $tabela => $colunas) {
            if (! $this->db->table_exists($tabela)) {
                continue;
            }

            foreach ($colunas as $coluna => $nulo) {
                if (! $this->db->field_exists($coluna, $tabela)) {
                    continue;
                }

                $this->db->query(sprintf(
                    'ALTER TABLE %s MODIFY %s DECIMAL(10,2) %s',
                    $this->db->protect_identifiers($tabela),
                    $this->db->protect_identifiers($coluna),
                    $nulo ? 'NULL DEFAULT NULL' : 'NOT NULL'
                ));
            }
        }
    }

    public function down()
    {
    }
}
