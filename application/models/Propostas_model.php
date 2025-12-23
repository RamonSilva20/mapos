<?php

use Piggly\Pix\StaticPayload;

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
        // Buscar o maior número de proposta (não o ID)
        $this->db->select_max('numero_proposta');
        $result = $this->db->get('propostas')->row();
        
        if ($result && $result->numero_proposta) {
            // Se já existe numeração, pegar o maior número e incrementar
            // Remove qualquer prefixo e pega apenas o número
            $numeroAtual = preg_replace('/[^0-9]/', '', $result->numero_proposta);
            $numeroAtual = intval($numeroAtual);
            return $numeroAtual + 1;
        }
        
        // Se não existe nenhuma proposta, começar do 1
        return 1;
    }

    /**
     * Gera QR Code Pix para proposta
     */
    public function getQrCode($id, $pixKey, $emitente)
    {
        if (empty($id) || empty($pixKey) || empty($emitente)) {
            return;
        }

        // Calcular valor total da proposta
        $proposta = $this->getById($id);
        if (!$proposta) {
            return;
        }

        $produtos = $this->getProdutos($id);
        $servicos = $this->getServicos($id);
        $outros = $this->getOutros($id);

        $totalProdutos = 0;
        foreach ($produtos as $p) {
            $totalProdutos += $p->subtotal;
        }

        $totalServicos = 0;
        foreach ($servicos as $s) {
            $totalServicos += ($s->preco * ($s->quantidade ?: 1));
        }

        $totalOutros = 0;
        foreach ($outros as $o) {
            $totalOutros += $o->preco;
        }

        $subtotal = $totalProdutos + $totalServicos + $totalOutros;
        $desconto = floatval($proposta->valor_desconto ?? 0);
        $amount = $subtotal - $desconto;

        if ($amount <= 0) {
            return;
        }

        $pix = (new StaticPayload())
            ->setAmount($amount)
            ->setTid($id)
            ->setDescription(sprintf('%s PROP %s', substr($emitente->nome, 0, 18), $id), true)
            ->setPixKey(getPixKeyType($pixKey), $pixKey)
            ->setMerchantName($emitente->nome)
            ->setMerchantCity($emitente->cidade);

        return $pix->getQRCode();
    }

    /**
     * Conta propostas com filtros
     */
    public function countPropostas($where_array = [])
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

        $this->db->from('propostas');
        $this->db->join('clientes', 'clientes.idClientes = propostas.clientes_id', 'left');

        // Condicional de status
        if (array_key_exists('status', $where_array) && !empty($where_array['status'])) {
            if (is_array($where_array['status'])) {
                $this->db->where_in('propostas.status', $where_array['status']);
            } else {
                $this->db->where('propostas.status', $where_array['status']);
            }
        }

        // Condicional de pesquisa
        if (array_key_exists('pesquisa', $where_array) && !empty($where_array['pesquisa'])) {
            if (count($lista_clientes) > 0) {
                $this->db->where_in('propostas.clientes_id', $lista_clientes);
            } else {
                $this->db->like('propostas.cliente_nome', $where_array['pesquisa']);
            }
        }

        // Condicional de data
        if (array_key_exists('de', $where_array)) {
            $this->db->where('propostas.data_proposta >=', $where_array['de']);
        }
        if (array_key_exists('ate', $where_array)) {
            $this->db->where('propostas.data_proposta <=', $where_array['ate']);
        }

        return $this->db->count_all_results();
    }

}

