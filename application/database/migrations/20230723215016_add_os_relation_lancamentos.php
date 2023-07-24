<?php

class Migration_add_os_relation_lancamentos extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('lancamentos', [
            'os_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->dbforge->drop_column('lancamentos', 'os_id');
    }
}