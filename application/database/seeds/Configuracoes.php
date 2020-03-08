<?php

class Configuracoes extends Seeder
{
    private $table = 'configuracoes';

    public function run()
    {
        echo "Running Configuracoes Seeder";

        $configs = [
            [
                'idConfig' => 2,
                'config' => 'app_name',
                'valor' => 'Map-OS',
            ],
            [
                'idConfig' => 3,
                'config' => 'app_theme',
                'valor' => 'white',
            ],
            [
                'idConfig' => 4,
                'config' => 'per_page',
                'valor' => 10,
            ],
            [
                'idConfig' => 5,
                'config' => 'os_notification',
                'valor' => 'cliente',
            ],
            [
                'idConfig' => 6,
                'config' => 'control_estoque',
                'valor' => '1',
            ],
        ];

        foreach ($configs as $config) {
            $this->db->insert($this->table, $config);
        }

        echo PHP_EOL;
    }
}
