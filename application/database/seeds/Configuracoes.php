<?php

class Configuracoes extends Seeder
{
    private $table = 'configuracoes';

    public function run()
    {
        echo 'Running Configuracoes Seeder';

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
            [
                'idConfig' => 12,
                'config' => 'os_status_list',
                'valor' => '[\"Aberto\",\"Faturado\",\"Negocia\\u00e7\\u00e3o\",\"Em Andamento\",\"Or\\u00e7amento\",\"Finalizado\",\"Cancelado\",\"Aguardando Pe\\u00e7as\"]',
            ],
            [
                'idConfig' => 13,
                'config' => 'control_edit_vendas',
                'valor' => '1',
            ],
            [
                'idConfig' => 15,
                'config' => 'control_2vias',
                'valor' => '0',
            ],
        ];

        // Sete destas chaves já vêm das migrations, cada uma inserida com o
        // mesmo idConfig que a seed usaria: control_baixa, control_editos,
        // control_datatable, pix_key, os_status_list, control_edit_vendas e
        // control_2vias. `configuracoes.config` tem UNIQUE (`unique_valor`), então
        // um insert cego aborta com 1062 duplicate entry, e aborta logo na
        // primeira linha — o que tornava o Tools::seed() impossível de
        // reexecutar em qualquer instalação, inclusive numa freshly migrada,
        // onde a migration já deixou a linha no lugar.
        $existentes = array_column(
            $this->db->select('config')->get($this->table)->result_array(),
            'config'
        );

        foreach ($configs as $config) {
            if (in_array($config['config'], $existentes, true)) {
                continue;
            }

            $this->db->insert($this->table, $config);
        }

        echo PHP_EOL;
    }
}
