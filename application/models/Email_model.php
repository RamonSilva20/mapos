<?php

class Email_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('id', 'desc');
        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    /**
     * Fila de e-mails da v5 (#2846): os mais recentes primeiro, com o filtro
     * de situação. A mensagem (HTML) não entra na listagem.
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $this->aplicarFiltros($filtros);

        return $this->db->select('id, to, status, date, headers')->order_by('id', 'DESC')->limit($limite, max(0, $offset))->get()->result();
    }

    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);

        return (int) $this->db->count_all_results();
    }

    private function aplicarFiltros(array $filtros): void
    {
        $this->db->from('email_queue');
        if (($filtros['status'] ?? '') !== '') {
            $this->db->where('status', $filtros['status']);
        }
    }

    public function getById($id)
    {
        $this->db->where('id', $id);
        $this->db->limit(1);

        return $this->db->get('email_queue')->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function delete($table, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table)
    {
        return $this->db->count_all($table);
    }
}
