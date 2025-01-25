<?php

class Migration_add_fields_to_clientes_table extends CI_Migration
{
    public function up()
    {
        //Adiciona campo nomeFantasia.
        $this->dbforge->add_column('clientes', array(
            'nomeFantasia' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'after' => 'nomeCliente'
            )
        ));

        //Adiciona campo rg_ie.
        $this->dbforge->add_column('clientes', array(
            'rg_ie' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'default' => null,
                'after' => 'documento'
            )
        ));

        //Adiciona campo obsCliente.
        $this->dbforge->add_column('clientes', array(
            'obsCliente' => array(
                'type' => 'TEXT',
                'null' => true,
                'default' => null,
                'after' => 'complemento'
            )
        ));

        //Adiciona campo situacao.
        $this->dbforge->add_column('clientes', array(
            'situacao' => array(
                'type' => 'BOOLEAN',
                'null' => false,
                'default' => 1,
                'after' => 'fornecedor'
            )
        ));

        //Adiciona campo dataNascimento.
        $this->dbforge->add_column('clientes', array(
            'dataNascimento' => array(
                'type' => 'DATE',
                'null' => true,
                'default' => null,
                'after' => 'situacao'
            )
        ));

        //Modifica o tamanho dos campos rua, numero, bairro e cidade.
        $fields = array(
            'rua' => array(
                'constraint' => 100
            ),
            'numero' => array(
                'constraint' => 6
            ),
            'bairro' => array(
                'constraint' => 100
            ),
            'cidade' => array(
                'constraint' => 100
            )
        );

        $this->dbforge->modify_column('clientes', $fields);
    }

    public function down()
    {
        //Remove o campo nomeFantasia.
        $this->dbforge->drop_column('clientes', 'nomeFantasia');
        //Remove o campo rg_ie.
        $this->dbforge->drop_column('clientes', 'rg_ie');
        //Remove o campo obsCliente.
        $this->dbforge->drop_column('clientes', 'obsCliente');
        //Remove o campo situacao.
        $this->dbforge->drop_column('clientes', 'situacao');
        //Remove o campo dataNascimento.
        $this->dbforge->drop_column('clientes', 'dataNascimento');

        //Reverte o tamanho dos campos rua, numero, bairro e cidade.
        $fields = array(
            'rua' => array(
                'constraint' => 70
            ),
            'numero' => array(
                'constraint' => 15
            ),
            'bairro' => array(
                'constraint' => 45
            ),
            'cidade' => array(
                'constraint' => 45
            )
        );
        $this->dbforge->modify_column('clientes', $fields);
    }
}
