<?php

class Migration_add_cep_to_usuarios_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('usuarios', [
            'cep' => [
                'type' => 'VARCHAR',
                'constraint' => 9,
                'null' => false,
                'default' => '70005-115',
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('usuarios', 'cep');
    }
}
