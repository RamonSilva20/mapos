<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Identificador do cliente no gateway Asaas.
 *
 * O arquivo original também tentava dropar `usuarios`.`asaas_id`, mas nenhuma
 * migration do projeto chegou a criar essa coluna, então o DROP falhava com
 * erro 1091 (Can't DROP; check that column/key exists) e a migration era
 * marcada como falha. O DROP saiu de up() e down().
 */
class Migration_add_asaas_id_clientes extends CI_Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `clientes` ADD `asaas_id` VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `clientes` DROP `asaas_id`');
    }
}
