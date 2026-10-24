<?php

/**
 * Autenticação em dois fatores (TOTP) opcional (#2876).
 *
 * usuarios.totp_secreto: segredo TOTP cifrado (AES-256-GCM com a
 *   encryption_key da instalação), nulo enquanto o 2FA não foi ativado.
 * usuarios.totp_ativado_em: quando o usuário confirmou o primeiro código.
 * usuarios.totp_ultimo_passo: última janela de 30 s aceita, para um código
 *   não valer duas vezes.
 * usuarios_codigos_recuperacao: códigos de uso único para quem perdeu o
 *   aplicativo; só o SHA-256 fica no banco.
 * permissoes.exigir_2fa: o grupo só entra no painel com o 2FA ativo.
 */
class Migration_create_two_factor extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('usuarios', [
            'totp_secreto' => ['type' => 'TEXT', 'null' => true],
            'totp_ativado_em' => ['type' => 'DATETIME', 'null' => true],
            'totp_ultimo_passo' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
        ]);

        $this->dbforge->add_column('permissoes', [
            'exigir_2fa' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
        ]);

        $this->dbforge->add_field([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'usuario_id' => ['type' => 'INT', 'constraint' => 11],
            'codigo_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'criado_em' => ['type' => 'DATETIME'],
            'usado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->dbforge->add_key('id', true);
        $this->dbforge->add_key('usuario_id');
        // Charset e collation vêm da conexão (config/database.php), como nas outras tabelas.
        $this->dbforge->create_table('usuarios_codigos_recuperacao', true, ['ENGINE' => 'InnoDB']);

        // Com db_debug desligado (produção) um CREATE ou ALTER com erro só vai para o log.
        if (! $this->db->table_exists('usuarios_codigos_recuperacao') || ! $this->db->field_exists('totp_secreto', 'usuarios') || ! $this->db->field_exists('exigir_2fa', 'permissoes')) {
            throw new RuntimeException('Não foi possível criar as estruturas do 2FA; veja o log da aplicação.');
        }

        $this->db->query('CREATE UNIQUE INDEX usuarios_codigos_hash ON ' . $this->db->protect_identifiers('usuarios_codigos_recuperacao') . ' (codigo_hash)');
    }

    public function down()
    {
        $this->dbforge->drop_table('usuarios_codigos_recuperacao', true);
        $this->dbforge->drop_column('permissoes', 'exigir_2fa');
        foreach (['totp_secreto', 'totp_ativado_em', 'totp_ultimo_passo'] as $coluna) {
            $this->dbforge->drop_column('usuarios', $coluna);
        }
    }
}
