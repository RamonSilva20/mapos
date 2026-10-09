<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cobrancas_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Listagem de cobranças da v5 (#2844), no padrão das listagens (#2852): os
     * mesmos filtros em listar() e contar(). Mais recentes primeiro, com o
     * nome do cliente.
     *
     * @param  array<string, string>  $filtros  pesquisa, status, tipo (os ou venda)
     */
    public function listar(array $filtros, int $limite, int $offset): array
    {
        $this->db->select('cobrancas.*, clientes.nomeCliente');
        $this->aplicarFiltros($filtros);

        return $this->db
            ->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left')
            ->order_by('cobrancas.idCobranca', 'desc')
            ->limit($limite, max(0, $offset))
            ->get()
            ->result();
    }

    /** Total da listagem com os mesmos filtros de listar(). */
    public function contar(array $filtros): int
    {
        $this->aplicarFiltros($filtros);
        $this->db->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left');

        return (int) $this->db->count_all_results();
    }

    /**
     * pesquisa procura no nome do cliente e, se for número, no Nº da cobrança
     * e no id do gateway (o OR fica entre parênteses para não anular os outros
     * filtros); status é o do gateway; tipo separa as de OS das de venda.
     */
    private function aplicarFiltros(array $filtros): void
    {
        $this->db->from('cobrancas');

        $pesquisa = $filtros['pesquisa'] ?? '';
        if ($pesquisa !== '') {
            $this->db->group_start()->like('clientes.nomeCliente', $pesquisa);
            if (ctype_digit($pesquisa)) {
                $this->db->or_where('cobrancas.idCobranca', (int) $pesquisa)->or_where('cobrancas.charge_id', (int) $pesquisa);
            }
            $this->db->group_end();
        }

        if (($filtros['status'] ?? '') !== '') {
            $this->db->where('cobrancas.status', $filtros['status']);
        }

        if (($filtros['tipo'] ?? '') === 'os') {
            $this->db->where('cobrancas.os_id IS NOT NULL', null, false);
        } elseif (($filtros['tipo'] ?? '') === 'venda') {
            $this->db->where('cobrancas.vendas_id IS NOT NULL', null, false);
        }
    }

    public function getById($id)
    {
        $this->db->select('cobrancas.*, clientes.*');
        $this->db->from('cobrancas');
        $this->db->where('cobrancas.idCobranca', $id);
        $this->db->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    public function getByOs($id)
    {
        $this->db->select('cobrancas.*, clientes.*, os.*');
        $this->db->distinct();
        $this->db->from('cobrancas');
        $this->db->join('os', 'os.idOs = cobrancas.os_id');
        $this->db->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left');
        $this->db->where('cobrancas.charge_id', $id);

        return $this->db->get()->row();
    }

    public function getByVendas($id)
    {
        $this->db->select('cobrancas.*, clientes.*, vendas.*');
        $this->db->distinct();
        $this->db->from('cobrancas');
        $this->db->join('vendas', 'vendas.idVendas = cobrancas.vendas_id');
        $this->db->join('clientes', 'clientes.idClientes = cobrancas.clientes_id', 'left');
        $this->db->where('cobrancas.charge_id', $id);

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

    public function atualizarStatus($idCobranca)
    {
        $cobranca = $this->getById($idCobranca);
        if (empty($cobranca)) {
            return $this->session->set_flashdata('error', 'Cobrança não existe!');
        }

        $gatewayDePagamento = $cobranca->payment_gateway;
        $this->load->library("Gateways/$gatewayDePagamento", null, 'PaymentGateway');

        $result = $this->PaymentGateway->atualizarDados($cobranca->idCobranca);

        return $result;
    }

    public function confirmarPagamento($idCobranca)
    {
        $cobranca = $this->getById($idCobranca);
        if (empty($cobranca)) {
            return $this->session->set_flashdata('error', 'Cobrança não existe!');
        }

        $gatewayDePagamento = $cobranca->payment_gateway;
        $this->load->library("Gateways/$gatewayDePagamento", null, 'PaymentGateway');

        $result = $this->PaymentGateway->confirmarPagamento($cobranca->idCobranca);

        return $result;
    }

    public function cancelarPagamento($idCobranca)
    {
        $cobranca = $this->getById($idCobranca);
        if (empty($cobranca)) {
            return $this->session->set_flashdata('error', 'Cobrança não existe!');
        }

        $gatewayDePagamento = $cobranca->payment_gateway;
        $this->load->library("Gateways/$gatewayDePagamento", null, 'PaymentGateway');

        $result = $this->PaymentGateway->cancelar($cobranca->idCobranca);

        return $result;
    }

    public function enviarEmail($idCobranca)
    {
        $cobranca = $this->getById($idCobranca);
        if (empty($cobranca)) {
            return $this->session->set_flashdata('error', 'Cobrança não existe!');
        }

        $gatewayDePagamento = $cobranca->payment_gateway;
        $this->load->library("Gateways/$gatewayDePagamento", null, 'PaymentGateway');

        $result = $this->PaymentGateway->enviarPorEmail($cobranca->idCobranca);

        return $result;
    }
}
