<?php

/**
 * Separa o tema da v4 (app_theme) em modo e cor de destaque (#2833).
 *
 * A conversão preserva a escolha atual: quem usa "Dark violet" passa a ter
 * modo escuro com destaque violeta. app_theme não é apagado, porque as telas
 * legadas continuam lendo dele até a #2855.
 *
 * A cor de destaque foi removida depois (#2913, migration
 * 20261008120000_remove_tema_destaque_from_configuracoes). O mapa da v4 fica
 * copiado aqui, e não lido do tema_helper, para esta migration continuar
 * produzindo o mesmo resultado em quem ainda não a rodou: o helper passou a
 * tratar só o modo.
 */
class Migration_add_tema_modo_destaque_to_configuracoes extends CI_Migration
{
    private $table = 'configuracoes';

    private const TEMAS_DA_V4 = [
        'default' => ['claro', 'laranja'],
        'white' => ['claro', 'laranja'],
        'puredark' => ['escuro', 'azul'],
        'darkorange' => ['escuro', 'laranja'],
        'darkviolet' => ['escuro', 'violeta'],
        'whitegreen' => ['claro', 'verde'],
        'whiteblack' => ['claro', 'grafite'],
    ];

    public function up()
    {
        $linha = $this->db->select('valor')->where('config', 'app_theme')->get($this->table)->row();
        $chave = strtolower(trim((string) ($linha ? $linha->valor : '')));
        [$modo, $destaque] = self::TEMAS_DA_V4[$chave] ?? ['claro', 'laranja'];

        // Idempotente: quem já tem a configuração (por rodar a migration duas
        // vezes ou por tê-la criado à mão) não é sobrescrito.
        foreach (['app_tema_modo' => $modo, 'app_tema_destaque' => $destaque] as $config => $valor) {
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
