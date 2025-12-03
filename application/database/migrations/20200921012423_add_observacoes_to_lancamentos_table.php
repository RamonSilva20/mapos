<?php

class Migration_add_observacoes_to_lancamentos_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('lancamentos', [
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('lancamentos', 'observacoes');
    }
}
