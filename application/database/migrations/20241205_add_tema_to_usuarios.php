<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_tema_to_usuarios extends CI_Migration {

    public function up()
    {
        // Verifica se a coluna já existe antes de criar
        if (!$this->db->field_exists('tema', 'usuarios')) {
            $this->dbforge->add_column('usuarios', [
                'tema' => [
                    'type' => 'VARCHAR',
                    'constraint' => '50',
                    'default' => 'white',
                    'null' => false,
                ],
            ]);
            echo "Coluna 'tema' adicionada à tabela 'usuarios'.\n";
        } else {
            echo "A coluna 'tema' já existe na tabela 'usuarios'.\n";
        }
    }

    public function down()
    {
        // Remove a coluna ao reverter
        if ($this->db->field_exists('tema', 'usuarios')) {
            $this->dbforge->drop_column('usuarios', 'tema');
            echo "Coluna 'tema' removida da tabela 'usuarios'.\n";
        } else {
            echo "A coluna 'tema' não existe na tabela 'usuarios'.\n";
        }
    }
}
