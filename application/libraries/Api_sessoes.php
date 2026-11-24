<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Api_sessoes
{
    private $db;

    /** @param  array{db?: object}|null  $opcoes */
    public function __construct($opcoes = null)
    {
        $this->db = is_array($opcoes) && isset($opcoes['db']) ? $opcoes['db'] : get_instance()->db;
    }

    public function encerrarDoUsuario(int $id, string $motivo = 'senha_alterada', ?string $exceto = null): void
    {
        $this->encerrar('usuario', $id, $motivo, $exceto);
    }

    public function encerrarDoCliente(int $id, string $motivo = 'senha_alterada', ?string $exceto = null): void
    {
        $this->encerrar('cliente', $id, $motivo, $exceto);
    }

    private function encerrar(string $tipo, int $id, string $motivo, ?string $exceto): void
    {
        if ($id <= 0 || ! $this->db->table_exists('api_sessoes')) {
            return;
        }

        $this->db->where('tipo', $tipo)->where('titular_id', $id)->where('revogada_em IS NULL', null, false);
        if ($exceto !== null) {
            $this->db->where('identificador !=', $exceto);
        }
        $this->db->update('api_sessoes', ['revogada_em' => date('Y-m-d H:i:s'), 'motivo' => mb_substr($motivo, 0, 30)]);
    }
}
