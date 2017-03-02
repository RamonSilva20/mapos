<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Financeiro_model extends CI_Model
{
    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 15, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('data_vencimento', 'asc');
        $this->db->order_by('idLancamentos', 'asc');

        $this->db->limit($perpage, $start);
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();

        // var_dump($this->db);
        return $result;
    }
    public function getTotalDespesas($where = '')
    {

        $this->db->select('(sum(valor) - sum(desconto)) as total_despesa');
        $this->db->from('lancamentos');
        $this->db->where($where);
        $this->db->where('tipo', 'despesa');

        $query = $this->db->get()->result();
        $result = $query[0]->total_despesa;

        return $result;
    }
    public function getTotalReceitas($where = '')
    {
        $this->db->select('(sum(valor) - sum(desconto)) as total_receita');
        $this->db->from('lancamentos');
        $this->db->where($where);
        $this->db->where('tipo', 'receita');

        $query = $this->db->get()->result();
        $result = $query[0]->total_receita;

        return $result;
    }

    public function getById($id)
    {
        $this->db->where('idClientes', $id);
        $this->db->limit(1);

        return $this->db->get('clientes')->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function edit($table, $data, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
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

    public function count($table, $where)
    {

        $this->db->from($table);
        if ($where) {
            $this->db->where($where);
        }
        return $this->db->count_all_results();
    }
}
