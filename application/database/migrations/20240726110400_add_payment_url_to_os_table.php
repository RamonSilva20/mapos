<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_payment_url_to_os_table extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_column('os', array(
            'payment_url' => array(
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
            ),
        ));

    }

    public function down()
    {
        $this->dbforge->drop_column('os', 'payment_url');

    }
}
