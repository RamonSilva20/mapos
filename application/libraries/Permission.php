<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Permission Class
 *
 * Biblioteca para controle de permissões
 *
 * @author      Ramon Silva
 * @copyright   Copyright (c) 2013, Ramon Silva.
 *
 * @since       Version 1.0
 * v... Visualizar
 * e... Editar
 * d... Deletar ou Desabilitar
 * c... Cadastrar
 */
class Permission
{
    /** @var object Instância do CodeIgniter, preenchida no construtor */
    private $CI;

    private $permissions = [];

    private $table = 'permissoes'; //Nome tabela onde ficam armazenadas as permissões

    private $pk = 'idPermissao'; // Nome da chave primaria da tabela

    private $select = 'permissoes'; // Campo onde fica o array de permissoes.

    public function __construct()
    {
        log_message('debug', 'Permission Class Initialized');
        $this->CI = &get_instance();
        $this->CI->load->database();
    }

    public function checkPermission($idPermissao = null, $atividade = null)
    {
        if ($idPermissao == null || $atividade == null) {
            return false;
        }
        // Se as permissões não estiverem carregadas, requisita o carregamento
        if ($this->permissions == null) {
            // Se não carregar retorna falso
            if (! $this->loadPermission($idPermissao)) {
                return false;
            }
        }

        if (is_array($this->permissions[0])) {
            if (array_key_exists($atividade, $this->permissions[0])) {
                // compara a atividade requisitada com a permissão.
                if ($this->permissions[0][$atividade] == 1) {
                    return true;
                }
            }
        }

        return false;
    }

    private function loadPermission($id = null)
    {
        if ($id != null) {
            $this->CI->db->select($this->table . '.' . $this->select);
            $this->CI->db->where($this->pk, $id);
            // Grupo desativado não concede nada (#2846): até a v4 a situação
            // do grupo era só informativa.
            $this->CI->db->where('situacao', 1);
            $this->CI->db->limit(1);
            // row_array() devolve null quando a consulta não traz linha, e
            // count(null) é TypeError fatal no PHP 8. Um usuário cujo
            // permissoes_id aponta para uma linha removida derrubava a tela
            // em vez de receber a negação de acesso.
            $array = $this->CI->db->get($this->table)->row_array();

            if (! empty($array)) {
                $raw = $array[$this->select];
                $array = json_decode_legacy($raw);
                $this->permissions = [$array];

                return true;
            }
        }

        return false;
    }
}
