<?php

/**
 * Refresh tokens da API (#2869) e contador do limite de requisições.
 *
 * api_refresh_tokens: uma linha por refresh token emitido no login. Só o
 * SHA-256 do token fica no banco. Cada uso gira o token (o antigo ganha
 * usado_em e nasce outro na mesma familia); reapresentar um token já usado
 * revoga a familia inteira. Ver application/libraries/Refresh_token.php.
 *
 * api_rate_limit: contador por janela de um minuto e chave (HMAC do IP).
 * Ver REST_Controller::limiteDeRequisicoes().
 */
class Migration_create_api_tokens extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'usuario_id' => ['type' => 'INT', 'constraint' => 11],
            'familia' => ['type' => 'CHAR', 'constraint' => 32],
            'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'criado_em' => ['type' => 'DATETIME'],
            'expira_em' => ['type' => 'DATETIME'],
            'usado_em' => ['type' => 'DATETIME', 'null' => true],
            'revogado_em' => ['type' => 'DATETIME', 'null' => true],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('usuario_id');
        $this->dbforge->add_key('familia');
        // Charset e collation vêm da conexão (config/database.php), como nas outras tabelas.
        $this->dbforge->create_table('api_refresh_tokens', true, ['ENGINE' => 'InnoDB']);

        $this->dbforge->add_field([
            'chave' => ['type' => 'CHAR', 'constraint' => 64],
            'janela' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'total' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
        ]);
        $this->dbforge->add_key(['chave', 'janela'], true);
        $this->dbforge->add_key('janela');
        $this->dbforge->create_table('api_rate_limit', true, ['ENGINE' => 'InnoDB']);

        // Com db_debug desligado (produção) um CREATE com erro só vai para o log
        // e a versão avançaria mesmo assim.
        foreach (['api_refresh_tokens', 'api_rate_limit'] as $tabela) {
            if (! $this->db->table_exists($tabela)) {
                throw new RuntimeException("Não foi possível criar a tabela {$tabela}; veja o log da aplicação.");
            }
        }

        $this->db->query('CREATE UNIQUE INDEX api_refresh_tokens_hash ON ' . $this->db->protect_identifiers('api_refresh_tokens') . ' (token_hash)');
    }

    public function down()
    {
        $this->dbforge->drop_table('api_rate_limit', true);
        $this->dbforge->drop_table('api_refresh_tokens', true);
    }
}
