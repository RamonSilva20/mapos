<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Garantias_model extends CI_Model
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
        $this->db->select($fields.', usuarios.nome, usuarios.idUsuarios');
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        $this->db->join('usuarios', 'usuarios.idUsuarios = garantias.usuarios_id');
        $this->db->order_by('idGarantias', 'asc');
        if ($where) {
            $this->db->where($where);
        }
        
        $query = $this->db->get();
        
        $result =  !$one  ? $query->result() : $query->row();
        return $result;
    }

    public function getById($id)
    {
        $this->db->select('garantias.*, usuarios.telefone, usuarios.email, usuarios.nome');
        $this->db->from('garantias');
        $this->db->join('usuarios', 'usuarios.idUsuarios = garantias.usuarios_id');
        $this->db->where('garantias.idGarantias', $id);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function getByIdOsGarantia($id)
    {
        $this->db->select('garantias.*, clientes.nomeCliente, os.idOS as idOs, os.dataFinal as osDataFinal,
         usuarios.telefone as tecnicoTelefone, usuarios.email as tecnicoEmail, usuarios.nome as tecnicoName');
        $this->db->from('garantias');
        $this->db->join('os', 'os.garantias_id = garantias.idGarantias');
        $this->db->join('clientes', 'os.clientes_id = clientes.idClientes');
        $this->db->join('usuarios', 'os.usuarios_id = usuarios.idUsuarios');
        $this->db->where('garantias.idGarantias', $id);
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

    public function autoCompleteProduto($q)
    {
        $this->db->select('*');
        $this->db->limit(5);
        $this->db->like('descricao', $q);
        $query = $this->db->get('produtos');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label'=>$row['descricao'].' | Preço: R$ '.$row['precoVenda'].' | Estoque: '.$row['estoque'],'estoque'=>$row['estoque'],'id'=>$row['idProdutos'],'preco'=>$row['precoVenda']];
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteCliente($q)
    {
        $this->db->select('*');
        $this->db->limit(5);
        $this->db->like('nomeCliente', $q);
        $query = $this->db->get('clientes');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label'=>$row['nomeCliente'].' | Telefone: '.$row['telefone'],'id'=>$row['idClientes']];
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteUsuario($q)
    {
        $this->db->select('*');
        $this->db->limit(5);
        $this->db->like('nome', $q);
        $this->db->where('situacao', 1);
        $query = $this->db->get('usuarios');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = ['label'=>$row['nome'].' | Telefone: '.$row['telefone'],'id'=>$row['idUsuarios']];
            }
            echo json_encode($row_set);
        }
    }
}

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
