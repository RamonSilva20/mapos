<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_clients_table extends CI_Migration {

	public function __construct()
	{
		$this->load->dbforge();
		$this->load->database();
	}

	public function up() {
		
		// Drop table 'clients' if it exists
		$this->dbforge->drop_table('clients', TRUE);

		// Table structure for table 'clients'
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
			),
			'fantasy_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => TRUE,
			),
			'document' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
			),
			'phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '12',
			),
			'celphone' => array(
				'type' => 'VARCHAR',
				'constraint' => '12',
			)
			,'email' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'unique' => TRUE
			)
			,'gender' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => TRUE,
			),
			'company' => array(
				'type' => 'TINYINT',
				'constraint' => '1',
				'default' => 0,
			)
			,'created_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
				'default' => '0000-00-00 00:00:00',
			),
			'updated_at' => array(
				'type' => 'DATETIME',
				'null' => TRUE,
				'default' => '0000-00-00 00:00:00',
			)
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('clients');

	}

	public function down() {
		$this->dbforge->drop_table('clients', TRUE);
	}

}

/* End of file 002_create_clients_table.php */
/* Location: ./application/migrations/002_create_clients_table.php */