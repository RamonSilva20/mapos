<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Audit_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('idLogs', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function add($data)
    {
        $this->db->insert('logs', $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        return $this->db->count_all('logs');
    }

    /**
     * Registro de ações da v5 (#2846): busca no usuário, na ação e no IP e
     * período pela data, os mais recentes primeiro.
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $this->aplicarFiltros($filtros);

        return $this->db->order_by('idLogs', 'DESC')->limit($limite, max(0, $offset))->get()->result();
    }

    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);

        return (int) $this->db->count_all_results();
    }

    private function aplicarFiltros(array $filtros): void
    {
        $this->db->from('logs');

        $pesquisa = $filtros['pesquisa'] ?? '';
        if ($pesquisa !== '') {
            $this->db->group_start()->like('usuario', $pesquisa)->or_like('tarefa', $pesquisa)->or_like('ip', $pesquisa)->group_end();
        }
        if (($filtros['de'] ?? '') !== '') {
            $this->db->where('data >=', $filtros['de']);
        }
        if (($filtros['ate'] ?? '') !== '') {
            $this->db->where('data <=', $filtros['ate']);
        }
    }

    public function clean()
    {
        $this->db->where('data <', date('Y-m-d', strtotime('- 30 days')));
        $this->db->delete('logs');

        if ($this->db->affected_rows()) {
            return true;
        }

        return false;
    }
}

/* End of file Log_model.php */
/* Location: ./application/models/Log_model.php */
