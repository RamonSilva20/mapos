<?php

/**
 * Desconto individual por item (#2880) em produtos_os, servicos_os e
 * itens_de_vendas.
 *
 * desconto guarda o número digitado (R$ ou %, conforme tipo_desconto) e
 * subTotal passa a ser o valor do item já com o desconto (preço x quantidade
 * menos o desconto), de modo que tudo o que soma subTotal continua certo.
 * Itens sem desconto ficam com desconto = 0 e não mudam.
 */
class Migration_add_desconto_to_itens extends CI_Migration
{
    private const TABELAS = ['produtos_os', 'servicos_os', 'itens_de_vendas'];

    public function up()
    {
        foreach (self::TABELAS as $tabela) {
            $this->dbforge->add_column($tabela, [
                'desconto' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => false, 'default' => 0],
                'tipo_desconto' => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => true],
            ]);

            // Com db_debug desligado (produção) um ALTER com erro só vai para o log.
            if (! $this->db->field_exists('desconto', $tabela) || ! $this->db->field_exists('tipo_desconto', $tabela)) {
                throw new RuntimeException("Não foi possível criar as colunas de desconto em {$tabela}; veja o log da aplicação.");
            }
        }
    }

    /**
     * Desfazer apaga os descontos por item: os subTotal já gravados continuam
     * com o desconto aplicado, então os totais não mudam.
     */
    public function down()
    {
        foreach (self::TABELAS as $tabela) {
            $this->dbforge->drop_column($tabela, 'desconto');
            $this->dbforge->drop_column($tabela, 'tipo_desconto');
        }
    }
}
