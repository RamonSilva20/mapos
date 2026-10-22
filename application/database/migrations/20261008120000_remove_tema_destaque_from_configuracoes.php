<?php

/**
 * Remove a configuração de cor de destaque (#2913).
 *
 * O DESIGN.md define uma cor de ação única, o laranja #F37338; o tema da v5
 * passa a ser só o modo (app_tema_modo). app_theme continua, para as telas
 * legadas, até a #2855.
 *
 * O down() recria app_tema_destaque a partir de app_theme, com o mesmo mapa
 * da migration 20261008000000, sem sobrescrever um valor que já exista.
 */
class Migration_remove_tema_destaque_from_configuracoes extends CI_Migration
{
    private $table = 'configuracoes';

    private const DESTAQUE_DOS_TEMAS_DA_V4 = [
        'default' => 'laranja',
        'white' => 'laranja',
        'puredark' => 'azul',
        'darkorange' => 'laranja',
        'darkviolet' => 'violeta',
        'whitegreen' => 'verde',
        'whiteblack' => 'grafite',
    ];

    public function up()
    {
        $this->db->where('config', 'app_tema_destaque')->delete($this->table);
    }

    public function down()
    {
        if ($this->db->where('config', 'app_tema_destaque')->count_all_results($this->table) > 0) {
            return;
        }

        $linha = $this->db->select('valor')->where('config', 'app_theme')->get($this->table)->row();
        $chave = strtolower(trim((string) ($linha ? $linha->valor : '')));

        $this->db->insert($this->table, [
            'config' => 'app_tema_destaque',
            'valor' => self::DESTAQUE_DOS_TEMAS_DA_V4[$chave] ?? 'laranja',
        ]);
    }
}
