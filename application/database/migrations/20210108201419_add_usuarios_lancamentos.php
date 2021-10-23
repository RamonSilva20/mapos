<?php

class Migration_add_usuarios_lancamentos extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('lancamentos', [
            'usuarios_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
        $this->db->query('ALTER TABLE `lancamentos` ADD INDEX `fk_lancamentos_usuarios1` (`usuarios_id` ASC)');
        $this->db->query('ALTER TABLE `lancamentos` ADD CONSTRAINT `fk_lancamentos_usuarios1`
			FOREIGN KEY (`usuarios_id`)
			REFERENCES `usuarios` (`idUsuarios`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION
        ');
    }

    public function down()
    {
        $this->dbforge->drop_column('lancamentos', 'usuarios_id');
    }
}
