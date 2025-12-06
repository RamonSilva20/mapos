<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_detalhes_to_servicos_os extends CI_Migration
{
    public function up()
    {
        // Verificar se o campo já existe
        $fields = $this->db->field_data('servicos_os');
        $field_exists = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'detalhes') {
                $field_exists = true;
                break;
            }
        }
        
        if (!$field_exists) {
            $this->dbforge->add_column('servicos_os', [
                'detalhes' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'preco',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('servicos_os', 'detalhes');
    }
}

