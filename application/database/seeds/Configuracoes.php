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
            [
                'idConfig' => 7,
                'config' => 'notifica_whats',
                'valor' => 'Prezado(a), {CLIENTE_NOME} a OS de nº {NUMERO_OS} teve o status alterado para :{STATUS_OS} segue a descrição {DESCRI_PRODUTOS} com valor total de {VALOR_OS}!
                Para mais informações entre em contato conosco.
                Atenciosamente, {EMITENTE} {TELEFONE_EMITENTE}.',
            ],
            [
                'idConfig' => 8,
                'config' => 'control_baixa',
                'valor' => '0',
            ],
            [
                'idConfig' => 9,
                'config' => 'control_editos',
                'valor' => '1',
            ],
            [
                'idConfig' => 10,
                'config' => 'control_datatable',
                'valor' => '1',
            ],
            [
                'idConfig' => 11,
                'config' => 'pix_key',
                'valor' => '',
            ],
        ];

        foreach ($configs as $config) {
            $this->db->insert($this->table, $config);
        }

        echo PHP_EOL;
    }
}
