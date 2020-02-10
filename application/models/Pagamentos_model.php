<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pagamentos_model extends CI_Model
{

    /**
     * author: Wilmerson Felipe
     * email: will.phelipe@gmail.com
     *
     */

    function __construct()
    {
        parent::__construct();
    }

    
    function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        
        $this->db->select($fields.', pagamento.nome, pagamento.idPag');
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        $this->db->order_by('idPag', 'asc');
        if ($where) {
            $this->db->where($where);
        }
        
        $query = $this->db->get();
        
        $result =  !$one  ? $query->result() : $query->row();
        return $result;
    }

    function getById($id)
    {
        $this->db->select('pagamento.idPag, pagamento.nome, pagamento.public_key, pagamento.access_token, pagamento.client_id, pagamento.client_secret');
        $this->db->from('pagamento');
        $this->db->where('pagamento.idPag', $id);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    function getPagamentos($id = null)//provisorio enquanto não implementar o default de pagamento
    {
        $this->db->select('pagamento.idPag, pagamento.public_key, pagamento.access_token, pagamento.client_id, pagamento.client_secret');
        $this->db->from('pagamento');
        $this->db->where('idPag', 1);
        return $this->db->get()->row();
    }

     
    function add($table, $data, $returnId = false)
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
    
    function edit($table, $data, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->update($table, $data);

        if ($this->db->affected_rows() >= 0) {
            return true;
        }
        
        return false;
    }
    
    function delete($table, $fieldID, $ID)
    {
        $this->db->where($fieldID, $ID);
        $this->db->delete($table);
        if ($this->db->affected_rows() == '1') {
            return true;
        }
        
        return false;
    }

    function count($table)
    {
        return $this->db->count_all($table);
    }

  
}

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
