<?php

class Migration_add_clientefornecedor extends CI_Migration
{
    public function up()
    {
        $sql = "ALTER TABLE `clientes` ADD `fornecedor` BOOLEAN NOT NULL DEFAULT FALSE";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `clientes` DROP `fornecedor`;");
    }
}
