<?php

class Migration_add_tempo_servicos extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `servicos` ADD `minutosEstimados` INT NOT NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `servicos_os` ADD `minutosGastos` INT NOT NULL DEFAULT 0");
        $this->db->query("ALTER TABLE `servicos_os` ADD `iniciadoEm` VARCHAR(64)");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `servicos` DROP IF EXISTS `minutosEstimados`");
        $this->db->query("ALTER TABLE `servicos_os` DROP IF EXISTS `minutosGastos`");
        $this->db->query("ALTER TABLE `servicos_os` DROP IF EXISTS `iniciadoEm`");
    }
}
