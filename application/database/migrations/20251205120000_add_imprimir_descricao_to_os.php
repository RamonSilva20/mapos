<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_imprimir_descricao_to_os extends CI_Migration
{
    public function up()
    {
        // Verificar se o campo já existe
        $fields = $this->db->field_data('os');
        $field_exists = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'imprimir_descricao') {
                $field_exists = true;
                break;
            }
        }
        
        if (!$field_exists) {
            $this->dbforge->add_column('os', [
                'imprimir_descricao' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => false,
                    'after' => 'descricaoProduto',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('os', 'imprimir_descricao');
    }
}

