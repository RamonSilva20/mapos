<?php

/**
 * Tokens de recuperação de senha passam a ser guardados só como hash (#2875).
 *
 * Os tokens pendentes foram gravados em texto claro e não casam mais com a
 * busca por hash: são marcados como utilizados, e o cliente pede outro. O
 * down() não tem o que desfazer, porque o token em claro não é recuperável a
 * partir de um novo formato, e reativar tokens antigos não é desejável.
 */
class Migration_invalidate_plain_password_reset_tokens extends CI_Migration
{
    private $table = 'resets_de_senha';

    public function up()
    {
        if (! $this->db->table_exists($this->table)) {
            return;
        }

        $this->db->where('token_utilizado', 0)->update($this->table, ['token_utilizado' => 1]);
    }

    public function down()
    {
    }
}
