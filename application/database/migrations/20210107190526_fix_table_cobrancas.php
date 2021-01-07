<?php

class Migration_fix_table_cobrancas extends CI_Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `cobrancas` CHANGE `idCobranca` `idCobranca` INT(11) NOT NULL AUTO_INCREMENT');
    }

    public function down()
    {
        $this->dbforge->drop_table('cobrancas');
    }
}
