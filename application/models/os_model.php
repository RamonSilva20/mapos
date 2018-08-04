<?php if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

class Os_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {

        $this->db->select($fields . ',clientes.nomeCliente');
        $this->db->from($table);
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->limit($perpage, $start);
        $this->db->order_by('idOs', 'desc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result = !$one ? $query->result() : $query->row();
        return $result;
    }

    public function getById($id)
    {
        $this->db->select('os.*, clientes.*, usuarios.telefone, usuarios.nome');
        $this->db->from('os');
        $this->db->join('clientes', 'clientes.idClientes = os.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = os.usuarios_id');
        $this->db->where('os.idOs', $id);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function getProdutos($id = null)
    {
        $this->db->select('produtos_os.*, produtos.*');
        $this->db->from('produtos_os');
        $this->db->join('produtos', 'produtos.idProdutos = produtos_os.produtos_id');
        $this->db->where('os_id', $id);
        return $this->db->get()->result();
    }

    public function getServicos($id = null)
    {
        $this->db->select('servicos_os.*, servicos.*');
        $this->db->from('servicos_os');
        $this->db->join('servicos', 'servicos.idServicos = servicos_os.servicos_id');
        $this->db->where('os_id', $id);
        return $this->db->get()->result();
    }

    public function getHistorico($id = null)
    {
        $this->db->select('historico.*');
        $this->db->from('historico');
        $this->db->order_by('idHistorico', 'desc');
        $this->db->where('idOs', $id);
        return $this->db->get()->result();
    }

    public function getCompras($id = null)
    {
        $this->db->select('compras.*');
        $this->db->from('compras');
        $this->db->where('idOs', $id);
        return $this->db->get()->result();
    }

    // public function getCompras($id = null){
    //     $this->db->select('compras_os.*, compras.*');
    //     $this->db->from('compras_os');
    //     $this->db->join('compras','compras.idCompras = compras_os.compra_id');
    //     $this->db->where('os_id',$id);
    //     return $this->db->get()->result();
    // }

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
        if ($query->num_rows > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = array('label' => $row['descricao'] . ' | Preço: R$ ' . $row['precoVenda'] . ' | Estoque: ' . $row['estoque'], 'estoque' => $row['estoque'], 'id' => $row['idProdutos'], 'preco' => $row['precoVenda']);
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteCliente($q)
    {

        $this->db->select('*');
        $this->db->limit(50);
        $this->db->like('nomeCliente', $q);
        $query = $this->db->get('clientes');
        if ($query->num_rows > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = array('label' => $row['nomeCliente'] . ' | Telefone: ' . $row['telefone'], 'id' => $row['idClientes']);
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
        if ($query->num_rows > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = array('label' => $row['nome'] . ' | Telefone: ' . $row['telefone'], 'id' => $row['idUsuarios']);
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteServico($q)
    {

        $this->db->select('*');
        $this->db->limit(50);
        $this->db->like('nome', $q);
        $query = $this->db->get('servicos');
        if ($query->num_rows > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = array('label' => $row['nome'] . ' | Preço: R$ ' . $row['preco'], 'id' => $row['idServicos'], 'preco' => $row['preco']);
            }
            echo json_encode($row_set);
        }
    }

    public function autoCompleteCompra($q)
    {

        $this->db->select('*');
        $this->db->limit(20);
        $this->db->like('idCompras', $q);
        $query = $this->db->get('compras');
        if ($query->num_rows > 0) {
            foreach ($query->result_array() as $row) {
                $row_set[] = array('label' => $row['descricao'] . ' | Preço: R$ ' . $row['valor'], 'id' => $row['idCompras'], 'preco' => $row['valor']);
            }
            echo json_encode($row_set);
        }
    }

    public function anexar($os, $anexo, $url, $thumb, $path)
    {

        $this->db->set('anexo', $anexo);
        $this->db->set('url', $url);
        $this->db->set('thumb', $thumb);
        $this->db->set('path', $path);
        $this->db->set('os_id', $os);

        return $this->db->insert('anexos');
    }

    public function getAnexos($os)
    {

        $this->db->where('os_id', $os);
        return $this->db->get('anexos')->result();
    }
}
