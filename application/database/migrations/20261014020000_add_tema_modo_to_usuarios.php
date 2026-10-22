<?php

/**
 * Preferência de modo de cor por usuário (#2854): claro, escuro ou sistema.
 * Nulo = sem preferência, vale o tema padrão das configurações
 * (app_tema_modo). A cor de destaque saiu na #2913: só o modo é guardado.
 */
class Migration_add_tema_modo_to_usuarios extends CI_Migration
{
    public function up()
    {
        $this->dbforge->add_column('usuarios', [
            'tema_modo' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
        ]);

        // Com db_debug desligado (produção) um ALTER com erro só vai para o log.
        if (! $this->db->field_exists('tema_modo', 'usuarios')) {
            throw new RuntimeException('Não foi possível criar a coluna usuarios.tema_modo; veja o log da aplicação.');
        }
    }

    public function down()
    {
        $this->dbforge->drop_column('usuarios', 'tema_modo');
    }
}
