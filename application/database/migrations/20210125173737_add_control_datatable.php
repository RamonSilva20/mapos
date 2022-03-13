<?php

class Migration_add_control_datatable extends CI_Migration
{
    public function up()
    {
        $sql = "INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (11, 'control_datatable', 1)";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 11");
    }
}
