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

    public function __construct()
    {
        parent::__construct();
    }

    
    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
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

    public function getById($id)
    {
        $this->db->select('pagamento.idPag, pagamento.nome, pagamento.public_key, pagamento.access_token, pagamento.client_id, pagamento.client_secret, pagamento.default_pag');
        $this->db->from('pagamento');
        $this->db->where('pagamento.idPag', $id);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function getPagamentos()
    {
        $this->db->select('pagamento.idPag, pagamento.nome, pagamento.public_key, pagamento.access_token, pagamento.client_id, pagamento.client_secret');
        $this->db->from('pagamento');
        $this->db->where('default_pag', 1);
        return $this->db->get()->row();
    }

    public function duplicadoPagDefault()
    {
        $this->db->select('pagamento.idPag, pagamento.nome, pagamento.public_key, pagamento.access_token, pagamento.client_id, pagamento.client_secret, pagamento.default_pag');
        $this->db->from('pagamento');
        $this->db->where('pagamento.default_pag', 1);
        
        $json = json_encode($this->db->get()->row());
        return json_decode($json);
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
        if ($data['default_pag']) {
            $this->db->set('default_pag', 0);
            $this->db->update($table);
        }

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

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
