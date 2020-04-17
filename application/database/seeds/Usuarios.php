<?php

class Usuarios extends Seeder
{
    private $table = 'usuarios';

    public function run()
    {
        echo "Running Usuarios Seeder";

        //seed records manually
        $data = [
            'idUsuarios' => 1,
            'nome' => 'Admin',
            'rg' => 'MG-25.502.560',
            'cpf' => '517.565.356-39',
            'cep' => '01024-900',
            'rua' => 'R. Cantareira',
            'numero' => '306',
            'bairro' => 'Centro Histórico de São Paulo',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'email' => 'admin@admin.com',
            'senha' => '$2y$10$lAW0AXb0JLZxR0yDdfcBcu3BN9c2AXKKjKTdug7Or0pr6cSGtgyGO', // 123456
            'telefone' => '0000-0000',
            'celular' => '',
            'situacao' => 1,
            'dataCadastro' => '2018-09-09',
            'permissoes_id' => 1,
            'dataExpiracao' => '2030-01-01',
        ];
        $this->db->insert($this->table, $data);

        echo PHP_EOL;
    }
}
