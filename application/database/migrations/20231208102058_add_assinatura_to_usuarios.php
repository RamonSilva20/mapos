<?php

class Migration_add_assinatura_to_usuarios extends CI_Migration
{
    public function up()
    {
        $fields = [
            'assinaturaImg' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ]
        ];

        $this->dbforge->add_column('usuarios', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('assinaturaImg');
    }
}
