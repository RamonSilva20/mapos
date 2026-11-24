<?php

class Migration_create_api_sessoes extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'identificador' => ['type' => 'CHAR', 'constraint' => 32],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 10],
            'titular_id' => ['type' => 'INT', 'constraint' => 11],
            'dispositivo' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'criada_em' => ['type' => 'DATETIME'],
            'ultimo_uso_em' => ['type' => 'DATETIME'],
            'expira_em' => ['type' => 'DATETIME'],
            'revogada_em' => ['type' => 'DATETIME', 'null' => true],
            'motivo' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key(['tipo', 'titular_id']);
        $this->dbforge->create_table('api_sessoes', true, ['ENGINE' => 'InnoDB']);

        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'sessao_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'criado_em' => ['type' => 'DATETIME'],
            'expira_em' => ['type' => 'DATETIME'],
            'usado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('sessao_id');
        $this->dbforge->add_key('expira_em');
        $this->dbforge->create_table('api_tokens_renovacao', true, ['ENGINE' => 'InnoDB']);

        foreach (['api_sessoes', 'api_tokens_renovacao'] as $tabela) {
            if (! $this->db->table_exists($tabela)) {
                throw new RuntimeException("Não foi possível criar a tabela {$tabela}; veja o log da aplicação.");
            }
        }

        $this->db->query('CREATE UNIQUE INDEX api_sessoes_identificador ON ' . $this->db->protect_identifiers('api_sessoes') . ' (identificador)');
        $this->db->query('CREATE UNIQUE INDEX api_tokens_renovacao_hash ON ' . $this->db->protect_identifiers('api_tokens_renovacao') . ' (token_hash)');

        $this->dbforge->drop_table('api_refresh_tokens', true);

        foreach (['usuarios', 'clientes'] as $tabela) {
            if ($this->db->field_exists('token_versao', $tabela)) {
                $this->dbforge->drop_column($tabela, 'token_versao');
            }
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('api_tokens_renovacao', true);
        $this->dbforge->drop_table('api_sessoes', true);

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
        $this->dbforge->create_table('api_refresh_tokens', true, ['ENGINE' => 'InnoDB']);

        foreach (['usuarios', 'clientes'] as $tabela) {
            if (! $this->db->field_exists('token_versao', $tabela)) {
                $this->dbforge->add_column($tabela, [
                    'token_versao' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
                ]);
            }
        }
    }
}
