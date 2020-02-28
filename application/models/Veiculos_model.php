<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Veiculos_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array') {
        $this->db->select($fields . ', clientes.nomeCliente, clientes.celular as celular_cliente');
        $this->db->from($table);
        $this->db->join('clientes', 'clientes.idClientes = veiculos.clientes_id');
        $this->db->limit($perpage, $start);
        $this->db->order_by('idVeiculos', 'desc');

        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        
        $result = !$one ? $query->result() : $query->row();
        return $result;
    }

    public function getById($id) {
        $this->db->select('veiculos.*, clientes.*, clientes.celular as celular_cliente');
        $this->db->from('veiculos');
        $this->db->join('clientes', 'clientes.idClientes = veiculos.clientes_id');
        $this->db->where('idVeiculos', $id);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function add($table, $data) {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            return true;
        }
        return false;
    }

    public function edit($table, $data, $fieldID, $ID) {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);
        if ($this->db->affected_rows() >= 0) {
            return true;
        }
        return false;
    }

    public function delete($table, $fieldID, $ID) {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }
        return false;
    }

    public function count($table) {
        return $this->db->count_all($table);
    }

    public function getOsByVeiculo($id) {
        $this->db->where('veiculos_id', $id);
        $this->db->order_by('idOs', 'desc');
        $this->db->limit(10);
        return $this->db->get('os')->result();
    }

}
