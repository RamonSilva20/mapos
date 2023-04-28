<?php

class Migration_alter_charset_configuracoes extends CI_Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `configuracoes` CHANGE `config` `config` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL;");
        $this->db->query("UPDATE `configuracoes` SET `valor` = 'Prezado(a), {CLIENTE_NOME} a OS de nº {NUMERO_OS} teve o status alterado para: {STATUS_OS} segue a descrição {DESCRI_PRODUTOS} com valor total de {VALOR_OS}! Para mais informações entre em contato conosco. Atenciosamente, {EMITENTE} {TELEFONE_EMITENTE}.' WHERE `configuracoes`.`idConfig` = 7");
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 7");
    }
}
