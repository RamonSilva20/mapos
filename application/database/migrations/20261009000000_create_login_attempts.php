<?php

/**
 * Tentativas de login com falha, para o bloqueio temporário (#2870).
 *
 * Uma linha por escopo (usuario, cliente), tipo da chave (email, ip) e chave.
 * A chave é o SHA-256 do e-mail normalizado ou do IP: a tabela não guarda
 * nenhum dos dois em claro. Ver application/libraries/Limite_login.php.
 */
class Migration_create_login_attempts extends CI_Migration
{
    private $table = 'login_attempts';

    public function up()
    {
        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'escopo' => ['type' => 'VARCHAR', 'constraint' => 20],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 10],
            'chave' => ['type' => 'CHAR', 'constraint' => 64],
            'falhas' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0],
            'bloqueado_ate' => ['type' => 'DATETIME', 'null' => true],
            'ultima_falha' => ['type' => 'DATETIME'],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('ultima_falha');
        // Charset e collation vêm da conexão (config/database.php), como nas outras tabelas.
        $this->dbforge->create_table($this->table, true, ['ENGINE' => 'InnoDB']);

        // Com db_debug desligado (produção) um CREATE com erro só vai para o
        // log e a versão avançaria mesmo assim.
        if (! $this->db->table_exists($this->table)) {
            throw new RuntimeException("Não foi possível criar a tabela {$this->table}; veja o log da aplicação.");
        }

        $this->db->query('CREATE UNIQUE INDEX login_attempts_chave ON ' . $this->db->protect_identifiers($this->table) . ' (escopo, tipo, chave)');
    }

    public function down()
    {
        $this->dbforge->drop_table($this->table, true);
    }
}
