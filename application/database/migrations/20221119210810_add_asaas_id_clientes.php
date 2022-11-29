<?php

class Migration_add_asaas_id_clientes extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `clientes` ADD `asaas_id` VARCHAR(255) NULL DEFAULT NULL");
        $this->db->query("ALTER TABLE `usuarios` DROP `asaas_id`");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `clientes` DROP `asaas_id`");
        $this->db->query("ALTER TABLE `usuarios` DROP `asaas_id`");
    }
}
