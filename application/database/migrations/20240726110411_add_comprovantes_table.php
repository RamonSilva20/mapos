<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_comprovantes_table extends CI_Migration {

    public function up()
    {
        // Criar a tabela 'comprovantes'
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'os_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
            ),
            'url_comprovante' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => TRUE,
            ),
            'verified' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('comprovantes');

        $this->dbforge->add_column('os', array(
            'payment_url' => array(
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => TRUE,
            ),
        ));
    }

    public function down()
    {
        $this->dbforge->drop_table('comprovantes');

        $this->dbforge->drop_column('os', 'payment_url');
    }
}
