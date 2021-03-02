<?php

class Migration_feature_notificawhats extends CI_Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `configuracoes` CHANGE `valor` `valor` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL');
        $sql = "INSERT INTO `configuracoes` (`idConfig`, `config`, `valor`) VALUES ('7', 'notifica_whats', 'Prezado(a), {CLIENTE_NOME} a OS de nº {NUMERO_OS} teve o status alterado para :{STATUS_OS} segue a descrição {DESCRI_PRODUTOS} com valor total de {VALOR_OS}!\\r\\nPara mais informações entre em contato conosco.\\r\\nAtenciosamente, {EMITENTE} {TELEFONE_EMITENTE}.')";
        $this->db->query($sql);
    }

    public function down()
    {
        $this->db->query("DELETE FROM `configuracoes` WHERE `configuracoes`.`idConfig` = 7");
        $this->db->query("ALTER TABLE `configuracoes` CHANGE `valor` `valor` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;");
    }
}
