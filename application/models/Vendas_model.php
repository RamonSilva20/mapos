<?php

use Piggly\Pix\StaticPayload;

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vendas_model extends CI_Model
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
        $this->db->select($fields.', clientes.nomeCliente, clientes.idClientes');
        $this->db->from($table);
        $this->db->limit($perpage, $start);
        $this->db->join('clientes', 'clientes.idClientes = '.$table.'.clientes_id');
        $this->db->order_by('idVendas', 'desc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();

        $result =  !$one  ? $query->result() : $query->row();
        return $result;
    }

    public function getById($id)
    {
        $this->db->select('vendas.*, clientes.*, clientes.email as emailCliente, lancamentos.data_vencimento, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        $this->db->join('lancamentos', 'vendas.idVendas = lancamentos.vendas_id', 'LEFT');
        $this->db->where('vendas.idVendas', $id);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    public function isEditable($id = null)
    {
        if ($vendas = $this->getById($id)) {
            if ($vendas->faturado) {
                return $this->data['configuration']['control_edit_vendas'] == '1';
            }
        }
        return true;
    }

    public function getByIdCobrancas($id)
    {
        $this->db->select('vendas.*, clientes.*, clientes.email as emailCliente, lancamentos.data_vencimento, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario, usuarios.nome, usuarios.nome, cobrancas.vendas_id,cobrancas.idCobranca,cobrancas.status');
        $this->db->from('vendas');
        $this->db->join('clientes', 'clientes.idClientes = vendas.clientes_id');
        $this->db->join('usuarios', 'usuarios.idUsuarios = vendas.usuarios_id');
        $this->db->join('cobrancas', 'cobrancas.vendas_id = vendas.idVendas');
        $this->db->join('lancamentos', 'vendas.idVendas = lancamentos.vendas_id', 'LEFT');
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

    public function getCobrancas($id = null)
    {
        $this->db->select('cobrancas.*');
        $this->db->from('cobrancas');
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
        } else {
            $row_set[] = ['label'=> 'Adicionar cliente...', 'id' => null];
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

    public function getQrCode($id, $pixKey, $emitente)
    {
        if (empty($id) || empty($pixKey) || empty($emitente)) {
            return;
        }

        $produtos = $this->getProdutos($id);
        $valorDesconto = $this->getById($id);
        $totalProdutos = array_reduce(
            $produtos,
            function ($carry, $produto) {
                return $carry + ($produto->quantidade * $produto->preco);
            },
            0
        );
        $amount = $valorDesconto->valor_desconto != 0 ? round(floatval($valorDesconto->valor_desconto), 2) : round(floatval($totalProdutos), 2);

        if ($amount <= 0) {
            return;
        }

        $pix = (new StaticPayload())
            ->setAmount($amount)
            ->setTid($id)
            ->setDescription(sprintf("%s Venda %s", substr($emitente->nome, 0, 18), $id), true)
            ->setPixKey(getPixKeyType($pixKey), $pixKey)
            ->setMerchantName($emitente->nome)
            ->setMerchantCity($emitente->cidade);

        return $pix->getQRCode();
    }
}

/* End of file vendas_model.php */
/* Location: ./application/models/vendas_model.php */
