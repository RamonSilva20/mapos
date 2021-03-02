<?php

class Migration_add_cep_to_emitente_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('emitente', [
            'cep' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'default' => null,
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('emitente', 'cep');
    }
}
