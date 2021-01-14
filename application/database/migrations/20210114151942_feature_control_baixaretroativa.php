<?php

class Migration_feature_control_baixa extends CI_Migration
{
    public function up()
    {
        $sql = "INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (\'8\', \'control_baixa\', \'0\')";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 8");
    }
}
