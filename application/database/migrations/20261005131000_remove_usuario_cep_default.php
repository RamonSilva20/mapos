<?php

/**
 * Tira o CEP de Brasília que a 20200306012421 deixou como DEFAULT de usuarios.cep.
 *
 * A 20200306012421 criou a coluna com `'default' => '70005-115'`. Esse é o CEP do
 * desenvolvedor que escreveu a migration, e ele ficou no schema: toda instalação nova
 * recebia `usuarios.cep DEFAULT '70005-115'`, e qualquer INSERT que omitisse a coluna
 * gravava o CEP de outra cidade. banco.sql nunca teve esse default, então o gate de
 * paridade comparava `'70005-115'` contra nada e não via a divergência.
 *
 * A coluna é NOT NULL, então DROP DEFAULT a deixaria sem valor para quem omita a coluna —
 * e em modo estrito isso é erro 1364. O DEFAULT '' é o que mantém a coluna usável sem
 * inventar um CEP: '' não é um CEP válido, e o que valida CEP é a aplicação.
 */
class Migration_remove_usuario_cep_default extends CI_Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE `usuarios` CHANGE `cep` `cep` VARCHAR(9) NOT NULL DEFAULT ''"
        );
    }

    public function down()
    {
        $this->db->query(
            "ALTER TABLE `usuarios` CHANGE `cep` `cep` VARCHAR(9) NOT NULL DEFAULT '70005-115'"
        );
    }
}
