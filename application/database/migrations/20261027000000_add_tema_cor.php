<?php

/**
 * Temas de cor do painel (#3032): padrão (laranja), azul, verde, violeta ou
 * grafite, independentes do modo de cor.
 *
 * - usuarios.tema_cor: preferência da pessoa. Nulo = sem preferência, vale o
 *   padrão do sistema.
 * - configuracoes.app_tema_cor: cor padrão do sistema, para quem ainda não
 *   escolheu. Nasce 'padrao' (laranja), então nada muda para quem não mexer.
 */
class Migration_add_tema_cor extends CI_Migration
{
    private $table = 'configuracoes';

    public function up()
    {
        if (! $this->db->field_exists('tema_cor', 'usuarios')) {
            $this->dbforge->add_column('usuarios', [
                'tema_cor' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            ]);
        }

        // Com db_debug desligado (produção) um ALTER com erro só vai para o log.
        if (! $this->db->field_exists('tema_cor', 'usuarios')) {
            throw new RuntimeException('Não foi possível criar a coluna usuarios.tema_cor; veja o log da aplicação.');
        }

        // Idempotente: quem já tem a configuração não é sobrescrito.
        $existe = $this->db->where('config', 'app_tema_cor')->count_all_results($this->table) > 0;
        if (! $existe) {
            $this->db->insert($this->table, ['config' => 'app_tema_cor', 'valor' => 'padrao']);
        }
    }

    public function down()
    {
        $this->db->where('config', 'app_tema_cor')->delete($this->table);

        if ($this->db->field_exists('tema_cor', 'usuarios')) {
            $this->dbforge->drop_column('usuarios', 'tema_cor');
        }
    }
}
