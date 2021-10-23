<?php

class Migration_add_cobrancas_cliente extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('cobrancas', [
            'clientes_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->db->query('ALTER TABLE  `cobrancas` ADD INDEX `fk_cobrancas_clientes1` (`clientes_id` ASC)');
        $this->db->query('ALTER TABLE  `cobrancas` ADD CONSTRAINT `fk_cobrancas_clientes1`
			FOREIGN KEY (`clientes_id`)
			REFERENCES `clientes` (`idClientes`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
        ');
    }

    public function down()
    {
        $this->dbforge->drop_table('cobrancas');
    }
}
