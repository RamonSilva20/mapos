<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Repara a precisão das colunas DECIMAL criadas pela migration create_base.
 *
 * O create_base declarava quatro colunas assim:
 *
 *     'preco' => [
 *         'type' => 'DECIMAL',
 *         'constraint' => 10, 2,
 *     ],
 *
 * O PHP não aplica o operador vírgula ali dentro de um array: ele lê
 * 'constraint' => 10 e descarta o 2 como elemento posicional. O dbforge só usa
 * o CONSTRAINT, então a coluna saía como DECIMAL(10) — que o MySQL interpreta
 * como DECIMAL(10,0), sem nenhuma casa decimal.
 *
 * O create_base foi corrigido no lugar, o que só ajuda quem ainda não rodou
 * essa migration. Quem rodou entre 2012 e agora tem as quatro colunas com zero
 * casas decimais, e nenhuma migration posterior as repara: a
 * 20220320173741 toca lancamentos, os, vendas, cobrancas, produtos_os,
 * servicos_os e itens_de_vendas, mas não contas, produtos e servicos.
 *
 * O estrago real:
 *
 *   - produtos.precoVenda e produtos.precoCompra alimentam
 *     Relatorios_model::produtosCustom(), que faz
 *     SUM(produtos.estoque * produtos.precoVenda) e filtra por
 *     precoVenda BETWEEN. Ou seja, a avaliação de estoque em dinheiro era
 *     arredondada para reais inteiros;
 *   - servicos.preco e contas.saldo tiveram o mesmo destino.
 *
 * Esta migration só amplia a precisão. Os centavos que o DECIMAL(10,0) já
 * descartou na escrita não voltam — o dado foi perdido na hora em que foi
 * gravado, e nenhuma alteração de schema consegue recuperá-lo.
 */
class Migration_repair_decimal_precision extends CI_Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `contas` MODIFY COLUMN `saldo` DECIMAL(10,2) NULL');
        $this->db->query('ALTER TABLE `produtos` MODIFY COLUMN `precoCompra` DECIMAL(10,2) NULL DEFAULT NULL');
        $this->db->query('ALTER TABLE `produtos` MODIFY COLUMN `precoVenda` DECIMAL(10,2) NOT NULL');
        $this->db->query('ALTER TABLE `servicos` MODIFY COLUMN `preco` DECIMAL(10,2) NOT NULL');
    }

    public function down()
    {
        // Reverter aqui reintroduz o arredondamento e perde centavos que ainda
        // existiam. Deixar como está.
    }
}
