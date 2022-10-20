<?php

class Migration_add_desconto_lancamentos_os_vendas extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `lancamentos` CHANGE `valor` `valor` DECIMAL(10,2) NOT NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `lancamentos` ADD `desconto` DECIMAL(10, 2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `lancamentos` ADD `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `os` CHANGE `valorTotal` `valorTotal` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `os` ADD `desconto` DECIMAL(10, 2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `os` ADD `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `vendas` CHANGE `valorTotal` `valorTotal` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `vendas` CHANGE `desconto` `desconto` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `vendas` ADD `valor_desconto` DECIMAL(10, 2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `cobrancas` CHANGE `total` `total` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `produtos_os` CHANGE `preco` `preco` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `produtos_os` CHANGE `subTotal` `subTotal` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `servicos_os` CHANGE `preco` `preco` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `servicos_os` CHANGE `subTotal` `subTotal` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `itens_de_vendas` CHANGE `subTotal` `subTotal` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `itens_de_vendas` CHANGE `preco` `preco` DECIMAL(10,2) NULL DEFAULT 0");
        $this->db->query("INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (14, 'email_automatico', 1)");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `lancamentos` DROP `desconto`");
        $this->db->query("ALTER TABLE `lancamentos` DROP `valor_desconto`");
        $this->db->query("ALTER TABLE `os` DROP `desconto`");
        $this->db->query("ALTER TABLE `os` DROP `valor_desconto`");
        $this->db->query("ALTER TABLE `vendas` DROP `desconto`");
        $this->db->query("ALTER TABLE `vendas` DROP `valor_desconto`");
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 14");
    }
}
