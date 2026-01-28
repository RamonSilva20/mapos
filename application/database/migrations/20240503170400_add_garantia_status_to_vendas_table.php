<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_garantia_status_to_vendas_table extends CI_Migration
{

    public function up()
    {
        // Adiciona o campo garantia
        $this->dbforge->add_column('vendas', [
            'garantia' => [
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
            ],
        ]);

        // Adiciona o campo status
        $this->dbforge->add_column('vendas', [
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        // Remove o campo garantia
        $this->dbforge->drop_column('vendas', 'garantia');

        // Remove o campo status
        $this->dbforge->drop_column('vendas', 'status');
    }
}
