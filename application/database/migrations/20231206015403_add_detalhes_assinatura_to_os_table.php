<?php

class Migration_add_detalhes_assinatura_to_os_table extends CI_Migration
{
    public function up()
    {
        $fields = [
            'assClienteImg' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'assClienteIp' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],
            'assClienteData' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'assTecnicoImg' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'assTecnicoIp' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true
            ],
            'assTecnicoData' => [
                'type' => 'DATETIME',
                'null' => true
            ],
        ];

        $this->dbforge->add_column('os', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('os', 'column_to_drop');
        $this->dbforge->drop_column('os', 'assClienteIp');
        $this->dbforge->drop_column('os', 'assClienteData');
        $this->dbforge->drop_column('os', 'assTecnicoImg');
        $this->dbforge->drop_column('os', 'assTecnicoIp');
        $this->dbforge->drop_column('os', 'assTecnicoData');
    }
}
