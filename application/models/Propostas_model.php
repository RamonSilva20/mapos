<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Propostas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($table, $fields, $where = '', $perpage = 0, $start = 0, $one = false, $array = 'array')
    {
        $this->db->select($fields . ',COALESCE(clientes.nomeCliente, propostas.cliente_nome) as nomeCliente, clientes.celular as celular_cliente');
        $this->db->from($table);
        $this->db->join('clientes', 'clientes.idClientes = propostas.clientes_id', 'left');
        $this->db->limit($perpage, $start);
        $this->db->order_by('idProposta', 'desc');
        if ($where) {
            $this->db->where($where);
        }

        $query = $this->db->get();
        $result = !$one ? $query->result() : $query->row();
        return $result;
    }

    public function getPropostas($where_array = [], $perpage = 0, $start = 0)
    {
        $lista_clientes = [];
        if (array_key_exists('pesquisa', $where_array)) {
            $this->db->select('idClientes');
            $this->db->like('nomeCliente', $where_array['pesquisa']);
            $this->db->or_like('documento', $where_array['pesquisa']);
            $this->db->limit(25);
            $clientes = $this->db->get('clientes')->result();

            foreach ($clientes as $c) {
                array_push($lista_clientes, $c->idClientes);
            }
        }

        $this->db->select('propostas.*, clientes.idClientes, COALESCE(clientes.nomeCliente, propostas.cliente_nome) as nomeCliente, clientes.celular as celular_cliente, usuarios.nome');
        $this->db->from('propostas');
        $this->db->join('clientes', 'clientes.idClientes = propostas.clientes_id', 'left');
        $this->db->join('usuarios', 'usuarios.idUsuarios = propostas.usuarios_id', 'left');

        // Condicional de status
        if (array_key_exists('status', $where_array)) {
            $this->db->where_in('propostas.status', $where_array['status']);
        }

        // Condicional de clientes - buscar também em cliente_nome
        if (array_key_exists('pesquisa', $where_array)) {
            $pesquisa = $where_array['pesquisa'];
            if ($lista_clientes != null && !empty($lista_clientes)) {
                $this->db->group_start();
                $this->db->where_in('propostas.clientes_id', $lista_clientes);
                $this->db->or_like('propostas.cliente_nome', $pesquisa);
                $this->db->group_end();
            } else {
                $this->db->like('propostas.cliente_nome', $pesquisa);
            }
        }

        // Condicional data inicial
        if (array_key_exists('de', $where_array)) {
            $this->db->where('propostas.data_proposta >=', $where_array['de']);
        }
        // Condicional data final
        if (array_key_exists('ate', $where_array)) {
            $this->db->where('propostas.data_proposta <=', $where_array['ate']);
        }

        $this->db->limit($perpage, $start);
        $this->db->order_by('propostas.idProposta', 'desc');

        $query = $this->db->get();
        return $query->result();
    }

    public function getById($id)
    {
        $this->db->select('propostas.*, clientes.nomeCliente as nomeCliente_original, clientes.documento, clientes.telefone, clientes.celular as celular_cliente, clientes.email, clientes.rua, clientes.numero, clientes.bairro, clientes.cidade, clientes.estado, clientes.cep, COALESCE(clientes.nomeCliente, propostas.cliente_nome) as nomeCliente, usuarios.nome, usuarios.telefone as telefone_usuario, usuarios.email as email_usuario');
        $this->db->from('propostas');
        $this->db->join('clientes', 'clientes.idClientes = propostas.clientes_id', 'left');
        $this->db->join('usuarios', 'usuarios.idUsuarios = propostas.usuarios_id', 'left');
        $this->db->where('propostas.idProposta', $id);
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function add($table, $data = null, $returnId = false)
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

    public function getProdutos($idProposta)
    {
        $this->db->select('produtos_proposta.*, produtos.descricao as descricao_produto');
        $this->db->from('produtos_proposta');
        $this->db->join('produtos', 'produtos.idProdutos = produtos_proposta.produtos_id', 'left');
        $this->db->where('produtos_proposta.proposta_id', $idProposta);
        $this->db->order_by('produtos_proposta.idProdutoProposta', 'asc');
        return $this->db->get()->result();
    }

    public function getServicos($idProposta)
    {
        $this->db->select('servicos_proposta.*, servicos.nome as nome_servico');
        $this->db->from('servicos_proposta');
        $this->db->join('servicos', 'servicos.idServicos = servicos_proposta.servicos_id', 'left');
        $this->db->where('servicos_proposta.proposta_id', $idProposta);
        $this->db->order_by('servicos_proposta.idServicoProposta', 'asc');
        return $this->db->get()->result();
    }

    public function getParcelas($idProposta)
    {
        $this->db->where('proposta_id', $idProposta);
        $this->db->order_by('numero', 'asc');
        return $this->db->get('parcelas_proposta')->result();
    }

    public function getOutros($idProposta)
    {
        $this->db->where('proposta_id', $idProposta);
        return $this->db->get('outros_proposta')->result();
    }

    public function gerarNumeroProposta()
    {
        $this->db->select_max('idProposta');
        $result = $this->db->get('propostas')->row();
        $nextId = ($result->idProposta ?? 0) + 1;
        
        return 'PROP-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

}

