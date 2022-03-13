<?php

class Migration_controle_editar_vendas extends CI_Migration
{
    public function up()
    {
        $this->db->query("INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES (10, 'control_edit_vendas', 1);");
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 10");
    }
}
