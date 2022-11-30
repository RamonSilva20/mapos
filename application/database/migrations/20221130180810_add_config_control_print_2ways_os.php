<?php

class Migration_add_config_control_print_2ways_os extends CI_Migration
{
    public function up()
    {
        $this->db->query("INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (15, 'control_2vias', 0);");
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 15");
    }
}
