<?php

/**
 * Histórico de status da OS (#2842), mostrado na aba Histórico da tela da OS.
 *
 * Uma linha por mudança: status anterior (null ao criar), status novo, quem
 * mudou (usuarios_id; null quando a OS foi aberta pelo cliente na área do
 * cliente) e quando. OS anteriores a esta versão não têm histórico.
 */
class Migration_create_os_historico extends CI_Migration
{
    private $table = 'os_historico';

    public function up()
    {
        $this->dbforge->add_field([
            'idHistorico' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'os_id' => ['type' => 'INT', 'constraint' => 11],
            'status_anterior' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'status_novo' => ['type' => 'VARCHAR', 'constraint' => 45],
            'usuarios_id' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'data_hora' => ['type' => 'DATETIME'],
        ]);
        $this->dbforge->add_key('idHistorico', true);
        $this->dbforge->add_key('os_id');
        // Charset e collation vêm da conexão (config/database.php), como nas outras tabelas.
        $this->dbforge->create_table($this->table, true, ['ENGINE' => 'InnoDB']);

        // Com db_debug desligado (produção) um CREATE com erro só vai para o
        // log e a versão avançaria mesmo assim.
        if (! $this->db->table_exists($this->table)) {
            throw new RuntimeException("Não foi possível criar a tabela {$this->table}; veja o log da aplicação.");
        }
    }

    public function down()
    {
        $this->dbforge->drop_table($this->table, true);
    }
}
