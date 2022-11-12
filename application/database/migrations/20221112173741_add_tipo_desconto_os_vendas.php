<?php

class Migration_add_tipo_desconto_os_vendas extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `os` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `vendas` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `lancamentos` ADD `tipo_desconto` VARCHAR(8) NULL DEFAULT NULL");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `os` DROP `tipo_desconto`");
        $this->db->query("ALTER TABLE `vendas` DROP `tipo_desconto`");
        $this->db->query("ALTER TABLE `lancamentos` DROP `tipo_desconto`");
    }
}
