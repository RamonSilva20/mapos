<?php

class Migration_add_payment_gateway_to_cobrancas extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('cobrancas', [
            'payment_gateway' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'default' => null,
            ],
        ]);
        $this->db->query('UPDATE cobrancas SET payment_gateway="GerencianetSdk" WHERE payment_gateway IS NULL');
    }

    public function down()
    {
        $this->db->query('UPDATE cobrancas SET payment_gateway=NULL');
        $this->dbforge->drop_column('cobrancas', 'payment_gateway');
    }
}
