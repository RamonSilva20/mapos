<?php

class Migration_add_desconto_lancamento extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `lancamentos` ADD `desconto` INT NULL DEFAULT 0;");
        $this->db->query("ALTER TABLE `lancamentos` ADD `valor_desconto` VARCHAR(55) NULL;");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `lancamentos` DROP `desconto`;");
        $this->db->query("ALTER TABLE `lancamentos` DROP `valor_desconto`;");
    }
}
