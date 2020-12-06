<?php

class Migration_add_desconto_to_os_table extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('os', [
            'desconto' => [
                'type' => 'DECIMAL',
                'null' => false,
                'default' => 0,
                'constraint' => '10,2',
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('os', 'desconto');
    }
}
