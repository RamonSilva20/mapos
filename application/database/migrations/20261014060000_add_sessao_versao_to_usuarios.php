<?php

/**
 * Versão da sessão do painel por usuário (#2879). A sessão guarda a versão do
 * login; trocar a senha (ou o admin trocá-la) soma 1 e as outras sessões abertas
 * do usuário deixam de valer na próxima requisição.
 */
class Migration_add_sessao_versao_to_usuarios extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('usuarios', [
            'sessao_versao' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);

        // Com db_debug desligado (produção) um ALTER com erro só vai para o log.
        if (! $this->db->field_exists('sessao_versao', 'usuarios')) {
            throw new RuntimeException('Não foi possível criar a coluna usuarios.sessao_versao; veja o log da aplicação.');
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('usuarios', 'sessao_versao');
    }
}
