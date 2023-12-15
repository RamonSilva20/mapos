<?php

class Migration_add_usar_assinatura_to_configuracoes_table extends CI_Migration
{
    public function up()
    {
        $sql = "INSERT INTO `configuracoes` (`config`, `valor`) VALUES ('usar_assinatura', 1), ('status_assinatura', 'Aprovado')";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`config` = 'usar_assinatura'");
    }
}
