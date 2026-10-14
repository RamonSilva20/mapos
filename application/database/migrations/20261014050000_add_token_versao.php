<?php

/**
 * Versão dos access tokens da API (#2879): usuarios.token_versao e
 * clientes.token_versao. O token leva a versão no momento do login (claim `tv`)
 * e só vale enquanto ela for igual à do banco; trocar a senha ou desativar a
 * conta soma 1 e derruba os tokens já emitidos. Ver Authorization_Token.
 */
class Migration_add_token_versao extends CI_Migration
{
    private const TABELAS = ['usuarios', 'clientes'];

    public function up()
    {
        foreach (self::TABELAS as $tabela) {
            if (! $this->db->field_exists('token_versao', $tabela)) {
                $this->dbforge->add_column($tabela, [
                    'token_versao' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
                ]);
            }
        }
    }

    public function down()
    {
        foreach (self::TABELAS as $tabela) {
            if ($this->db->field_exists('token_versao', $tabela)) {
                $this->dbforge->drop_column($tabela, 'token_versao');
            }
        }
    }
}
