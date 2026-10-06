<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Dá um valor padrão a `resets_de_senha.token_utilizado`.
 *
 * A coluna é NOT NULL e sem default desde a 20220307173741, e quem escreve nela
 * — o `Mine::gerarTokenResetarSenha()` — manda só e-mail, token e
 * data_expiracao, porque um token recem-criado não está utilizado.
 *
 * Em modo não estrito o MySQL preenche com 0 e nada aparece. Em
 * STRICT_TRANS_TABLES, que é o padrão do MySQL 5.7 em diante, a inserção é
 * recusada com 1364 "Field 'token_utilizado' doesn't have a default value", o
 * `add()` devolve false e a tela responde "Falha ao realizar solicitação!":
 * ou seja, a recuperação de senha estava indisponível numa instalação padrão.
 *
 * O default é a invariante da coluna — um token nasce não utilizado — e não
 * um conserto pontual do controller: qualquer escrita futura de
 * `resets_de_senha` herda o mesmo valor sem precisar conhecê-lo.
 */
class Migration_default_token_utilizado extends CI_Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE `resets_de_senha` ALTER `token_utilizado` SET DEFAULT 0');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `resets_de_senha` ALTER `token_utilizado` DROP DEFAULT');
    }
}
