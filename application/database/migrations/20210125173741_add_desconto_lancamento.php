<?php

class Migration_add_desconto_lancamento extends CI_Migration
{
    public function up()
    {
        $sql = "ALTER TABLE `lancamentos` ADD `desconto` INT NULL;";
        $sql += "ALTER TABLE `lancamentos` ADD `valor_desconto` VARCHAR NULL;";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `lancamentos` DROP `desconto`;");
        $this->db->query("ALTER TABLE `lancamentos` DROP `valor_desconto`;");
    }
}
