<?php

class Migration_add_aprovado_to_status_list extends CI_Migration
{
    public function up()
    {
        $configurationSql = "
            SELECT valor
            FROM configuracoes
            WHERE idConfig = 12
            LIMIT 1
        ";
        $result = $this->db->query($configurationSql)->row();

        $osStatus = json_decode($result->valor, true);
        if (empty($osStatus)) {
            $osStatus = ["Aberto","Faturado","Negociação","Em Andamento","Orçamento","Finalizado","Cancelado","Aguardando Peças", "Aprovado"];
        } else {
            $osStatus[] = 'Aprovado';
        }

        $sql = "UPDATE `configuracoes` SET valor = ? WHERE idConfig = 12";
        $this->db->query($sql, [json_encode($osStatus)]);
    }

    public function down()
    {
        $this->db->query("UPDATE `configuracoes` SET valor = '[\"Aberto\",\"Faturado\",\"Negocia\\u00e7\\u00e3o\",\"Em Andamento\",\"Or\\u00e7amento\",\"Finalizado\",\"Cancelado\",\"Aguardando Pe\\u00e7as\"]' WHERE idConfig = 12");
    }
}
