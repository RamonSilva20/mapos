<?php

/**
 * Separa o tema da v4 (app_theme) em modo e cor de destaque (#2833).
 *
 * A conversão preserva a escolha atual: quem usa "Dark violet" passa a ter
 * modo escuro com destaque violeta. app_theme não é apagado, porque as telas
 * legadas continuam lendo dele até a #2855.
 */
class Migration_add_tema_modo_destaque_to_configuracoes extends CI_Migration
{
    private $table = 'configuracoes';

    public function up()
    {
        if (! function_exists('temaDeAppTheme')) {
            require_once APPPATH . 'helpers' . DIRECTORY_SEPARATOR . 'tema_helper.php';
        }

        $linha = $this->db->select('valor')->where('config', 'app_theme')->get($this->table)->row();
        $tema = temaDeAppTheme($linha ? $linha->valor : null);

        // Idempotente: quem já tem a configuração (por rodar a migration duas
        // vezes ou por tê-la criado à mão) não é sobrescrito.
        foreach (['app_tema_modo' => $tema['modo'], 'app_tema_destaque' => $tema['destaque']] as $config => $valor) {
            $existe = $this->db->where('config', $config)->count_all_results($this->table) > 0;

            if (! $existe) {
                $this->db->insert($this->table, ['config' => $config, 'valor' => $valor]);
            }
        }
    }

    public function down()
    {
        $this->db->where_in('config', ['app_tema_modo', 'app_tema_destaque'])->delete($this->table);
    }
}
