<?php

class Migration_controle_editar_os extends CI_Migration
{
    public function up()
    {
        $sql = "INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (9, 'control_editos', 1)";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 9");
    }
}
