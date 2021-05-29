<?php

class Migration_add_desconto_os_venda extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `os` ADD `desconto` INT NULL DEFAULT 0;");
        $this->db->query("ALTER TABLE `os` ADD `valor_desconto` VARCHAR(55) NULL;");
        $this->db->query("ALTER TABLE `vendas` ADD `desconto` INT NULL DEFAULT 0;");
        $this->db->query("ALTER TABLE `vendas` ADD `valor_desconto` VARCHAR(55) NULL;");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `os` DROP `desconto`;");
        $this->db->query("ALTER TABLE `os` DROP `valor_desconto`;");
        $this->db->query("ALTER TABLE `vendas` DROP `desconto`;");
        $this->db->query("ALTER TABLE `vendas` DROP `valor_desconto`;");
    }
}
