<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Outros_produtos_servicos_os_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getByOs($os_id)
    {
        $this->db->where('os_id', $os_id);
        $this->db->order_by('ordem', 'ASC');
        $this->db->order_by('idOutros', 'ASC');
        return $this->db->get('outros_produtos_servicos_os')->result();
    }

    public function getById($id)
    {
        $this->db->where('idOutros', $id);
        return $this->db->get('outros_produtos_servicos_os')->row();
    }

    public function add($data)
    {
        $this->db->insert('outros_produtos_servicos_os', $data);
        return $this->db->insert_id();
    }

    public function edit($id, $data)
    {
        $this->db->where('idOutros', $id);
        return $this->db->update('outros_produtos_servicos_os', $data);
    }

    public function delete($id)
    {
        $this->db->where('idOutros', $id);
        return $this->db->delete('outros_produtos_servicos_os');
    }

    public function getTotalByOs($os_id)
    {
        $this->db->select_sum('preco');
        $this->db->where('os_id', $os_id);
        $result = $this->db->get('outros_produtos_servicos_os')->row();
        return $result->preco ? floatval($result->preco) : 0;
    }
}


