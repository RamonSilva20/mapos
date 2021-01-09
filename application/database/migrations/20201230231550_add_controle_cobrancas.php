<?php

class Migration_add_controle_cobrancas extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'idCobranca' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'auto_increment' => true
            ],
            'charge_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
            ],
            'conditional_discount_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'custom_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'expire_at' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'message' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'payment_url' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'request_delivery_address' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 36,
                'null' => true,
            ],
            'total' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
            ],
            'barcode' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'link' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'payment' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'pdf' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'vendas_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->dbforge->add_key("idCobranca", true);
        $this->dbforge->create_table("cobrancas", true);
        $this->db->query('ALTER TABLE  `cobrancas` ADD INDEX `fk_cobrancas_os1` (`os_id` ASC)');
        $this->db->query('ALTER TABLE  `cobrancas` ADD CONSTRAINT `fk_cobrancas_os1`
			FOREIGN KEY (`os_id`)
			REFERENCES `os` (`idOs`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
        ');
        $this->db->query('ALTER TABLE  `cobrancas` ADD INDEX `fk_cobrancas_vendas1` (`vendas_id` ASC)');
        $this->db->query('ALTER TABLE  `cobrancas` ADD CONSTRAINT `fk_cobrancas_vendas1`
			FOREIGN KEY (`vendas_id`)
			REFERENCES `vendas` (`idVendas`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
        $this->db->query('ALTER TABLE `cobrancas` ENGINE = InnoDB');
    }

    public function down()
    {
        $this->dbforge->drop_table('cobrancas');
    }
}
