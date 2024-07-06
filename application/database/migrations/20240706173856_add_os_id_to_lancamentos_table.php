<?php

class Migration_add_os_id_to_lancamentos_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('lancamentos', [
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ]
        ]);

        $this->db->query('ALTER TABLE  `os` ADD CONSTRAINT `fk_os_lancamentos1`
			FOREIGN KEY (`lancamento`)
			REFERENCES `lancamentos` (`idLancamentos`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
		');
    }

    public function down()
    {
        $this->dbforge->drop_column('lancamentos', 'os_id');
    }
}
