<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Categorias_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('categoria', 'asc');
        $this->db->limit($perpage, $start);
        
        if ($where) {
            $this->db->like('categoria', $where);
            $this->db->or_like('tipo', $where);
        }

        $query = $this->db->get();

        $result = ! $one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $this->db->where('idCategorias', $id);
        $this->db->limit(1);

        return $this->db->get('categorias')->row();
    }

    public function add($table, $data)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return $this->db->insert_id();
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
        // Verificar se há lançamentos vinculados
        $this->db->where('categorias_id', $ID);
        $count = $this->db->count_all_results('lancamentos');
        
        if ($count > 0) {
            return false; // Não pode excluir se houver lançamentos
        }

        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }

        return false;
    }

    public function count($table, $where = '')
    {
        if ($where) {
            $this->db->like('categoria', $where);
            $this->db->or_like('tipo', $where);
        }
        return $this->db->count_all_results($table);
    }

    public function getByTipo($tipo)
    {
        $this->db->where('tipo', $tipo);
        $this->db->where('status', 1);
        $this->db->order_by('categoria', 'asc');
        return $this->db->get('categorias')->result();
    }

    public function getAll()
    {
        $this->db->order_by('tipo', 'asc');
        $this->db->order_by('categoria', 'asc');
        return $this->db->get('categorias')->result();
    }

    public function hasLancamentos($id)
    {
        $this->db->where('categorias_id', $id);
        return $this->db->count_all_results('lancamentos') > 0;
    }
}

