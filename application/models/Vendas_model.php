<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendas_model extends CI_Model
{
    /**
     * author: Ramon Silva
     * email: silva018-mg@yahoo.com.br.
     */
    public function __construct($table = 'vendas')
    {
        parent::__construct();
        $this->table = $table;
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields.', clientes.nomeCliente, clientes.idClientes');
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        $this->db->join('clientes', 'clientes.idClientes = '.$table.'.clientes_id');
        $this->db->order_by('idVendas', 'desc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();

        return $result;
    }

    public function getById($id)
    {
        $this->db->select('vendas.*, clientes.*, usuarios.telefone, usuarios.email,usuarios.nome');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        $this->db->where('vendas.idVendas', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getProdutos($id = null)
    {
        $this->db->select('itens_de_vendas.*, produtos.*');
        $this->db->from('itens_de_vendas');
        $this->db->join('produtos', 'produtos.idProdutos = itens_de_vendas.produtos_id');
        $this->db->where('vendas_id', $id);

        return $this->db->get()->result();
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
                $row_set[] = array('label' => $row['descricao'].' | Preço: R$ '.$row['precoVenda'].' | Estoque: '.$row['estoque'], 'estoque' => $row['estoque'], 'id' => $row['idProdutos'], 'preco' => $row['precoVenda']);
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteCliente($q)
    {
        $this->db->select('idClientes, nomeCliente, telefone');
        $this->db->limit(5);
        $this->db->like('nomeCliente', $q);
        $query = $this->db->get('clientes');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = array('label' => $row['nomeCliente'].' | Telefone: '.$row['telefone'], 'id' => $row['idClientes']);
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteUsuario($q)
    {
        $this->db->select('idUsuarios, nome, telefone');
        $this->db->limit(5);
        $this->db->like('nome', $q);
        $this->db->where('situacao', 1);
        $query = $this->db->get('usuarios');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = array('label' => $row['nome'].' | Telefone: '.$row['telefone'], 'id' => $row['idUsuarios']);
            }
            echo json_encode($row_set);
        }
    }
    public function getTotalDesconto()
    {
        $this->db->select('desconto');
        $this->db->from('totalDesconto');
        $this->db->where('produtos_os.os_id', $id);
        $resultado = $this->db->get();
        if($resultado->num_rows() === 1 ){
            if (is_array($resultado->result())) {
                if (is_object($resultado->result()[0])) {
                    $resultado =  $resultado->result()[0]->TotalDesconto;
                }else{
                    $resultado = FALSE;
                }
            }else{
                $resultado = FALSE;
            }
        }else{
            $resultado = FALSE;
        }
        return $resultado;
    }
    private function _updateDescontoTotal($desconto, $id, $opercao_matematica = '+')
    {
        $this->db->set('descontoTotal', "descontoTotal {$opercao_matematica} {$desconto}", FALSE);
        return $this->_salvaUpdateValida($id, 'idVendas');
    }      
    private function _updateValorTotal($valorTotal, $id, $opercao_matematica = '+')
    {
        $this->db->set('valorTotal', "valorTotal {$opercao_matematica} {$valorTotal}", FALSE);
        return $this->_salvaUpdateValida($id, 'idVendas');
    }
    private function _salvaUpdateValida($id, $campo_where)
    {
        $id = intval($id);
        if ($id > 0) {
            $this->db->where($campo_where, $id);
            $resultado = $this->db->update($this->table);
        }else{
            $resultado = false;
        }
        return $resultado;
    }
    private function _converteValorParaAmericano($valor)
    {
        return str_replace(',', '.', str_replace('.','', $valor));
    }
    public function AdicionarProduto($id = 0, $desconto = 0, $valorTotal = 0, $opercao_matematica = '+')
    {
        $resultado = $this->_updateDescontoTotal($desconto, $id, $opercao_matematica);
        $resultado = $this->_updateValorTotal($valorTotal, $id, $opercao_matematica);
    }
}

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
