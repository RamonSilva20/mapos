<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cobrancas_model extends CI_Model
{

    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br
     *
     */

    public function __construct()
    {
        parent::__construct();
    }

    
    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields, 'vendas.*,os.*');
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        $this->db->order_by('idCobranca', 'desc');
        if ($where) {
            $this->db->where($where);
        }
        
        $query = $this->db->get();
        $result =  !$one  ? $query->result() : $query->row();

        return $result;
    }
 

    public function getById($id)
    {
        $this->db->select('vendas.*, clientes.*, cobrancas.*,os.*, clientes.email as emailCliente, lancamentos.data_vencimento, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        $this->db->join('cobrancas', 'cobrancas.clientes_id = vendas.clientes_id');
        $this->db->join('os', 'os.idOs = cobrancas.os_id');
        $this->db->join('lancamentos', 'vendas.idVendas = lancamentos.vendas_id', 'LEFT');
        $this->db->where('vendas.idVendas', $id);
        $this->db->or_where('cobrancas.charge_id', $id);
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    public function add($table, $data, $returnId = false)
    {
        $this->db->insert($table, $data);
        if ($this->db->affected_rows() == '1') {
            if ($returnId == true) {
                return $this->db->insert_id($table);
            }
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

    public function count($table)
    {
        return $this->db->count_all($table);
    }
}
