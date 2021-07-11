<?php

class Migration_asaas_payment_gateway extends CI_Migration
{
    public function up()
    {
        $sql = "
            ALTER TABLE clientes ADD asaas_id varchar(255) NULL;
            ALTER TABLE cobrancas MODIFY COLUMN charge_id VARCHAR(255) NOT NULL;
        ";
        $this->db->query($sql);
    }

    public function down()
    {
        $sql = "
            ALTER TABLE cobrancas MODIFY COLUMN charge_id INT(11) NOT NULL;
            ALTER TABLE clientes DROP COLUMN asaas_id;
        ";
        $this->db->query($sql);
    }
}
